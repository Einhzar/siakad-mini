<?php
require_once __DIR__ . '/../src/Auth.php';
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

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo 'Method not allowed.';
    exit;
}

if (!hash_equals($_SESSION['csrf_token'], $_POST['csrf_token'] ?? '')) {
    die('CSRF token tidak valid.');
}

$repo = new DosenRepository();
$id = (int)($_POST['id'] ?? 0);
if ($id > 0) {
    $repo->softDelete($id);
}

header('Location: index.php');
exit;
