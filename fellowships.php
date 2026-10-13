<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Fellowships';
$base = base_url();

$active = db()->query('SELECT * FROM fellowships WHERE is_active = 1 ORDER BY sort_order')->fetch_all(MYSQLI_ASSOC);
$slug = $_GET['slug'] ?? null;
$one = null;
if ($slug) {
    foreach ($active as $f) {
        if ($f['slug'] === $slug) { $one = $f; break; }
    }
    if (!$one) {
        header('Location: ' . $base . 'fellowships', true, 301);
        exit;
    }
} elseif (count($active) === 1) {
    $one = $active[0];
}
if ($one) {
    $pageTitle = $one['title'];
}
require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <h1><?= $one ? h($one['title']) : 'Fellowships' ?></h1>
  <p><?= $one ? h($one['tagline']) : 'Growing together in faith and community' ?></p>
</div>

<?php if ($one): ?>
<section>
  <div class="container fellowship-body">
    <div class="divider reveal"><span></span></div>
    <?= render_rich_text($one['description'] ?? '') ?>
    <?php if (count($active) > 1): ?>
    <div style="margin-top: 34px;" class="reveal"><a class="btn" href="<?= $base ?>fellowships">&larr; All Fellowships</a></div>
    <?php endif; ?>
  </div>
</section>

<?php else: ?>
<section>
  <div class="container">
    <div class="grid grid-3">
      <?php $i = 0; foreach ($active as $f): $i++; ?>
      <div class="fcard reveal reveal-delay-<?= min($i, 3) ?>">
        <div class="fcard-body">
          <h3><?= h($f['title']) ?></h3>
          <p style="color: var(--text-muted); font-style: italic;">"<?= h($f['tagline']) ?>"</p>
          <a href="<?= $base ?>fellowships?slug=<?= urlencode($f['slug']) ?>">Learn more &rarr;</a>
        </div>
      </div>
      <?php endforeach; ?>
      <?php if (!$active): ?><p style="grid-column:1/-1; text-align:center; color:var(--text-muted);">Coming soon.</p><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
