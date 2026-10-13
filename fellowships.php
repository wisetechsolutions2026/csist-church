<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Fellowships';
$slug = $_GET['slug'] ?? null;
$one = null;
if ($slug) {
    $stmt = db()->prepare('SELECT * FROM fellowships WHERE slug = ?');
    $stmt->bind_param('s', $slug);
    $stmt->execute();
    $one = $stmt->get_result()->fetch_assoc();
}
if ($one) {
    $pageTitle = $one['title'];
}
require __DIR__ . '/includes/header.php';
$base = base_url();
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
    <div style="margin-top: 34px;" class="reveal"><a class="btn" href="<?= $base ?>fellowships">&larr; All Fellowships</a></div>
  </div>
</section>

<?php else: ?>
<section>
  <div class="container">
    <div class="grid grid-3">
      <?php
      $all = db()->query('SELECT * FROM fellowships ORDER BY sort_order');
      $i = 0;
      while ($f = $all->fetch_assoc()):
        $i++;
      ?>
      <div class="fcard reveal reveal-delay-<?= min($i, 3) ?>">
        <div class="fcard-body">
          <h3><?= h($f['title']) ?></h3>
          <p style="color: var(--text-muted); font-style: italic;">"<?= h($f['tagline']) ?>"</p>
          <a href="<?= $base ?>fellowships?slug=<?= urlencode($f['slug']) ?>">Learn more &rarr;</a>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
