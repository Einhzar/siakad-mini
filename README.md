# SIAKAD Mini
https://github.com/Einhzar/siakad-mini.git

SIAKAD Mini adalah aplikasi web sederhana yang dibangun untuk memenuhi tugas UTS Pemrograman Web. Aplikasi ini menyediakan fitur autentikasi, manajemen data dosen, soft delete, upload foto, proteksi CSRF, role-based access control, relasi many-to-many antara dosen dan mata kuliah, serta fitur tambahan seperti pencarian, filter, sorting, dashboard, dan export data ke CSV.

## Identitas
- Nama: Dimas Abiyyu Febriyan
- NIM: 2024150145
- Mata Kuliah: Pemrograman Web
- Tugas: UTS

## Fitur utama
- Login dan logout dengan password yang di-hash
- Akses halaman yang diproteksi berdasarkan role
- CRUD data dosen
- Soft delete dan restore data dosen
- Upload foto dengan validasi tipe file
- Proteksi CSRF untuk form POST
- Relasi many-to-many antara dosen dan mata kuliah
- Pencarian, filter, sort, dan pagination
- Dashboard statistik
- Export data dosen ke format CSV

## Struktur folder
- config/ : konfigurasi database
- public/ : halaman utama aplikasi
- src/ : kelas seperti Auth, DosenRepository, dan Validator
- uploads/ : tempat penyimpanan file foto dosen
- seed.sql : file SQL untuk membuat skema dan data awal

## Persiapan lingkungan
Pastikan lingkungan Anda sudah memiliki:
- PHP 8.x
- MySQL/MariaDB
- Web server seperti Apache atau Nginx

## Langkah setup
1. Buat database dengan nama `siakad_mini`.
2. Impor file `seed.sql` ke database tersebut.
3. Jika perlu, sesuaikan konfigurasi database di file `config/database.php`.
4. Jalankan web server dari folder `public`, misalnya:
   - `php -S localhost:8000 -t public`
   lalu buka `http://localhost:8000`
   (atau jika menggunakan Apache/XAMPP: `http://localhost/siakad-mini/public/index.php`)

## Akun demo
- Admin
  - Username: `admin`
  - Password: `admin123`
- Operator
  - Username: `operator`
  - Password: `operator123`

## Catatan penting
- Password disimpan dalam bentuk hash menggunakan `password_hash()`.
- Semua form POST menggunakan token CSRF.
- Fitur admin seperti tambah, hapus, dan restore diproteksi di sisi server.
- File foto yang diupload disimpan di folder `uploads/` dengan nama terenkripsi.

## Cara penggunaan singkat
1. Login menggunakan akun demo.
2. Setelah login, Anda bisa melihat daftar dosen, mencari data, dan melakukan filter serta sort.
3. Admin dapat menambah, mengedit, menghapus, dan restore data dosen.
4. Operator dapat melihat dan mengedit data, tetapi tidak dapat mengakses aksi admin yang sensitif.
5. Gunakan menu dashboard untuk melihat statistik, serta fitur export untuk mengunduh data CSV.
