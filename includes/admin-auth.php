<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';
require_once __DIR__ . '/admin-credentials.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function find_admin_user(string $username): ?array {
    try {
        $stmt = db()->prepare('SELECT id, username, display_name, password_hash, role FROM admin_users WHERE username = ? LIMIT 1');
        $stmt->bind_param('s', $username);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        return $row ?: null;
    } catch (Throwable $e) {
        return null;
    }
}

function admin_users_table_has_rows(): bool {
    try {
        return (int)db()->query('SELECT COUNT(*) c FROM admin_users')->fetch_assoc()['c'] > 0;
    } catch (Throwable $e) {
        return false;
    }
}

function current_admin(): ?array {
    static $user = false;
    if ($user !== false) {
        return $user;
    }
    $user = null;
    if (empty($_SESSION['admin_logged_in'])) {
        return null;
    }
    if (!empty($_SESSION['admin_id'])) {
        try {
            $stmt = db()->prepare('SELECT id, username, display_name, role FROM admin_users WHERE id = ? LIMIT 1');
            $id = (int)$_SESSION['admin_id'];
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();
        } catch (Throwable $e) {
            $row = null;
        }
        if ($row) {
            $user = $row;
            return $user;
        }
        unset($_SESSION['admin_logged_in'], $_SESSION['admin_id'], $_SESSION['admin_username'], $_SESSION['admin_role']);
        return null;
    }
    $user = [
        'id' => 0,
        'username' => $_SESSION['admin_username'] ?? '',
        'display_name' => $_SESSION['admin_username'] ?? '',
        'role' => $_SESSION['admin_role'] ?? '',
    ];
    return $user;
}

function admin_logged_in(): bool {
    return current_admin() !== null;
}

function admin_role(): string {
    $u = current_admin();
    return $u['role'] ?? '';
}

function is_super_admin(): bool {
    return admin_role() === 'super';
}

function require_admin(): void {
    if (!admin_logged_in()) {
        header('Location: login');
        exit;
    }
}

function require_super_admin(): void {
    require_admin();
    if (!is_super_admin()) {
        header('Location: celebrations');
        exit;
    }
}
