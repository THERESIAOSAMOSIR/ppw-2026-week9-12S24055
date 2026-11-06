<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\V1\BukuResource;
use App\Models\Buku;
use App\Traits\ApiResponse;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class BukuApiController extends Controller
{
    use ApiResponse;

    /**
     * GET /api/v1/bukus (Publik)
     * Menampilkan koleksi buku terpaginasi lengkap dengan blok meta,
     * mendukung ?search=..., ?kategori_id=..., ?tersedia=1, serta ?sort=...
     * Bebas dari N+1 Query Problem dengan with('kategori')->withCount('peminjamans').
     */
    public function index(Request $request): JsonResponse
    {
        $allowedSorts = ['id', 'isbn', 'judul', 'penulis', 'penerbit', 'tahun_terbit', 'stok', 'created_at'];
        $sortBy       = in_array($request->query('sort'), $allowedSorts, true) ? $request->query('sort') : 'judul';
        $sortDir      = strtolower((string) $request->query('dir', 'asc')) === 'desc' ? 'desc' : 'asc';
        $perPage      = min(max((int) $request->query('per_page', 5), 1), 50);

        $paginator = Buku::query()
            ->with('kategori')
            ->withCount('peminjamans')
            ->when($request->query('search'), function ($q, $search) {
                $q->where(function ($sub) use ($search) {
                    $sub->where('judul', 'like', "%{$search}%")
                        ->orWhere('penulis', 'like', "%{$search}%")
                        ->orWhere('isbn', 'like', "%{$search}%");
                });
            })
            ->when($request->query('kategori_id'), fn ($q, $katId) => $q->where('kategori_id', (int) $katId))
            ->when($request->boolean('tersedia'), fn ($q) => $q->where('stok', '>', 0))
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return $this->successResponse(
            data: BukuResource::collection($paginator->items()),
            message: 'Daftar koleksi katalog buku berhasil diambil.',
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
     * GET /api/v1/bukus/{id} (Publik)
     * Menampilkan detail satu buku beserta objek kategori dan daftar riwayat peminjamannya
     */
    public function show(string $identifier): JsonResponse
    {
        $buku = Buku::with(['kategori', 'peminjamans'])
            ->withCount('peminjamans')
            ->where('id', $identifier)
            ->orWhere('isbn', $identifier)
            ->first();

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan pengenal '{$identifier}' tidak ditemukan di katalog perpustakaan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        return $this->successResponse(
            data: new BukuResource($buku),
            message: 'Detail katalog buku berhasil ditemukan.'
        );
    }

    /**
     * POST /api/v1/bukus (Wajib Bearer Token)
     * Menyimpan buku baru & mereturn 201 Created beserta Header Location
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'kategori_id'  => 'required|integer|exists:kategoris,id',
            'isbn'         => 'required|string|max:20|unique:bukus,isbn',
            'judul'        => 'required|string|max:150',
            'penulis'      => 'required|string|max:100',
            'penerbit'     => 'required|string|max:100',
            'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
            'stok'         => 'required|integer|min:0|max:1000',
            'sinopsis'     => 'nullable|string|max:2000',
        ]);

        $validated['isbn'] = trim($validated['isbn']);

        $buku = Buku::create($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->createdResponse(
            data: new BukuResource($buku),
            locationUrl: url("/api/v1/bukus/{$buku->id}"),
            message: 'Koleksi buku baru berhasil ditambahkan ke katalog.'
        );
    }

    /**
     * PUT / PATCH /api/v1/bukus/{id} (Wajib Bearer Token)
     * Membuktikan perbedaan nyata antara penggantian penuh (PUT) dan modifikasi parsial (PATCH)
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $buku = Buku::find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan di katalog perpustakaan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        $isPut = $request->isMethod('put');

        if ($isPut) {
            // SEMANTIK PUT (Full Replacement): Seluruh atribut wajib dikirim secara utuh!
            $validated = $request->validate([
                'kategori_id'  => 'required|integer|exists:kategoris,id',
                'isbn'         => "required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul'        => 'required|string|max:150',
                'penulis'      => 'required|string|max:100',
                'penerbit'     => 'required|string|max:100',
                'tahun_terbit' => 'required|integer|min:1900|max:' . (date('Y') + 1),
                'stok'         => 'required|integer|min:0|max:1000',
                'sinopsis'     => 'nullable|string|max:2000',
            ]);

            // Pada semantik PUT murni, jika atribut nullable (sinopsis) tidak dikirim di body, ia direset ke null
            $validated['sinopsis'] = $request->input('sinopsis', null);
            $validated['isbn']     = trim($validated['isbn']);
        } else {
            // SEMANTIK PATCH (Partial Update): HANYA memvalidasi & memperbarui atribut yang dikirim di body!
            $validated = $request->validate([
                'kategori_id'  => 'sometimes|required|integer|exists:kategoris,id',
                'isbn'         => "sometimes|required|string|max:20|unique:bukus,isbn,{$buku->id}",
                'judul'        => 'sometimes|required|string|max:150',
                'penulis'      => 'sometimes|required|string|max:100',
                'penerbit'     => 'sometimes|required|string|max:100',
                'tahun_terbit' => 'sometimes|required|integer|min:1900|max:' . (date('Y') + 1),
                'stok'         => 'sometimes|required|integer|min:0|max:1000',
                'sinopsis'     => 'sometimes|nullable|string|max:2000',
            ]);

            if (empty($validated)) {
                return $this->problemResponse(
                    title: 'Bad Request',
                    detail: 'Permintaan PATCH wajib menyertakan minimal satu atribut buku yang ingin diperbarui.',
                    status: 400,
                    errorSlug: 'empty-patch-payload'
                );
            }

            if (isset($validated['isbn'])) {
                $validated['isbn'] = trim($validated['isbn']);
            }
        }

        $buku->update($validated);
        $buku->load('kategori')->loadCount('peminjamans');

        return $this->successResponse(
            data: new BukuResource($buku),
            message: $isPut
                ? 'Seluruh representasi data buku berhasil diganti secara utuh (PUT).'
                : 'Atribut data buku berhasil diperbarui sebagian (PATCH).'
        );
    }

    /**
     * DELETE /api/v1/bukus/{id} (Wajib Bearer Token)
     * Menolak penghapusan dengan 409 Conflict jika masih ada peminjaman aktif ('dipinjam'),
     * atau mengembalikan 204 No Content jika berhasil dihapus.
     */
    public function destroy(string $id): JsonResponse
    {
        $buku = Buku::withCount([
            'peminjamans as active_loans_count' => fn ($q) => $q->where('status', 'dipinjam'),
        ])->find($id);

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan ID '{$id}' tidak ditemukan di katalog perpustakaan.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        if ($buku->active_loans_count > 0) {
            return $this->problemResponse(
                title: 'Conflict — Book Has Active Loans',
                detail: "Buku '{$buku->judul}' (ISBN: {$buku->isbn}) tidak dapat dihapus karena sedang dipinjam oleh {$buku->active_loans_count} mahasiswa.",
                status: 409,
                errorSlug: 'book-has-active-loans'
            );
        }

        $buku->delete();

        return $this->noContentResponse();
    }
    /**
     * GET /api/v1/bukus/{id}/referensi-eksternal (Publik)
     * REST Service Consumer: Memanggil OpenLibrary REST API berdasarkan ISBN buku
     * menggunakan fasad Http::timeout(8)->get(...) dengan penanganan fallback yang aman.
     */
    public function referensiEksternal(string $id): JsonResponse
    {
        $buku = Buku::with('kategori')->where('id', $id)->orWhere('isbn', $id)->first();

        if (!$buku) {
            return $this->problemResponse(
                title: 'Resource Not Found',
                detail: "Buku dengan pengenal '{$id}' tidak ditemukan di katalog lokal SIPUS-Del.",
                status: 404,
                errorSlug: 'book-not-found'
            );
        }

        $cleanIsbn = preg_replace('/[^0-9Xx]/', '', (string) $buku->isbn);
        $bibKey    = "ISBN:{$cleanIsbn}";

        try {
            $response = Http::acceptJson()
                ->timeout(8)
                ->get('https://openlibrary.org/api/books', [
                    'bibkeys' => $bibKey,
                    'format'  => 'json',
                    'jscmd'   => 'data',
                ]);

            if ($response->failed()) {
                return $this->problemResponse(
                    title: 'Upstream Service Unavailable',
                    detail: 'Gagal berkomunikasi dengan layanan eksternal OpenLibrary REST API.',
                    status: 502,
                    errorSlug: 'external-service-error'
                );
            }

            $payload      = $response->json();
            $externalBook = $payload[$bibKey] ?? null;

            return $this->successResponse(
                data: [
                    'buku_lokal' => [
                        'id'           => $buku->id,
                        'isbn'         => $buku->isbn,
                        'judul'        => $buku->judul,
                        'penulis'      => $buku->penulis,
                        'penerbit'     => $buku->penerbit,
                        'tahun_terbit' => (int) $buku->tahun_terbit,
                    ],
                    'openlibrary_metadata' => $externalBook ? [
                        'found'           => true,
                        'title'           => $externalBook['title'] ?? $buku->judul,
                        'subtitle'        => $externalBook['subtitle'] ?? null,
                        'number_of_pages' => $externalBook['number_of_pages'] ?? null,
                        'publish_date'    => $externalBook['publish_date'] ?? null,
                        'publishers'      => collect($externalBook['publishers'] ?? [])->pluck('name')->values(),
                        'authors'         => collect($externalBook['authors'] ?? [])->pluck('name')->values(),
                        'info_url'        => $externalBook['url'] ?? "https://openlibrary.org/isbn/{$cleanIsbn}",
                        'cover_url'       => $externalBook['cover']['medium'] ?? null,
                    ] : [
                        'found'      => false,
                        'note'       => "Metadata ISBN '{$buku->isbn}' belum terdaftar di katalog publik OpenLibrary (terbitan lokal/kampus).",
                        'lookup_url' => "https://openlibrary.org/isbn/{$cleanIsbn}",
                    ],
                ],
                message: 'Pengayaan metadata buku dari OpenLibrary REST API berhasil diproses.'
            );
        } catch (\Throwable $e) {
            return $this->problemResponse(
                title: 'External API Connection Timeout',
                detail: 'Koneksi menuju layanan eksternal OpenLibrary melampaui batas waktu tunggu (8 detik) atau jaringan tidak tersedia.',
                status: 504,
                errorSlug: 'external-api-timeout'
            );
        }
    }
}
