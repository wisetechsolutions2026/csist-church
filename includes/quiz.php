<?php
const QUIZ_MONTHS = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];

/**
 * Parse pasted quiz lines such as
 *   "2 Allen Athavan 1502 25 25 25 25 24 24 24 25 25 222"
 * (S.No, name, optional "Sub" number, one mark per month so far, total). "AAA" = absent.
 * Returns [rows, errors, warnings].
 */
function parse_quiz_text(string $text, int $months): array {
    $rows = [];
    $errors = [];
    $warnings = [];
    foreach (preg_split('/\R/', $text) as $n => $line) {
        $line = trim(preg_replace('/\s+/', ' ', $line));
        if ($line === '' || preg_match('/^S\.?\s*No\b/i', $line)) {
            continue;
        }
        $tok = explode(' ', $line);
        if (!ctype_digit($tok[0]) || count($tok) < 4) {
            $errors[] = 'Line ' . ($n + 1) . ' skipped: ' . $line;
            continue;
        }
        $sno = (int)array_shift($tok);
        $nameParts = [];
        while ($tok && !preg_match('/^\d/', $tok[0]) && strtoupper($tok[0]) !== 'AAA') {
            $nameParts[] = array_shift($tok);
        }
        $name = trim(implode(' ', $nameParts));
        $total = null;
        if ($tok) {
            $total = array_pop($tok);
        }
        $sub = null;
        if (count($tok) === $months + 1) {
            $sub = array_shift($tok);
        }
        if ($name === '' || count($tok) !== $months || $total === null || !is_numeric($total)) {
            $errors[] = 'Line ' . ($n + 1) . ' skipped (needs ' . $months . ' monthly marks): ' . $line;
            continue;
        }
        $marks = [];
        $sum = 0.0;
        $bad = false;
        foreach ($tok as $t) {
            if (strtoupper($t) === 'AAA') {
                $marks[] = null;
            } elseif (is_numeric($t) && (float)$t >= 0 && (float)$t <= 100) {
                $marks[] = (float)$t;
                $sum += (float)$t;
            } else {
                $bad = true;
            }
        }
        if ($bad) {
            $errors[] = 'Line ' . ($n + 1) . ' skipped (unreadable mark): ' . $line;
            continue;
        }
        if (abs($sum - (float)$total) > 0.01) {
            $warnings[] = $name . ': total in list is ' . $total . ' but the marks add up to ' . $sum . ' (the added-up value is used)';
        }
        $rows[] = ['sno' => $sno, 'name' => $name, 'sub' => $sub, 'marks' => $marks, 'total' => $sum];
    }
    return [$rows, $errors, $warnings];
}

function quiz_ensure_table(): void {
    db()->query("CREATE TABLE IF NOT EXISTS quiz_results (
      id INT AUTO_INCREMENT PRIMARY KEY,
      sno INT NOT NULL,
      name VARCHAR(150) NOT NULL,
      sub VARCHAR(20) DEFAULT NULL,
      marks VARCHAR(120) NOT NULL,
      total DECIMAL(6,1) NOT NULL DEFAULT 0
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

/** Replace all results with the parsed rows. marks are stored as comma-separated values, empty = absent. */
function quiz_import(array $rows, int $months): int {
    quiz_ensure_table();
    db()->query('DELETE FROM quiz_results');
    $stmt = db()->prepare('INSERT INTO quiz_results (sno, name, sub, marks, total) VALUES (?,?,?,?,?)');
    foreach ($rows as $r) {
        $marks = implode(',', array_map(fn($m) => $m === null ? '' : rtrim(rtrim(number_format($m, 1, '.', ''), '0'), '.'), $r['marks']));
        $sno = $r['sno'];
        $sub = $r['sub'];
        $total = $r['total'];
        $name = $r['name'];
        $stmt->bind_param('isssd', $sno, $name, $sub, $marks, $total);
        $stmt->execute();
    }
    $set = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES ('quiz_months', ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    $m = (string)$months;
    $set->bind_param('s', $m);
    $set->execute();
    return count($rows);
}

function quiz_load(): array {
    quiz_ensure_table();
    $out = [];
    $res = db()->query('SELECT * FROM quiz_results ORDER BY sno');
    while ($r = $res->fetch_assoc()) {
        $r['marks'] = array_map(fn($x) => $x === '' ? null : (float)$x, explode(',', $r['marks']));
        $out[] = $r;
    }
    return $out;
}
