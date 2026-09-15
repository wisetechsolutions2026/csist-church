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
require __DIR__ . '/includes/header.php';
$base = base_url();
?>

<div class="page-hero">
  <h1><?= $one ? h($one['title']) : 'Fellowships' ?></h1>
  <p><?= $one ? h($one['tagline']) : 'Growing together in faith and community' ?></p>
</div>

<?php if ($one): ?>
<section>
  <div class="container" style="max-width: 780px;">
    <div class="divider reveal"><span></span></div>
    <p class="reveal"><?= h($one['description']) ?></p>
    <div class="gallery-grid" style="margin-top: 30px;">
      <?php
      $imgs = db()->query("SELECT gi.filename, gi.alt FROM gallery_images gi JOIN gallery_categories gc ON gi.category_id = gc.id WHERE gc.slug = '" . db()->real_escape_string($one['gallery_slug']) . "'");
      $i = 0;
      while ($img = $imgs->fetch_assoc()): $i++;
      ?>
      <a class="lightbox-link reveal reveal-delay-<?= min($i, 3) ?>" href="<?= $base ?>assets/img/gallery/<?= h($one['gallery_slug']) ?>/<?= h($img['filename']) ?>" data-caption="<?= h($img['alt']) ?>">
        <img src="<?= $base ?>assets/img/gallery/<?= h($one['gallery_slug']) ?>/<?= h($img['filename']) ?>" alt="<?= h($img['alt']) ?>">
      </a>
      <?php endwhile; ?>
    </div>
    <div style="margin-top: 34px;" class="reveal"><a class="btn" href="<?= $base ?>fellowships.php">&larr; All Fellowships</a></div>
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
        $img = db()->query("SELECT gi.filename FROM gallery_images gi JOIN gallery_categories gc ON gi.category_id=gc.id WHERE gc.slug='" . db()->real_escape_string($f['gallery_slug']) . "' LIMIT 1")->fetch_assoc();
      ?>
      <div class="fcard reveal reveal-delay-<?= min($i, 3) ?>">
        <?php if ($img): ?>
        <div class="fimg-wrap">
          <img src="<?= $base ?>assets/img/gallery/<?= h($f['gallery_slug']) ?>/<?= h($img['filename']) ?>" alt="<?= h($f['title']) ?>">
        </div>
        <?php endif; ?>
        <div class="fcard-body">
          <h3><?= h($f['title']) ?></h3>
          <p style="color: var(--text-muted); font-style: italic;">"<?= h($f['tagline']) ?>"</p>
          <a href="<?= $base ?>fellowships.php?slug=<?= urlencode($f['slug']) ?>">Learn more &rarr;</a>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
