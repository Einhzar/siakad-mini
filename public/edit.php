<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/Validator.php';
require_once __DIR__ . '/../src/DosenRepository.php';

Auth::requireLogin();
Auth::startSession();

$repo = new DosenRepository();
$id = (int)($_GET['id'] ?? 0);
$dosen = $repo->find($id);

if (!$dosen) {
    die('Data dosen tidak ditemukan.');
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$errors = [];
$old = [
    'nidn' => $dosen['nidn'],
    'nama' => $dosen['nama'],
    'email' => $dosen['email'],
    'program_studi' => $dosen['program_studi'],
    'status' => $dosen['status'],
];

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

    if (empty($errors['nama']) && mb_strlen($old['nama']) > 100) {
        $errors['nama'] = 'Nama maksimal 100 karakter.';
    }

    if (empty($errors['nama']) && empty($errors['email']) && empty($errors['nidn'])) {
        $foto = $dosen['foto'];
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
            $repo->update($id, [
                ':nidn' => $old['nidn'],
                ':nama' => $old['nama'],
                ':email' => $old['email'],
                ':program_studi' => $old['program_studi'],
                ':foto' => $foto,
                ':status' => $old['status'],
            ]);
            $repo->setMatkul($id, array_map('intval', $_POST['matkul'] ?? []));
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            header('Location: index.php');
            exit;
        }
    }
}

$matkulList = $repo->getAllMatkul();
$selectedMatkul = $repo->getMatkulForDosen($id);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Edit Dosen</title>
</head>
<body>
    <h2>Edit Dosen</h2>
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
        <label>Mata Kuliah:</label><br>
        <?php foreach ($matkulList as $mk): ?>
            <label>
                <input type="checkbox" name="matkul[]" value="<?= (int)$mk['id'] ?>" <?= in_array((int)$mk['id'], $selectedMatkul, true) ? 'checked' : '' ?>>
                <?= htmlspecialchars($mk['nama'], ENT_QUOTES, 'UTF-8') ?>
            </label><br>
        <?php endforeach; ?>
        <br>
        <button type="submit">Simpan</button>
    </form>
</body>
</html>
