# SIPUS-Del (Sistem Informasi Perpustakaan Del) - Tahap 2
**Modul Praktikum Pemrograman & Pengujian Web (PPW) - Minggu 06**

---

## Identitas Praktikan
- **Nama**: Theresia Oktaviani Samosir
- **NIM**: 12S24055
- **Program Studi**: S1 Sistem Informasi
- **Institut Teknologi Del**

---

## Deskripsi Tugas Praktikum
Praktikum Minggu 06 (SIPUS-Del Tahap 2) berfokus pada pengembangan modul sirkulasi peminjaman, autentikasi pustakawan, pengelolaan sesi, serta penerapan mekanisme transaksi basis data ACID (*Atomicity, Consistency, Isolation, Durability*) dengan penguncian baris (*pessimistic locking*) menggunakan `lockForUpdate()`.

**Cakupan Implementasi Fitur:**
1. **Autentikasi Pustakawan & Keamanan Sesi**: Proteksi rute dengan middleware `auth`, regenerasi ID sesi (`$request->session()->regenerate()`), dan manajemen Cookie aman berAtribut `HttpOnly` serta `SameSite=Lax`.
2. **Sirkulasi Peminjaman (ACID Protected)**: Eksekusi transaksi peminjaman & pengembalian atomik via `DB::transaction()` dengan `lockForUpdate()` untuk mencegah *race condition* stok buku serta *rollback* otomatis jika stok habis.
3. **Katalog & Analytics Perpustakaan**: Statistik ringkasan menggunakan Fluent Query Builder (`DB::table`) dan pemetaan agregasi relasi `withCount('peminjamans')`.

---

## Struktur Direktori Proyek

```text
ppw-2026-week5-12S24055/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AuthController.php        # Controller Login/Logout & Manajemen Sesi
│   │   │   ├── BukuController.php        # Controller Katalog & Analytics DB::table
│   │   │   └── SirkulasiController.php   # Controller Sirkulasi Transaksi ACID & lockForUpdate
│   │   └── Middleware/
│   └── Models/
│       ├── Buku.php                      # Model Buku (BelongsTo Kategori, HasMany Peminjaman)
│       ├── Kategori.php                  # Model Kategori Buku
│       ├── Peminjaman.php                # Model Transaksi Sirkulasi
│       └── User.php                      # Model User/Pustakawan
├── database/
│   ├── migrations/                       # Skema Tabel (users, bukus, kategoris, peminjamans)
│   └── seeders/
│       └── DatabaseSeeder.php            # Seeder Pustakawan & Sampel Data Uji Stok
├── resources/
│   └── views/
│       ├── auth/
│       │   └── login.blade.php           # Tampilan Form Login Pustakawan
│       ├── bukus/
│       │   └── index.blade.php           # Dashboard Katalog & Card Analytics
│       └── sirkulasi/
│           └── index.blade.php           # Form & Histori Sirkulasi Peminjaman
├── routes/
│   └── web.php                           # Definisi Rute & Proteksi Middleware Auth
├── screenshots/
│   ├── acid-rollback-test.png            # Bukti Penolakan Transaksi (Stok = 0)
│   ├── acid-success-test.png             # Bukti Transaksi Peminjaman Sukses
│   └── cookie-audit.png                  # Bukti Audit Cookie HttpOnly di DevTools
└── README.md