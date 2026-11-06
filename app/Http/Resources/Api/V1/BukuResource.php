<?php

namespace App\Http\Resources\Api\V1;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BukuResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $totalPeminjaman = $this->whenCounted('peminjamans', fn () => (int) $this->peminjamans_count, 0);
        $isAvailable     = (int) $this->stok > 0;

        // Bangun kontrol Hypermedia HATEOAS (Richardson Maturity Model Level 3)
        $links = [
            'self' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'GET',
            ],
            'referensi_eksternal' => [
                'href'   => url("/api/v1/bukus/{$this->id}/referensi-eksternal"),
                'method' => 'GET',
            ],
            'update_full' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PUT',
            ],
            'update_partial' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'PATCH',
            ],
            'delete' => [
                'href'   => url("/api/v1/bukus/{$this->id}"),
                'method' => 'DELETE',
            ],
        ];

        // Keindahan HATEOAS Dinamis: Link aksi 'pinjam_buku' HANYA muncul apabila stok > 0
        if ($isAvailable) {
            $links['pinjam_buku'] = [
                'href'   => url('/api/v1/peminjamans'),
                'method' => 'POST',
            ];
        }

        return [
            'id'                  => $this->id,
            'isbn'                => $this->isbn,
            'judul'               => $this->judul,
            'penulis'             => $this->penulis,
            'penerbit'            => $this->penerbit,
            'tahun_terbit'        => (int) $this->tahun_terbit,
            'stok'                => (int) $this->stok,
            'status_ketersediaan' => $isAvailable ? 'AVAILABLE' : 'OUT_OF_STOCK',
            'sinopsis'            => $this->sinopsis,
            'total_peminjaman'    => $totalPeminjaman,
            'kategori'            => new KategoriResource($this->whenLoaded('kategori')),
            'riwayat_peminjaman'  => PeminjamanResource::collection($this->whenLoaded('peminjamans')),
            'updated_at'          => $this->updated_at?->toIso8601String(),
            '_links'              => $links,
        ];
    }
}
