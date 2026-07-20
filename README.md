
# SIAKAD Mini

SIAKAD Mini adalah aplikasi web sederhana untuk memenuhi tugas UTS Pemrograman Web. Aplikasi ini mengimplementasikan alur autentikasi, manajemen data dosen, soft delete, upload foto, CSRF protection, role-based access control, relasi many-to-many antara dosen dan mata kuliah, serta fitur lanjutan seperti filter, sorting, dashboard, dan export CSV.

## Identitas
- Nama: Dimas Abiyyu Febriyan
- NIM: 2024150145
- Mata Kuliah: Pemrograman Web
- Tugas: UTS

## Fitur yang tersedia
- Autentikasi login/logout dengan password hash
- Guard akses halaman yang diproteksi
- CRUD dosen
- Soft delete dan restore data dosen
- Upload foto dengan validasi MIME type
- CSRF token untuk form POST
- RBAC sederhana (admin dan operator)
- Relasi many-to-many dosen dengan mata kuliah
- Pencarian, filter, sort, dan pagination
- Dashboard statistik
- Export data dosen ke CSV

## Struktur folder
- config/ -> konfigurasi database
- public/ -> halaman utama aplikasi
- src/ -> kelas Auth, DosenRepository, Validator
- uploads/ -> penyimpanan file foto dosen
- seed.sql -> skema database dan data awal

## Persiapan lingkungan
Pastikan Anda sudah menyiapkan server web dan database berikut:
- PHP 8.x
- MySQL/MariaDB
- Apache atau server web lain yang mendukung PHP

## Langkah setup
1. Buat database dengan nama `siakad_mini`.
2. Import file `seed.sql` ke database tersebut.
3. Jalankan aplikasi melalui browser pada folder `public`.
4. Jika diperlukan, sesuaikan koneksi database di file `config/database.php`.

## Akun demo
- Admin
  - Username: `admin`
  - Password: `admin123`
- Operator
  - Username: `operator`
  - Password: `operator123`

## Catatan penting
- Password disimpan dalam bentuk hash menggunakan `password_hash()`.
- Semua form POST menggunakan CSRF token.
- Akses fitur admin seperti tambah, hapus, dan restore diproteksi di sisi server.
- File foto yang diupload akan disimpan di folder `uploads/` dengan nama terenkripsi.

## Cara penggunaan singkat
1. Login menggunakan akun demo.
2. Setelah login, Anda dapat melihat daftar dosen, mencari data, dan melakukan filter/sort.
3. Admin dapat menambah, mengedit, menghapus, dan restore data dosen.
4. Operator dapat melihat dan mengedit data, tetapi tidak bisa mengakses aksi admin yang sensitif.
5. Gunakan menu dashboard untuk melihat statistik dan export untuk mengunduh CSV.
>>>>>>> 0d71d54 (Initial commit)
