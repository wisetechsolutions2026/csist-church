<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Monthly Magazine';
$mags = db()->query('SELECT * FROM magazines ORDER BY sort_order');
require __DIR__ . '/includes/header.php';
$base = base_url();
?>

<div class="page-hero">
  <h1>Monthly Magazine</h1>
  <p>Click a magazine to download</p>
</div>

<section>
  <div class="container">
    <div class="magazine-grid">
      <?php $i = 0; while ($m = $mags->fetch_assoc()): $i++; ?>
      <a class="magazine-card reveal reveal-delay-<?= min($i, 3) ?>" href="<?= $base ?>assets/pdf/<?= h($m['filename']) ?>" target="_blank">
        <div class="icon">&#128196;</div>
        <div><?= h($m['title']) ?></div>
        <div style="color: var(--sapphire); font-family:'Cormorant Garamond', serif; font-weight: 700;"><?= h($m['period']) ?></div>
      </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
