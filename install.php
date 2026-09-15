<?php
declare(strict_types=1);
require_once __DIR__ . '/includes/db-credentials.php';

header('Content-Type: text/plain');

$mysqli = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
$mysqli->set_charset('utf8mb4');

function run_sql_file(mysqli $mysqli, string $path): void {
    $sql = file_get_contents($path);
    // Strip CREATE DATABASE / USE statements — the DB already exists on the host.
    $sql = preg_replace('/^\s*(CREATE DATABASE.*?;|USE .*?;)\s*$/im', '', $sql);

    if ($mysqli->multi_query($sql)) {
        do {
            if ($result = $mysqli->store_result()) {
                $result->free();
            }
        } while ($mysqli->more_results() && $mysqli->next_result());
    }
    if ($mysqli->error) {
        die("Error running " . basename($path) . ": " . $mysqli->error);
    }
    echo basename($path) . " imported successfully.\n";
}

run_sql_file($mysqli, __DIR__ . '/schema.sql');
run_sql_file($mysqli, __DIR__ . '/seed.sql');

echo "\nDatabase setup complete.\n";

$mysqli->close();

// Self-delete for security — this script should only ever run once.
@unlink(__FILE__);
echo "This installer has deleted itself.\n";
