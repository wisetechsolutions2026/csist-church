<?php
$pageTitle = 'Site Info';
$requireSuper = true;
require __DIR__ . '/_layout_top.php';

$flash = '';
$fields = [
    'site_name' => 'Church Name',
    'site_location' => 'Location (e.g. Porur, Chennai)',
    'tagline' => 'Tagline',
    'address' => 'Full Address',
    'email' => 'Contact Email',
    'phone' => 'Contact Phone',
    'youtube_channel' => 'YouTube Channel URL',
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach (array_keys($fields) as $key) {
        $value = trim($_POST[$key] ?? '');
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
    }
    $historyKey = 'history';
    $history = trim($_POST['history'] ?? '');
    $stmt->bind_param('ss', $historyKey, $history);
    $stmt->execute();
    $flash = 'Saved.';
}

$get = fn($key) => db()->query("SELECT setting_value FROM settings WHERE setting_key = '" . db()->real_escape_string($key) . "'")->fetch_assoc()['setting_value'] ?? '';
?>

<div class="admin-card">
  <h2>Site Info</h2>
  <p>Basic details shown across the site (header, footer, contact page).</p>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post">
    <?php foreach ($fields as $key => $label): ?>
      <label><?= h($label) ?></label>
      <input type="text" name="<?= h($key) ?>" value="<?= h($get($key)) ?>" required>
    <?php endforeach; ?>

    <label>Our History (used on the About page — separate paragraphs with a blank line)</label>
    <textarea name="history" rows="10"><?= h($get('history')) ?></textarea>

    <button type="submit" class="btn" style="margin-top:20px;">Save</button>
  </form>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
