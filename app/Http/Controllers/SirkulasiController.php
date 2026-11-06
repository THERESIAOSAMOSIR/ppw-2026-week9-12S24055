<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use App\Models\Peminjaman;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use Illuminate\Support\Str;
use RuntimeException;

class SirkulasiController extends Controller
{
    public function index(Request $request)
    {
        $peminjamans = Peminjaman::with('buku')
            ->when($request->search, function ($q, $search) {
                $q->where('kode_pinjam', 'like', "%{$search}%")
                  ->orWhere('nim_peminjam', 'like', "%{$search}%")
                  ->orWhere('nama_peminjam', 'like', "%{$search}%");
            })
            ->when($request->status, fn($q, $status) => $q->where('status', $status))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return view('sirkulasi.index', compact('peminjamans'));
    }

    public function pinjamStore(Request $request)
    {
        $validated = $request->validate([
            'buku_id' => 'required|exists:bukus,id',
            'nim_peminjam' => 'required|string|min:6|max:15',
            'nama_peminjam' => 'required|string|min:3|max:150',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                // Lock row buku untuk cegah race condition kuota/stok
                $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->firstOrFail();

                if ($buku->stok < 1) {
                    throw new RuntimeException("Stok buku '{$buku->judul}' telah habis!");
                }

                $sedangDipinjam = Peminjaman::where('buku_id', $buku->id)
                    ->where('nim_peminjam', strtoupper(trim($validated['nim_peminjam'])))
                    ->where('status', 'dipinjam')
                    ->exists();

                if ($sedangDipinjam) {
                    throw new RuntimeException("Mahasiswa dengan NIM tersebut masih meminjam buku ini!");
                }

                $buku->decrement('stok');

                Peminjaman::create([
                    'kode_pinjam' => 'PMJ-' . strtoupper(Str::random(8)),
                    'buku_id' => $buku->id,
                    'nim_peminjam' => strtoupper(trim($validated['nim_peminjam'])),
                    'nama_peminjam' => $validated['nama_peminjam'],
                    'tanggal_pinjam' => now()->toDateString(),
                    'tanggal_jatuh_tempo' => now()->addDays(7)->toDateString(),
                    'status' => 'dipinjam',
                ]);

                session(['last_peminjam' => $validated['nama_peminjam']]);
                cookie()->queue('last_peminjam_nim', $validated['nim_peminjam'], 10080);
            }, 3);

            return back()->with('success', 'Transaksi peminjaman berhasil diproses secara atomik.');

        } catch (RuntimeException $e) {
            return back()->withInput()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[PEMINJAMAN_DB_ERROR]: ' . $e->getMessage());
            return back()->withInput()->with('error', 'Gagal memproses transaksi basis data.');
        }
    }

    public function kembalikanStore($id)
    {
        try {
            DB::transaction(function () use ($id) {
                $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->firstOrFail();

                if ($peminjaman->status === 'dikembalikan') {
                    throw new RuntimeException("Buku ini sudah dikembalikan sebelumnya.");
                }

                $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->firstOrFail();

                $peminjaman->update([
                    'status' => 'dikembalikan',
                    'tanggal_kembali' => now()->toDateString(),
                ]);

                $buku->increment('stok');
            }, 3);

            return back()->with('success', 'Buku berhasil dikembalikan dan stok diperbarui.');

        } catch (RuntimeException $e) {
            return back()->with('error', $e->getMessage());
        } catch (QueryException $e) {
            Log::error('[PENGEMBALIAN_DB_ERROR]: ' . $e->getMessage());
            return back()->with('error', 'Gagal memproses pengembalian pada sistem database.');
        }
    }
}