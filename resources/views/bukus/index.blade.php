<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog & Analytics - SIPUS-Del</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50">
    <!-- Navbar -->
    <nav class="bg-blue-900 text-white px-6 py-4 flex justify-between items-center shadow-md">
        <div class="flex items-center gap-2">
            <span class="text-2xl font-black tracking-wider">SIPUS-Del</span>
            <span class="text-xs bg-blue-700 px-2 py-0.5 rounded text-blue-200">Tahap 2</span>
        </div>
        <div class="flex items-center gap-6">
            <a href="{{ route('bukus.index') }}" class="font-semibold hover:text-blue-200">Katalog Buku</a>
            <a href="{{ route('sirkulasi.index') }}" class="hover:text-blue-200">Sirkulasi Peminjaman</a>
            <form action="{{ route('logout') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="bg-red-500 hover:bg-red-600 px-3 py-1.5 rounded-lg text-sm font-medium transition">
                    Logout
                </button>
            </form>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto py-8 px-4">
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded-lg mb-6 shadow-sm">
                {{ session('success') }}
            </div>
        @endif

        <!-- Kartu Analytics Perpustakaan (Fluent Query Builder DB::table) -->
        <h2 class="text-xl font-bold text-gray-800 mb-4">Ringkasan Statistik Perpustakaan</h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Judul Buku</p>
                <p class="text-3xl font-extrabold text-blue-900 mt-1">{{ $stats['total_judul'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Stok Tersedia</p>
                <p class="text-3xl font-extrabold text-emerald-600 mt-1">{{ $stats['total_stok'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Buku Sedang Dipinjam</p>
                <p class="text-3xl font-extrabold text-amber-500 mt-1">{{ $stats['total_dipinjam'] }}</p>
            </div>
            <div class="bg-white p-5 rounded-xl shadow-sm border border-gray-100">
                <p class="text-xs font-semibold text-gray-500 uppercase tracking-wider">Total Dikembalikan</p>
                <p class="text-3xl font-extrabold text-indigo-600 mt-1">{{ $stats['total_dikembalikan'] }}</p>
            </div>
        </div>

        <!-- Filter Pencarian Dinamis -->
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 mb-6">
            <form action="{{ route('bukus.index') }}" method="GET" class="flex flex-col sm:flex-row gap-3">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, penulis, atau ISBN..." class="flex-1 border border-gray-300 rounded-lg px-4 py-2 focus:ring-2 focus:ring-blue-500 focus:outline-none">
                <button type="submit" class="bg-blue-800 hover:bg-blue-900 text-white font-medium px-6 py-2 rounded-lg transition">
                    Cari Buku
                </button>
                @if(request('search'))
                    <a href="{{ route('bukus.index') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 font-medium px-4 py-2 rounded-lg text-center transition">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        <!-- Tabel Katalog Buku -->
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <table class="min-w-full divide-y divide-gray-200">
                <bg-gray-50 class="bg-gray-50">
                    <tr>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Judul Buku</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Kategori</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">Penulis / Penerbit</th>
                        <th class="px-6 py-3.5 text-left text-xs font-bold text-gray-500 uppercase tracking-wider">ISBN</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Stok</th>
                        <th class="px-6 py-3.5 text-center text-xs font-bold text-gray-500 uppercase tracking-wider">Total Dipinjam</th>
                    </tr>
                </bg-gray-50>
                <tbody class="divide-y divide-gray-200 bg-white">
                    @forelse($bukus as $buku)
                        <tr class="hover:bg-gray-50 transition">
                            <td class="px-6 py-4 text-sm font-semibold text-gray-900">{{ $buku->judul }}</td>
                            <td class="px-6 py-4 text-sm text-gray-600">
                                <span class="bg-blue-50 text-blue-700 px-2.5 py-1 rounded-full text-xs font-medium">
                                    {{ $buku->kategori->nama_kategori ?? 'Umum' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm text-gray-600">{{ $buku->penulis }} <span class="text-xs text-gray-400">({{ $buku->penerbit ?? '-' }})</span></td>
                            <td class="px-6 py-4 text-sm font-mono text-gray-500">{{ $buku->isbn }}</td>
                            <td class="px-6 py-4 text-sm text-center font-bold">
                                @if($buku->stok > 0)
                                    <span class="text-emerald-600 bg-emerald-50 px-2.5 py-1 rounded-md">{{ $buku->stok }}</span>
                                @else
                                    <span class="text-red-600 bg-red-50 px-2.5 py-1 rounded-md">Habis (0)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-sm text-center font-medium text-gray-700">
                                {{ $buku->peminjamans_count }}x
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-8 text-center text-gray-500 text-sm">
                                Tidak ada data buku yang ditemukan.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <div class="mt-4">
            {{ $bukus->links() }}
        </div>
    </div>
</body>
</html>