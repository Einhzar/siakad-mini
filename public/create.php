<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Validator.php';
require_once __DIR__ . '/../src/DosenRepository.php';

Auth::requireLogin();

if (($role = $_SESSION['role'] ?? '') !== 'admin') {
    http_response_code(403);
    echo 'Akses ditolak.';
    exit;
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$repo = new DosenRepository();
$errors = [];
$submitError = '';
$old = ['nidn' => '', 'nama' => '', 'email' => '', 'program_studi' => '', 'status' => 'aktif'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $old['nidn'] = trim($_POST['nidn'] ?? '');
    $old['nama'] = trim($_POST['nama'] ?? '');
    $old['email'] = trim($_POST['email'] ?? '');
    $old['program_studi'] = trim($_POST['program_studi'] ?? '');
    $old['status'] = trim($_POST['status'] ?? 'aktif');

    $errors['nidn'] = Validator::nidn($old['nidn']);
    $errors['nama'] = Validator::required($old['nama']);
    $errors['email'] = Validator::email($old['email']);
    $errors['program_studi'] = Validator::required($old['program_studi']);
    $errors['status'] = Validator::required($old['status']);

    if (empty($errors['nama']) && mb_strlen($old['nama']) > 100) {
        $errors['nama'] = 'Nama maksimal 100 karakter.';
    }

    if (empty($errors['email']) && empty($errors['nama']) && empty($errors['nidn']) && empty($errors['program_studi']) && empty($errors['status'])) {
        $foto = null;
        if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime = $finfo->file($_FILES['foto']['tmp_name']);
            if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp'], true)) {
                $errors['foto'] = 'Hanya file JPG/PNG/WebP yang diperbolehkan.';
            } else {
                $uploadDir = __DIR__ . '/../uploads';
                if (!is_dir($uploadDir)) {
                    mkdir($uploadDir, 0777, true);
                }
                $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
                $namaBaru = hash('sha256', uniqid((string)mt_rand(), true)) . '.' . $ext;
                move_uploaded_file($_FILES['foto']['tmp_name'], $uploadDir . '/' . $namaBaru);
                $foto = $namaBaru;
            }
        }

        if (empty($errors['foto'])) {
            try {
                $repo->create([
                    ':nidn' => $old['nidn'],
                    ':nama' => $old['nama'],
                    ':email' => $old['email'],
                    ':program_studi' => $old['program_studi'],
                    ':foto' => $foto,
                    ':status' => $old['status'],
                ]);
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                header('Location: index.php');
                exit;
            } catch (Throwable $e) {
                $submitError = 'Gagal menyimpan data: ' . $e->getMessage();
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Tambah Dosen</title>
</head>
<body>
    <h2>Tambah Dosen</h2>
    <?php if ($submitError !== ''): ?>
        <p style="color:red"><?= htmlspecialchars($submitError, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <p><a href="index.php">Kembali ke daftar</a></p>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
        <label>NIDN: <input type="text" name="nidn" value="<?= htmlspecialchars($old['nidn'], ENT_QUOTES, 'UTF-8') ?>"></label><br>
        <?php if (!empty($errors['nidn'])): ?><p style="color:red"><?= htmlspecialchars($errors['nidn'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><br>
        <label>Nama: <input type="text" name="nama" value="<?= htmlspecialchars($old['nama'], ENT_QUOTES, 'UTF-8') ?>"></label><br>
        <?php if (!empty($errors['nama'])): ?><p style="color:red"><?= htmlspecialchars($errors['nama'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><br>
        <label>Email: <input type="email" name="email" value="<?= htmlspecialchars($old['email'], ENT_QUOTES, 'UTF-8') ?>"></label><br>
        <?php if (!empty($errors['email'])): ?><p style="color:red"><?= htmlspecialchars($errors['email'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><br>
        <label>Program Studi:
            <select name="program_studi">
                <option value="Teknik Informatika" <?= $old['program_studi'] === 'Teknik Informatika' ? 'selected' : '' ?>>Teknik Informatika</option>
                <option value="Sistem Informasi" <?= $old['program_studi'] === 'Sistem Informasi' ? 'selected' : '' ?>>Sistem Informasi</option>
                <option value="Teknik Elektro" <?= $old['program_studi'] === 'Teknik Elektro' ? 'selected' : '' ?>>Teknik Elektro</option>
            </select>
        </label><br><br>
        <label>Foto: <input type="file" name="foto"></label><br>
        <?php if (!empty($errors['foto'])): ?><p style="color:red"><?= htmlspecialchars($errors['foto'], ENT_QUOTES, 'UTF-8') ?></p><?php endif; ?><br>
        <label>Status:
            <select name="status">
                <option value="aktif" <?= $old['status'] === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                <option value="nonaktif" <?= $old['status'] === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
            </select>
        </label><br><br>
        <button type="submit">Simpan</button>
    </form>
</body>
</html>
