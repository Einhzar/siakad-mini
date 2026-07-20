<?php
require_once __DIR__ . '/../src/Auth.php';
require_once __DIR__ . '/../config/database.php';

Auth::startSession();

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    try {
        $pdo = Database::getInstance();
        $stmt = $pdo->prepare('SELECT id, username, password_hash, role FROM users WHERE username = :username');
        $stmt->execute([':username' => $username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password_hash'])) {
            session_regenerate_id(true);
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['role'] = $user['role'];
            header('Location: index.php');
            exit;
        }

        $error = 'Username atau password salah.';
    } catch (Throwable $e) {
        $error = $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login SIAKAD Mini</title>
</head>
<body>
    <h2>Login SIAKAD Mini</h2>
    <p>Masuk untuk mengakses data dosen.</p>
    <?php if ($error): ?>
        <p style="color:red"><?= htmlspecialchars($error, ENT_QUOTES, 'UTF-8') ?></p>
    <?php endif; ?>
    <form method="post">
        <label>Username: <input type="text" name="username" required></label><br><br>
        <label>Password: <input type="password" name="password" required></label><br><br>
        <button type="submit">Login</button>
    </form>
</body>
</html>
