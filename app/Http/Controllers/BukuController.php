<?php

namespace App\Http\Controllers;

use App\Models\Buku;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class BukuController extends Controller
{
    public function index(Request $request)
    {
        if ($request->filled('search')) {
            session(['last_buku_search' => $request->search]);
        }

        $bukus = Buku::with('kategori')
            ->withCount('peminjamans')
            ->when($request->search, function ($q, $s) {
                $q->where('judul', 'like', "%{$s}%")
                  ->orWhere('penulis', 'like', "%{$s}%")
                  ->orWhere('isbn', 'like', "%{$s}%");
            })
            ->when($request->kategori_id, fn($q, $k) => $q->where('kategori_id', $k))
            ->orderBy('judul')
            ->paginate(10)
            ->withQueryString();

        // Rekapitulasi Statistik dengan Fluent Query Builder (DB::table)
        $stats = [
            'total_judul' => DB::table('bukus')->count(),
            'total_stok' => DB::table('bukus')->sum('stok'),
            'total_dipinjam' => DB::table('peminjamans')->where('status', 'dipinjam')->count(),
            'total_dikembalikan' => DB::table('peminjamans')->where('status', 'dikembalikan')->count(),
        ];

        return view('bukus.index', compact('bukus', 'stats'));
    }
}