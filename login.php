<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';
if (isset($_SESSION['user_id'])) { header('Location: ' . ($_SESSION['user_role'] === 'admin' ? 'admin/' : 'dashboard.php')); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $statement = $pdo->prepare('SELECT id, name, password_hash, role FROM users WHERE email = :email LIMIT 1');
    $statement->execute(['email' => trim((string)$_POST['email'])]);
    $user = $statement->fetch();
    if ($user && password_verify((string)$_POST['password'], $user['password_hash'])) {
        session_regenerate_id(true);
        $_SESSION['user_id'] = (int)$user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        if ($user['role'] === 'admin') {
            $_SESSION['admin_id'] = (int)$user['id'];
            $_SESSION['admin_name'] = $user['name'];
        }
        header('Location: ' . ($user['role'] === 'admin' ? 'admin/' : 'dashboard.php'));
        exit;
    }
    $error = 'Email or password is incorrect.';
}
?><!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>GlobeTrotter Login</title><link rel="stylesheet" href="auth.css"></head><body><main class="auth-box"><p class="eyebrow">GlobeTrotter</p><h1>Welcome back</h1><p class="muted">Sign in to continue planning your trips.</p><?php if ($error): ?><p class="message error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?><form method="post"><label>Email<input type="email" name="email" required></label><label>Password<input type="password" name="password" required></label><button type="submit">Log in</button></form><p class="auth-link">New here <a href="register.php">Create an account</a></p></main></body></html>
