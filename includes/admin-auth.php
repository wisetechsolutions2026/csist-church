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

function admin_role(): string {
    return $_SESSION['admin_role'] ?? '';
}

function is_super_admin(): bool {
    return admin_role() === 'super';
}

function require_admin(): void {
    if (!admin_logged_in()) {
        header('Location: login.php');
        exit;
    }
}

function require_super_admin(): void {
    require_admin();
    if (!is_super_admin()) {
        header('Location: celebrations.php');
        exit;
    }
}
