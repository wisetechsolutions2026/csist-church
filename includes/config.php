<?php
declare(strict_types=1);

require_once __DIR__ . '/db-credentials.php';

function db(): mysqli {
    static $conn = null;
    if ($conn === null) {
        $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        if ($conn->connect_error) {
            http_response_code(500);
            die('Database connection failed.');
        }
        $conn->set_charset('utf8mb4');
    }
    return $conn;
}

function h(?string $value): string {
    return htmlspecialchars($value ?? '', ENT_QUOTES, 'UTF-8');
}

function setting(string $key, string $default = ''): string {
    static $cache = null;
    if ($cache === null) {
        $cache = [];
        $result = db()->query('SELECT setting_key, setting_value FROM settings');
        while ($row = $result->fetch_assoc()) {
            $cache[$row['setting_key']] = $row['setting_value'];
        }
    }
    return $cache[$key] ?? $default;
}

function format_ordinal_date(string $ymd): string {
    if ($ymd === '') {
        return '';
    }
    $ts = strtotime($ymd);
    if ($ts === false) {
        return $ymd;
    }
    $day = (int)date('j', $ts);
    if ($day % 10 === 1 && $day !== 11) {
        $suffix = 'st';
    } elseif ($day % 10 === 2 && $day !== 12) {
        $suffix = 'nd';
    } elseif ($day % 10 === 3 && $day !== 13) {
        $suffix = 'rd';
    } else {
        $suffix = 'th';
    }
    return $day . $suffix . date(' F Y', $ts);
}

function base_url(): string {
    $dir = rtrim(str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'])), '/');
    return $dir === '' ? '/' : $dir . '/';
}

function today_ist(): string {
    return (new DateTime('now', new DateTimeZone('Asia/Kolkata')))->format('Y-m-d');
}

function celebration_is_expired(?string $date): bool {
    return $date !== null && preg_match('/^\d{4}-\d{2}-\d{2}$/', $date) === 1 && $date < today_ist();
}
