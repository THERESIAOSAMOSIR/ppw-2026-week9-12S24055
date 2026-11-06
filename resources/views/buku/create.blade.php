<x-layout>
    <x-slot:title>Tambah Buku Baru</x-slot>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h4 class="fw-bold text-del">Tambah Data Buku</h4>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('buku.store') }}" method="POST">
                        @csrf
                        
                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">ISBN</label>
                                <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror" value="{{ old('isbn') }}" required>
                                @error('isbn') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Judul Buku</label>
                                <input type="text" name="judul" class="form-control @error('judul') is-invalid @enderror" value="{{ old('judul') }}" required>
                                @error('judul') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Penulis</label>
                                <input type="text" name="penulis" class="form-control @error('penulis') is-invalid @enderror" value="{{ old('penulis') }}" required>
                                @error('penulis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-bold">Penerbit</label>
                                <input type="text" name="penerbit" class="form-control @error('penerbit') is-invalid @enderror" value="{{ old('penerbit') }}" required>
                                @error('penerbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Tahun Terbit</label>
                                <input type="number" name="tahun_terbit" class="form-control @error('tahun_terbit') is-invalid @enderror" value="{{ old('tahun_terbit') }}" required>
                                @error('tahun_terbit') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Kategori</label>
                                <select name="kategori_id" class="form-select @error('kategori_id') is-invalid @enderror" required>
                                    <option value="">-- Pilih Kategori --</option>
                                    @foreach($kategoris as $kategori)
                                        <option value="{{ $kategori->id }}" {{ old('kategori_id') == $kategori->id ? 'selected' : '' }}>
                                            {{ $kategori->nama_kategori }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('kategori_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Stok</label>
                                <input type="number" name="stok" class="form-control @error('stok') is-invalid @enderror" value="{{ old('stok', 0) }}" required>
                                @error('stok') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="form-label fw-bold">Sinopsis</label>
                            <textarea name="sinopsis" class="form-control @error('sinopsis') is-invalid @enderror" rows="3">{{ old('sinopsis') }}</textarea>
                            @error('sinopsis') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <div class="d-flex justify-content-end gap-2">
                            <a href="{{ route('buku.index') }}" class="btn btn-secondary">Batal</a>
                            <button type="submit" class="btn btn-del">Simpan Data</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</x-layout>