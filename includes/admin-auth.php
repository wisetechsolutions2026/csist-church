<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/admin-credentials.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function admin_logged_in(): bool {
    return !empty($_SESSION['admin_logged_in']);
}

function require_admin(): void {
    if (!admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}
