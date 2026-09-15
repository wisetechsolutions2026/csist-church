<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Gallery';
$activeCat = $_GET['cat'] ?? '';
$categories = db()->query('SELECT * FROM gallery_categories ORDER BY sort_order');
$cats = [];
while ($c = $categories->fetch_assoc()) { $cats[] = $c; }

if ($activeCat && !in_array($activeCat, array_column($cats, 'slug'), true)) {
    $activeCat = '';
}

if ($activeCat) {
    $stmt = db()->prepare('SELECT gi.filename, gi.alt, gc.folder FROM gallery_images gi JOIN gallery_categories gc ON gi.category_id = gc.id WHERE gc.slug = ? ORDER BY gi.id');
    $stmt->bind_param('s', $activeCat);
    $stmt->execute();
    $images = $stmt->get_result();
} else {
    $images = db()->query('SELECT gi.filename, gi.alt, gc.folder FROM gallery_images gi JOIN gallery_categories gc ON gi.category_id = gc.id ORDER BY gc.sort_order, gi.id');
}

require __DIR__ . '/includes/header.php';
$base = base_url();
?>

<div class="page-hero">
  <h1>Gallery</h1>
  <p>Take a peek at our festivals and programmes</p>
</div>

<section>
  <div class="container">
    <div class="category-tabs reveal">
      <a href="<?= $base ?>gallery.php" class="<?= $activeCat === '' ? 'active' : '' ?>">All</a>
      <?php foreach ($cats as $c): ?>
      <a href="<?= $base ?>gallery.php?cat=<?= urlencode($c['slug']) ?>" class="<?= $activeCat === $c['slug'] ? 'active' : '' ?>"><?= h($c['title']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="gallery-grid">
      <?php $i = 0; while ($img = $images->fetch_assoc()): $i++; ?>
      <a class="lightbox-link reveal reveal-delay-<?= min($i, 3) ?>" href="<?= $base ?>assets/img/gallery/<?= h($img['folder']) ?>/<?= h($img['filename']) ?>" data-caption="<?= h($img['alt']) ?>">
        <img src="<?= $base ?>assets/img/gallery/<?= h($img['folder']) ?>/<?= h($img['filename']) ?>" alt="<?= h($img['alt']) ?>" loading="lazy">
      </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
