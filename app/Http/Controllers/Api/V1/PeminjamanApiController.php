<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\PeminjamanResource;
use App\Models\Buku;
use App\Models\Peminjaman;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PeminjamanApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/peminjamans (Wajib Bearer Token)
     * Menampilkan daftar transaksi peminjaman terpaginasi dengan filter ?status=dipinjam|dikembalikan dan ?nim=...
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min(max((int) $request->query('per_page', 10), 1), 50);

        $paginator = Peminjaman::query()
            ->with('buku')
            ->when($request->query('status'), fn ($q, $status) => $q->where('status', $status))
            ->when($request->query('nim'), fn ($q, $nim) => $q->where('nim_peminjam', strtoupper(trim($nim))))
            ->latest('id')
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: PeminjamanResource::collection($paginator->items()),
            message: 'Daftar transaksi sirkulasi peminjaman berhasil diambil.',
            meta: [
                'current_page' => $paginator->currentPage(),
                'per_page'     => $paginator->perPage(),
                'total_items'  => $paginator->total(),
                'total_pages'  => $paginator->lastPage(),
                'links'        => [
                    'first' => $paginator->url(1),
                    'prev'  => $paginator->previousPageUrl(),
                    'next'  => $paginator->nextPageUrl(),
                    'last'  => $paginator->url($paginator->lastPage()),
                ],
            ]
        );
    }

    /**
     * GET /api/v1/peminjamans/{id} (Wajib Bearer Token)
     * Menampilkan detail satu transaksi peminjaman
     */
    public function show(string $id): JsonResponse
    {
        $peminjaman = Peminjaman::with('buku')->find($id);

        if (!$peminjaman) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Transaksi peminjaman dengan ID '{$id}' tidak ditemukan.",
                status: 404,
                errorSlug: 'loan-not-found'
            );
        }

        return $this->successResponse(
            data: new PeminjamanResource($peminjaman),
            message: 'Detail transaksi peminjaman berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/peminjamans (Wajib Bearer Token)
     * Mencatat peminjaman buku baru secara atomik dengan DB::transaction() + lockForUpdate()
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'buku_id'       => 'required|integer|exists:bukus,id',
            'nim_peminjam'  => 'required|string|min:6|max:15',
            'nama_peminjam' => 'required|string|min:3|max:150',
            'durasi_hari'   => 'nullable|integer|min:1|max:30',
        ]);

        return DB::transaction(function () use ($validated) {
            // 1. Kunci baris buku secara eksklusif (SELECT ... FOR UPDATE) untuk menangkal Race Condition
            $buku = Buku::where('id', $validated['buku_id'])->lockForUpdate()->first();

            if (!$buku) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: 'Buku yang diminta tidak ditemukan.',
                    status: 404,
                    errorSlug: 'book-not-found'
                );
            }

            $nim = strtoupper(trim($validated['nim_peminjam']));

            // 2. Cek apakah mahasiswa masih meminjam buku yang sama dan belum dikembalikan -> 409 Conflict
            $alreadyBorrowed = Peminjaman::where('buku_id', $buku->id)
                ->where('nim_peminjam', $nim)
                ->where('status', 'dipinjam')
                ->exists();

            if ($alreadyBorrowed) {
                return $this->problemResponse(
                    title: 'Duplicate Active Loan Conflict',
                    detail: "Mahasiswa dengan NIM '{$nim}' masih meminjam buku '{$buku->judul}' dan belum mengembalikannya.",
                    status: 409,
                    errorSlug: 'duplicate-active-loan'
                );
            }

            // 3. Cek ketersediaan stok buku -> 422 Unprocessable Content jika stok < 1
            if ($buku->stok < 1) {
                return $this->problemResponse(
                    title: 'Book Stock Exhausted',
                    detail: "Stok buku sedang habis (0 eksemplar tersisa untuk '{$buku->judul}').",
                    status: 422,
                    errorSlug: 'stock-exhausted'
                );
            }

            // 4. Kurangi stok (-1) dan simpan peminjaman baru secara atomik
            $buku->decrement('stok');

            $durasiHari = (int) ($validated['durasi_hari'] ?? 7);

            $peminjaman = Peminjaman::create([
                'kode_pinjam'         => 'PMJ-' . strtoupper(Str::random(8)),
                'buku_id'             => $buku->id,
                'nim_peminjam'        => $nim,
                'nama_peminjam'       => $validated['nama_peminjam'],
                'tanggal_pinjam'      => now()->toDateString(),
                'tanggal_jatuh_tempo' => now()->addDays($durasiHari)->toDateString(),
                'status'              => 'dipinjam',
            ]);

            $peminjaman->load('buku');

            return $this->createdResponse(
                data: new PeminjamanResource($peminjaman),
                locationUrl: url("/api/v1/peminjamans/{$peminjaman->id}"),
                message: "Peminjaman buku '{$buku->judul}' berhasil dicatat. Sisa stok kini: {$buku->fresh()->stok}."
            );
        });
    }

    /**
     * PATCH /api/v1/peminjamans/{id}/kembalikan (Wajib Bearer Token)
     * Mengembalikan buku di dalam DB::transaction() + lockForUpdate()
     */
    public function kembalikan(string $id): JsonResponse
    {
        return DB::transaction(function () use ($id) {
            $peminjaman = Peminjaman::where('id', $id)->lockForUpdate()->first();

            if (!$peminjaman) {
                return $this->problemResponse(
                    title: 'Resource Not Found',
                    detail: "Data transaksi peminjaman dengan ID '{$id}' tidak ditemukan.",
                    status: 404,
                    errorSlug: 'loan-not-found'
                );
            }

            // Jika sudah berstatus 'dikembalikan', tolak dengan 409 Conflict
            if ($peminjaman->status === 'dikembalikan') {
                return $this->problemResponse(
                    title: 'Loan Already Returned Conflict',
                    detail: "Transaksi peminjaman '{$peminjaman->kode_pinjam}' sudah berstatus dikembalikan pada {$peminjaman->tanggal_kembali}.",
                    status: 409,
                    errorSlug: 'loan-already-returned'
                );
            }

            $buku = Buku::where('id', $peminjaman->buku_id)->lockForUpdate()->first();

            $peminjaman->update([
                'status'          => 'dikembalikan',
                'tanggal_kembali' => now()->toDateString(),
            ]);

            if ($buku) {
                $buku->increment('stok');
            }

            $peminjaman->load('buku');

            return $this->successResponse(
                data: new PeminjamanResource($peminjaman),
                message: "Buku berhasil dikembalikan. Stok terkini: " . ($buku ? $buku->fresh()->stok : 0) . "."
            );
        });
    }
}
