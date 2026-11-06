<x-layout>
    <x-slot:title>Daftar Buku</x-slot>

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold text-del">Katalog Buku Perpustakaan</h3>
        <a href="{{ route('buku.create') }}" class="btn btn-del">Tambah Buku Baru</a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>ISBN</th>
                            <th>Judul Buku</th>
                            <th>Kategori</th>
                            <th>Penulis</th>
                            <th>Tahun</th>
                            <th>Stok</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($bukus as $buku)
                            <tr>
                                <td>{{ $buku->isbn }}</td>
                                <td>{{ $buku->judul }}</td>
                                <td>
                                    <span class="badge bg-secondary">{{ $buku->kategori->nama_kategori }}</span>
                                </td>
                                <td>{{ $buku->penulis }}</td>
                                <td>{{ $buku->tahun_terbit }}</td>
                                <td>
                                    @if($buku->stok > 0)
                                        <span class="badge bg-success">{{ $buku->stok }}</span>
                                    @else
                                        <span class="badge bg-danger">Habis</span>
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('buku.edit', $buku->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                    <form action="{{ route('buku.destroy', $buku->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus buku ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    Belum ada data buku yang terdaftar di sistem.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <div class="mt-4">
        {{ $bukus->links('pagination::bootstrap-5') }}
    </div>
</x-layout>