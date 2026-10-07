<?php
$pageTitle = 'Sunday Service Card';
require __DIR__ . '/_layout_top.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [
        'service_card_enabled' => isset($_POST['enabled']) ? '1' : '0',
        'service_date' => trim($_POST['service_date'] ?? ''),
        'service_early' => trim($_POST['service_early'] ?? ''),
        'service_morning' => trim($_POST['service_morning'] ?? ''),
        'service_evening' => trim($_POST['service_evening'] ?? ''),
        'special_enabled' => isset($_POST['special_enabled']) ? '1' : '0',
        'special_name' => trim($_POST['special_name'] ?? ''),
        'special_date' => trim($_POST['special_date'] ?? ''),
        'special_time' => trim($_POST['special_time'] ?? ''),
    ];
    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($values as $key => $value) {
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
    }
    $flash = 'Saved.';
}

$get = fn($key) => db()->query("SELECT setting_value FROM settings WHERE setting_key = '" . db()->real_escape_string($key) . "'")->fetch_assoc()['setting_value'] ?? '';
$enabled = $get('service_card_enabled');
$date = $get('service_date');
$early = $get('service_early');
$morning = $get('service_morning');
$evening = $get('service_evening');
$spEnabled = $get('special_enabled');
$spName = $get('special_name');
$spDate = $get('special_date');
$spTime = $get('special_time');
?>

<div class="admin-card">
  <h2>Sunday Service Card</h2>
  <p>This controls the "This Sunday" card on the home page and in the opening splash.</p>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post">
    <label><input type="checkbox" name="enabled" <?= $enabled === '1' ? 'checked' : '' ?>> Show this card on the home page</label>
    <label>Service Date</label>
    <input type="date" name="service_date" value="<?= h($date) ?>" required>
    <p style="font-size:0.8rem; color:#6b5a4d; margin-top:4px;">Will display on the home page as: <strong><?= h($date ? format_ordinal_date($date) : '—') ?></strong></p>
    <label>Early Morning Service Time (optional — leave empty to hide)</label>
    <input type="text" name="service_early" value="<?= h($early) ?>" placeholder="e.g. 5:30 AM">
    <label>Morning Service Time</label>
    <input type="text" name="service_morning" value="<?= h($morning) ?>" required>
    <label>Evening Service Time</label>
    <input type="text" name="service_evening" value="<?= h($evening) ?>" required>

    <h2 style="margin-top:34px; font-size:1.35rem;">Special Service (monthly)</h2>
    <p class="hint" style="font-size:0.85rem; color:#6b5a4d; margin-top:6px;">Once a month, in the first week, one extra service is held. Name it, pick the day and time. It shows under the Sunday timings and hides itself after its date.</p>
    <label><input type="checkbox" name="special_enabled" <?= $spEnabled === '1' ? 'checked' : '' ?>> Show the special service</label>
    <label>Service Name</label>
    <input type="text" name="special_name" value="<?= h($spName) ?>" placeholder="e.g. Communion Service">
    <label>Date</label>
    <input type="date" name="special_date" value="<?= h($spDate) ?>">
    <p style="font-size:0.8rem; color:#6b5a4d; margin-top:4px;">Will display as: <strong><?= h($spDate ? format_ordinal_date($spDate) : '—') ?></strong></p>
    <label>Time</label>
    <input type="text" name="special_time" value="<?= h($spTime) ?>" placeholder="e.g. 7:00 PM">

    <button type="submit" class="btn" style="margin-top:24px;">Save</button>
  </form>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
