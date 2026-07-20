<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../src/DosenRepository.php';

Auth::requireLogin();

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$role = $_SESSION['role'] ?? 'operator';
$error = '';
$repo = null;
$search = trim($_GET['search'] ?? '');
$programStudi = trim($_GET['program_studi'] ?? '');
$status = trim($_GET['status'] ?? '');
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';
$page = max(1, (int)($_GET['page'] ?? 1));
$perPage = 5;
$offset = ($page - 1) * $perPage;
$filters = ['program_studi' => $programStudi, 'status' => $status];

try {
    $repo = new DosenRepository();
    $dosenList = $repo->listWithFilters($search, $filters, $sort, $order, $perPage, $offset);
    $total = $repo->countWithFilters($search, $filters);
    $totalPages = (int)ceil($total / $perPage);
} catch (Throwable $e) {
    $error = $e->getMessage();
    $dosenList = [];
    $totalPages = 1;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Daftar Dosen</title>
</head>
<body>
    <h2>Daftar Dosen</h2>
    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <p>Login sebagai: <?= htmlspecialchars($_SESSION['username'] ?? '', ENT_QUOTES, 'UTF-8') ?></p>
    <p><a href="logout.php">Logout</a></p>
    <form method="get">
        <input type="text" name="search" value="<?= htmlspecialchars($search, ENT_QUOTES, 'UTF-8') ?>" placeholder="Cari nama/NIDN">
        <select name="program_studi">
            <option value="">Semua Program Studi</option>
            <option value="Teknik Informatika" <?= $programStudi === 'Teknik Informatika' ? 'selected' : '' ?>>Teknik Informatika</option>
            <option value="Sistem Informasi" <?= $programStudi === 'Sistem Informasi' ? 'selected' : '' ?>>Sistem Informasi</option>
            <option value="Teknik Elektro" <?= $programStudi === 'Teknik Elektro' ? 'selected' : '' ?>>Teknik Elektro</option>
        </select>
        <select name="status">
            <option value="">Semua Status</option>
            <option value="aktif" <?= $status === 'aktif' ? 'selected' : '' ?>>Aktif</option>
            <option value="nonaktif" <?= $status === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
        </select>
        <select name="sort">
            <option value="created_at" <?= $sort === 'created_at' ? 'selected' : '' ?>>Tanggal Dibuat</option>
            <option value="nama" <?= $sort === 'nama' ? 'selected' : '' ?>>Nama</option>
            <option value="nidn" <?= $sort === 'nidn' ? 'selected' : '' ?>>NIDN</option>
        </select>
        <select name="order">
            <option value="ASC" <?= $order === 'ASC' ? 'selected' : '' ?>>Ascending</option>
            <option value="DESC" <?= $order === 'DESC' ? 'selected' : '' ?>>Descending</option>
        </select>
        <button type="submit">Cari</button>
    </form>
    <?php if ($role === 'admin'): ?>
        <p><a href="create.php">Tambah Dosen</a></p>
    <?php endif; ?>
    <p><a href="dashboard.php">Lihat Dashboard</a> | <a href="export.php?search=<?= urlencode($search) ?>&program_studi=<?= urlencode($programStudi) ?>&status=<?= urlencode($status) ?>&sort=<?= urlencode($sort) ?>&order=<?= urlencode($order) ?>">Export CSV</a></p>
    <table border="1" cellpadding="5">
        <tr>
            <th>NIDN</th><th>Nama</th><th>Email</th><th>Program Studi</th><th>Status</th><th>MK Diampu</th><th>Aksi</th>
        </tr>
        <?php foreach ($dosenList as $dosen): ?>
            <tr>
                <td><?= htmlspecialchars($dosen['nidn'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($dosen['nama'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($dosen['email'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($dosen['program_studi'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= htmlspecialchars($dosen['status'], ENT_QUOTES, 'UTF-8') ?></td>
                <td><?= (int)($dosen['matakuliah_count'] ?? 0) ?></td>
                <td>
                    <a href="edit.php?id=<?= (int)$dosen['id'] ?>">Edit</a>
                    <?php if ($role === 'admin'): ?>
                        |
                        <form method="post" action="delete.php" style="display:inline;" onsubmit="return confirm('Soft delete dosen ini?')">
                            <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($_SESSION['csrf_token'], ENT_QUOTES, 'UTF-8') ?>">
                            <input type="hidden" name="id" value="<?= (int)$dosen['id'] ?>">
                            <button type="submit" style="background:none; border:none; color:blue; padding:0; cursor:pointer;">Delete</button>
                        </form>
                    <?php endif; ?>
                </td>
            </tr>
        <?php endforeach; ?>
    </table>
    <p>Halaman <?= $page ?> dari <?= $totalPages ?></p>
    <?php if ($page > 1): ?>
        <a href="index.php?search=<?= urlencode($search) ?>&program_studi=<?= urlencode($programStudi) ?>&status=<?= urlencode($status) ?>&sort=<?= urlencode($sort) ?>&order=<?= urlencode($order) ?>&page=<?= $page - 1 ?>">Sebelumnya</a>
    <?php endif; ?>
    <?php if ($page < $totalPages): ?>
        <a href="index.php?search=<?= urlencode($search) ?>&program_studi=<?= urlencode($programStudi) ?>&status=<?= urlencode($status) ?>&sort=<?= urlencode($sort) ?>&order=<?= urlencode($order) ?>&page=<?= $page + 1 ?>">Berikutnya</a>
    <?php endif; ?>
</body>
</html>
