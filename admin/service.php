<?php
$pageTitle = 'Sunday Service Card';
require __DIR__ . '/_layout_top.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $enabled = isset($_POST['enabled']) ? '1' : '0';
    $date = trim($_POST['service_date'] ?? '');
    $morning = trim($_POST['service_morning'] ?? '');
    $evening = trim($_POST['service_evening'] ?? '');

    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ([
        'service_card_enabled' => $enabled,
        'service_date' => $date,
        'service_morning' => $morning,
        'service_evening' => $evening,
    ] as $key => $value) {
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
    }
    $flash = 'Saved.';
}

$get = fn($key) => db()->query("SELECT setting_value FROM settings WHERE setting_key = '" . db()->real_escape_string($key) . "'")->fetch_assoc()['setting_value'] ?? '';
$enabled = $get('service_card_enabled');
$date = $get('service_date');
$morning = $get('service_morning');
$evening = $get('service_evening');
?>

<div class="admin-card">
  <h2>Sunday Service Card</h2>
  <p>This controls the "This Sunday" card that floats on the home page, right below the hero slider.</p>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post">
    <label><input type="checkbox" name="enabled" <?= $enabled === '1' ? 'checked' : '' ?>> Show this card on the home page</label>
    <label>Date (e.g. 27th September 2026)</label>
    <input type="text" name="service_date" value="<?= h($date) ?>" required>
    <label>Morning Service Time</label>
    <input type="text" name="service_morning" value="<?= h($morning) ?>" required>
    <label>Evening Service Time</label>
    <input type="text" name="service_evening" value="<?= h($evening) ?>" required>
    <button type="submit" class="btn" style="margin-top:20px;">Save</button>
  </form>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
