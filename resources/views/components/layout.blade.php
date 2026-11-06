<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $title ?? 'SIPUS-Del' }} | Institut Teknologi Del</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .navbar-del { background-color: #4C1D95; }
        .btn-del { background-color: #4C1D95; color: #ffffff; }
        .btn-del:hover { background-color: #3B0764; color: #ffffff; }
        .text-del { color: #4C1D95; }
    </style>
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark navbar-del shadow-sm mb-4">
        <div class="container">
            <a class="navbar-brand fw-bold" href="{{ route('buku.index') }}">SIPUS-Del</a>
        </div>
    </nav>
    
    <div class="container pb-5">
        <!-- Menangkap dan menampilkan Flash Session (Notifikasi Sukses) -->
        @if(session('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif
        
        <!-- Tempat merender konten halaman anak -->
        {{ $slot }}
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>