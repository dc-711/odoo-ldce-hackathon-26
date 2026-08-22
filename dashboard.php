<?php
declare(strict_types=1);
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}
if (($_SESSION['user_role'] ?? 'user') === 'admin') {
    header('Location: admin/');
    exit;
}
require __DIR__ . '/index.html';
