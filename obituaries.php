<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/obituary.php';
$pageTitle = 'Obituary';
$base = base_url();

obit_ensure_table();
$limit = (new DateTime(today_ist(), new DateTimeZone('Asia/Kolkata')))->modify('-1 day')->format('Y-m-d');
$all = db()->query('SELECT * FROM obituaries WHERE is_active = 1 ORDER BY (service_date IS NULL) DESC, service_date DESC, id DESC')->fetch_all(MYSQLI_ASSOC);
$recent = [];
$past = [];
foreach ($all as $o) {
    if ($o['service_date'] === null || $o['service_date'] >= $limit) { $recent[] = $o; } else { $past[] = $o; }
}
usort($recent, fn($a, $b) => strcmp((string)$a['service_date'], (string)$b['service_date']));

$preview = null;
if (isset($_GET['preview'])) {
    require_once __DIR__ . '/includes/admin-auth.php';
    if (admin_logged_in()) {
        $pid = (int)$_GET['preview'];
        $stmt = db()->prepare('SELECT * FROM obituaries WHERE id = ?');
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $preview = $stmt->get_result()->fetch_assoc() ?: null;
    }
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <h1>Obituary</h1>
  <p>Remembering our beloved church members</p>
</div>

<section>
  <div class="container obit-wrap">
    <?php if ($preview): ?>
      <p class="obit-preview">Preview &mdash; only you (logged in) can see this<?= $preview['is_active'] ? '' : '; it is currently hidden from visitors' ?>.</p>
      <div class="obit-list" style="margin-bottom:40px;"><?php render_obit_card($preview, $base); ?></div>
    <?php endif; ?>
    <?php if ($recent): ?>
      <h2 class="obit-heading reveal">Recent notices</h2>
      <div class="obit-list">
        <?php foreach ($recent as $o) { render_obit_card($o, $base); } ?>
      </div>
    <?php endif; ?>

    <?php if ($past): ?>
      <h2 class="obit-heading reveal">In loving memory</h2>
      <div class="obit-list">
        <?php foreach ($past as $o) { render_obit_card($o, $base, true); } ?>
      </div>
    <?php endif; ?>

    <?php if (!$recent && !$past): ?>
      <p style="text-align:center; color:var(--text-muted);">There are no notices at this time.</p>
    <?php endif; ?>

    <p class="obit-verse reveal">&ldquo;Blessed are the dead who die in the Lord.&rdquo; <span>Revelation 14:13</span></p>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
