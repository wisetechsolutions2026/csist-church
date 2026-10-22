<?php
$pageTitle = 'Verse of the Month';
require __DIR__ . '/_layout_top.php';

$flash = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $values = [
        'verse_enabled' => isset($_POST['enabled']) ? '1' : '0',
        'verse_title' => trim($_POST['verse_title'] ?? ''),
        'verse_text' => trim(str_replace("\r\n", "\n", $_POST['verse_text'] ?? '')),
        'verse_ref' => trim($_POST['verse_ref'] ?? ''),
    ];
    $stmt = db()->prepare("INSERT INTO settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value = VALUES(setting_value)");
    foreach ($values as $key => $value) {
        $stmt->bind_param('ss', $key, $value);
        $stmt->execute();
    }
    $flash = 'Saved.';
}

$get = fn($key) => db()->query("SELECT setting_value FROM settings WHERE setting_key = '" . db()->real_escape_string($key) . "'")->fetch_assoc()['setting_value'] ?? '';
$enabled = $get('verse_enabled');
$title = $get('verse_title');
$text = $get('verse_text');
$ref = $get('verse_ref');
?>

<div class="admin-card">
  <h2>Verse of the Month</h2>
  <p>Shown as the 2nd picture in the home page slider.</p>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post">
    <label><input type="checkbox" name="enabled" <?= $enabled === '1' ? 'checked' : '' ?>> Show this section on the home page</label>
    <label>Heading (shown on phones — on wide screens the picture already has its own heading)</label>
    <input type="text" name="verse_title" value="<?= h($title) ?>" placeholder="இம்மாத இறைவார்த்தை" required>
    <label>Verse</label>
    <textarea name="verse_text" rows="8" required><?= h($text) ?></textarea>
    <p style="font-size:0.8rem; color:#6b5a4d; margin-top:6px; line-height:1.6;">Each line you type stays on its own line. Put a word between stars, like <code>*மகிழ்ச்சி*</code>, to highlight it in gold.</p>
    <label>Reference</label>
    <input type="text" name="verse_ref" value="<?= h($ref) ?>" placeholder="e.g. திருப்பாடல் (சங்) 37:4-5">
    <button type="submit" class="btn" style="margin-top:24px;">Save</button>
  </form>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
