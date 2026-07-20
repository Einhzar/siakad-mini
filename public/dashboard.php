<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../config/database.php';

Auth::requireLogin();

$pdo = Database::getInstance();

$stats = [];
try {
    $stats['dosen_per_prodi'] = $pdo->query(
        "SELECT program_studi, COUNT(*) AS total FROM dosen WHERE deleted_at IS NULL GROUP BY program_studi ORDER BY program_studi"
    )->fetchAll();

    $stats['status_dosen'] = $pdo->query(
        "SELECT status, COUNT(*) AS total FROM dosen WHERE deleted_at IS NULL GROUP BY status ORDER BY status"
    )->fetchAll();

    $stats['total_sks'] = $pdo->query(
        "SELECT COALESCE(SUM(mk.sks), 0) AS total_sks FROM dosen_matakuliah dm JOIN mata_kuliah mk ON mk.id = dm.matakuliah_id"
    )->fetchColumn();
} catch (Throwable $e) {
    $stats = [];
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard SIAKAD</title>
</head>
<body>
    <h2>Dashboard</h2>
    <p><a href="index.php">Kembali</a></p>
    <h3>Total SKS yang Diampu: <?= (int)($stats['total_sks'] ?? 0) ?></h3>
    <h3>Jumlah Dosen per Program Studi</h3>
    <ul>
        <?php foreach ($stats['dosen_per_prodi'] ?? [] as $row): ?>
            <li><?= htmlspecialchars($row['program_studi'], ENT_QUOTES, 'UTF-8') ?>: <?= (int)$row['total'] ?></li>
        <?php endforeach; ?>
    </ul>
    <h3>Jumlah Dosen Aktif vs Nonaktif</h3>
    <ul>
        <?php foreach ($stats['status_dosen'] ?? [] as $row): ?>
            <li><?= htmlspecialchars($row['status'], ENT_QUOTES, 'UTF-8') ?>: <?= (int)$row['total'] ?></li>
        <?php endforeach; ?>
    </ul>
</body>
</html>
