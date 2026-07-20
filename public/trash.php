<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/DosenRepository.php';

Auth::requireLogin();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$repo = new DosenRepository();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (($_SESSION['role'] ?? '') !== 'admin') {
        http_response_code(403);
        echo 'Akses ditolak.';
        exit;
    }

    if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
        die('CSRF token tidak valid.');
    }

    $repo->restore((int)($_POST['restore_id'] ?? 0));
    header('Location: trash.php');
    exit;
}

$trash = $repo->trash();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Sampah Dosen</title>
</head>
<body>
    <h2>Daftar Sampah Dosen</h2>
    <a href="index.php">Kembali</a>
    <table border="1" cellpadding="5">
        <tr><th>Nama</th><th>NIDN</th><th>Aksi</th></tr>
        <?php foreach ($trash as $dosen): ?>
            <tr>
                <td><?= htmlspecialchars($dosen['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($dosen['nidn'], ENT_QUOTES, 'UTF-8') ?></td>
                <td>
                    <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
                        <form method="post" style="display:inline;">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="restore_id" value="<?= (int)$dosen['id'] ?>">
                            <button type="submit">Restore</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
</body>
</html>
