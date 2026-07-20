<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../config/database.php';

Auth::requireLogin();

$pdo = Database::getInstance();
$search = trim($_GET['search'] ?? '');
$programStudi = trim($_GET['program_studi'] ?? '');
$status = trim($_GET['status'] ?? '');
$sort = $_GET['sort'] ?? 'created_at';
$order = $_GET['order'] ?? 'DESC';

$allowedSorts = ['nidn', 'nama', 'program_studi', 'status', 'created_at'];
$sort = in_array($sort, $allowedSorts, true) ? $sort : 'created_at';
$order = strtoupper($order) === 'ASC' ? 'ASC' : 'DESC';

$sql = 'SELECT nidn, nama, email, program_studi, status FROM dosen WHERE deleted_at IS NULL';
$params = [];

if ($search !== '') {
    $sql .= ' AND (nama LIKE :search OR nidn LIKE :search)';
    $params[':search'] = '%' . $search . '%';
}

if ($programStudi !== '') {
    $sql .= ' AND program_studi = :program_studi';
    $params[':program_studi'] = $programStudi;
}

if ($status !== '') {
    $sql .= ' AND status = :status';
    $params[':status'] = $status;
}

$sql .= ' ORDER BY ' . $sort . ' ' . $order;
$stmt = $pdo->prepare($sql);

if ($search !== '') {
    $stmt->bindValue(':search', $params[':search']);
}
if ($programStudi !== '') {
    $stmt->bindValue(':program_studi', $params[':program_studi']);
}
if ($status !== '') {
    $stmt->bindValue(':status', $params[':status']);
}
$stmt->execute();
$rows = $stmt->fetchAll();

header('Content-Type: text/csv; charset=utf-8');
header('Content-Disposition: attachment; filename="dosen.csv"');

$handle = fopen('php://output', 'w');
fputcsv($handle, ['NIDN', 'Nama', 'Email', 'Program Studi', 'Status']);
foreach ($rows as $row) {
    fputcsv($handle, [$row['nidn'], $row['nama'], $row['email'], $row['program_studi'], $row['status']]);
}
fclose($handle);
exit;
