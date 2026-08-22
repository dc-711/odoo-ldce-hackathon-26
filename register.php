<?php
declare(strict_types=1);
session_start();
require_once __DIR__ . '/db.php';
if (isset($_SESSION['user_id'])) { header('Location: index.php'); exit; }
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim((string)$_POST['name']);
    $email = trim((string)$_POST['email']);
    $password = (string)$_POST['password'];
    if ($name === '' || !filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 6) {
        $error = 'Enter a name, valid email, and password of at least six characters.';
    } else {
        try {
            $statement = $pdo->prepare('INSERT INTO users (name, email, password_hash, role) VALUES (:name, :email, :password_hash, "user")');
            $statement->execute(['name' => $name, 'email' => $email, 'password_hash' => password_hash($password, PASSWORD_DEFAULT)]);
            header('Location: login.php');
            exit;
        } catch (PDOException $exception) { $error = 'That email is already registered.'; }
    }
}
?><!doctype html><html lang="en"><head><meta charset="UTF-8"><meta name="viewport" content="width=device-width, initial-scale=1.0"><title>GlobeTrotter Register</title><link rel="stylesheet" href="auth.css"></head><body><main class="auth-box"><p class="eyebrow">GlobeTrotter</p><h1>Create account</h1><p class="muted">Start building your next journey.</p><?php if ($error): ?><p class="message error"><?php echo htmlspecialchars($error); ?></p><?php endif; ?><form method="post"><label>Name<input name="name" required></label><label>Email<input type="email" name="email" required></label><label>Password<input type="password" name="password" minlength="6" required></label><button type="submit">Register</button></form><p class="auth-link">Already registered <a href="login.php">Log in</a></p></main></body></html>
