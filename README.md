# SPK Pemilihan Tempat Kos

Aplikasi web Sistem Pendukung Keputusan (SPK) untuk membandingkan tempat kos
menggunakan metode Simple Additive Weighting (SAW).

## Fitur

- Login pengguna
- Pengelolaan data kos dan koordinat lokasi
- Pengaturan jenis dan bobot kriteria
- Pengisian nilai alternatif berdasarkan kriteria
- Perhitungan normalisasi SAW dan hasil peringkat
- Dashboard ringkasan data

## Persyaratan

- PHP dengan ekstensi `mysqli`
- MySQL atau MariaDB
- Apache atau web server PHP lain

## Menjalankan secara lokal

1. Salin proyek ke folder web server, misalnya `htdocs` pada XAMPP.
2. Buat database MySQL bernama `spk_kos`.
3. Sesuaikan host, pengguna, kata sandi, dan nama database pada `koneksi.php`.
4. Siapkan tabel `users`, `alternatif`, `kriteria`, dan `penilaian` beserta kolom
   yang digunakan aplikasi.
5. Buka `login.php` melalui web server.

Repositori ini belum menyertakan dump atau skrip SQL database. Simpan kata sandi
pengguna sebagai hash yang dibuat dengan `password_hash()`; login memverifikasi
kata sandi menggunakan `password_verify()`.
