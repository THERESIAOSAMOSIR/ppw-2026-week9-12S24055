<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sirkulasi Peminjaman - SIPUS-Del</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <nav class="bg-blue-900 text-white px-6 py-4 flex justify-between items-center">
        <h1 class="text-xl font-bold">SIPUS-Del - Sirkulasi Perpustakaan</h1>
        <div class="flex items-center gap-4">
            <a href="{{ route('bukus.index') }}" class="hover:underline">Katalog Buku</a>
            <form action="{{ route('logout') }}" method="POST">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1 rounded text-sm">Logout</button>
            </form>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-6">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-6">
                {{ session('error') }}
            </div>
        @endif

        <!-- Form Transaksi Peminjaman -->
        <div class="bg-white p-6 rounded-lg shadow mb-8">
            <h2 class="text-lg font-bold mb-4 text-gray-800">Form Transaksi Peminjaman (ACID Protected)</h2>
            <form action="{{ route('sirkulasi.pinjam') }}" method="POST" class="grid grid-cols-1 md:grid-cols-4 gap-4">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-700">Pilih Buku</label>
                    <select name="buku_id" required class="w-full mt-1 border border-gray-300 rounded p-2">
                        @foreach(\App\Models\Buku::all() as $buku)
                            <option value="{{ $buku->id }}">{{ $buku->judul }} (Stok: {{ $buku->stok }})</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">NIM Peminjam</label>
                    <input type="text" name="nim_peminjam" value="{{ old('nim_peminjam') }}" required placeholder="12S24001" class="w-full mt-1 border border-gray-300 rounded p-2">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700">Nama Peminjam</label>
                    <input type="text" name="nama_peminjam" value="{{ old('nama_peminjam') }}" required placeholder="Nama Mahasiswa" class="w-full mt-1 border border-gray-300 rounded p-2">
                </div>
                <div class="flex items-end">
                    <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 rounded">
                        Proses Pinjam
                    </button>
                </div>
            </form>
        </div>

        <!-- Tabel Histori Peminjaman -->
        <div class="bg-white rounded-lg shadow overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Kode Pinjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Judul Buku</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Peminjam</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tgl Pinjam / Jatuh Tempo</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    @forelse($peminjamans as $pmj)
                        <tr>
                            <td class="px-6 py-4 text-sm font-bold text-blue-600">{{ $pmj->kode_pinjam }}</td>
                            <td class="px-6 py-4 text-sm text-gray-900">{{ $pmj->buku->judul }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $pmj->nama_peminjam }} ({{ $pmj->nim_peminjam }})</td>
                            <td class="px-6 py-4 text-sm text-gray-500">{{ $pmj->tanggal_pinjam }} / <span class="text-red-500">{{ $pmj->tanggal_jatuh_tempo }}</span></td>
                            <td class="px-6 py-4 text-sm">
                                <span class="px-2 py-1 rounded text-xs font-semibold {{ $pmj->status === 'dipinjam' ? 'bg-yellow-100 text-yellow-800' : 'bg-green-100 text-green-800' }}">
                                    {{ strtoupper($pmj->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm">
                                @if($pmj->status === 'dipinjam')
                                    <form action="{{ route('sirkulasi.kembali', $pmj->id) }}" method="POST">
                                        @csrf
                                        @method('PATCH')
                                        <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1 rounded text-xs">
                                            Kembalikan
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-xs">Selesai ({{ $pmj->tanggal_kembali }})</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-4 text-center text-gray-500 text-sm">Belum ada data transaksi sirkulasi.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>