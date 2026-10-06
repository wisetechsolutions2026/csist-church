<?php
require_once __DIR__ . '/includes/config.php';

$eventId = (int)($_GET['event'] ?? 0);
$event = null;
if ($eventId > 0) {
    $stmt = db()->prepare('SELECT e.*, c.title AS cat_title, c.slug AS cat_slug FROM gallery_events e JOIN gallery_categories c ON e.category_id = c.id WHERE e.id = ?');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
}

$pageTitle = $event ? $event['title'] : 'Gallery';
$base = base_url();

if ($event) {
    $stmt = db()->prepare('SELECT gi.filename, gi.alt, gc.folder FROM gallery_images gi JOIN gallery_categories gc ON gi.category_id = gc.id WHERE gi.event_id = ? ORDER BY gi.id');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
} else {
    $activeCat = $_GET['cat'] ?? '';
    $cats = db()->query('SELECT * FROM gallery_categories ORDER BY sort_order')->fetch_all(MYSQLI_ASSOC);
    if ($activeCat && !in_array($activeCat, array_column($cats, 'slug'), true)) {
        $activeCat = '';
    }
    $sql = "SELECT e.id, e.title, e.event_date, c.title AS cat_title, c.folder,
                   (SELECT COUNT(*) FROM gallery_images gi WHERE gi.event_id = e.id) AS cnt,
                   COALESCE(e.cover, (SELECT gi.filename FROM gallery_images gi WHERE gi.event_id = e.id ORDER BY gi.id LIMIT 1)) AS thumb
            FROM gallery_events e JOIN gallery_categories c ON e.category_id = c.id";
    if ($activeCat) {
        $stmt = db()->prepare($sql . ' WHERE c.slug = ? HAVING cnt > 0 ORDER BY (e.event_date IS NULL), e.event_date DESC, e.id DESC');
        $stmt->bind_param('s', $activeCat);
        $stmt->execute();
        $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    } else {
        $events = db()->query($sql . ' HAVING cnt > 0 ORDER BY (e.event_date IS NULL), e.event_date DESC, e.id DESC')->fetch_all(MYSQLI_ASSOC);
    }
}

require __DIR__ . '/includes/header.php';
?>

<?php if ($event): ?>
<div class="page-hero">
  <h1><?= h($event['title']) ?></h1>
  <p><?= $event['event_date'] ? h(format_ordinal_date($event['event_date'])) . ' &middot; ' : '' ?><?= h($event['cat_title']) ?> &middot; <?= count($photos) ?> photo<?= count($photos) === 1 ? '' : 's' ?></p>
</div>

<section>
  <div class="container">
    <div class="event-back reveal"><a class="btn btn-outline-dark" href="<?= $base ?>gallery">&larr; All events</a></div>
    <div class="gallery-grid">
      <?php $i = 0; foreach ($photos as $img): $i++; ?>
      <a class="lightbox-link reveal reveal-delay-<?= min($i, 3) ?>" href="<?= $base ?>assets/img/gallery/<?= h($img['folder']) ?>/<?= h($img['filename']) ?>" data-caption="<?= h($event['title']) ?>">
        <img src="<?= $base ?>assets/img/gallery/<?= h($img['folder']) ?>/<?= h($img['filename']) ?>" alt="<?= h($event['title']) ?>" loading="lazy">
      </a>
      <?php endforeach; ?>
      <?php if (!$photos): ?><p style="grid-column:1/-1; text-align:center; color:var(--text-muted);">No photos in this event yet.</p><?php endif; ?>
    </div>
  </div>
</section>

<?php else: ?>
<div class="page-hero">
  <h1>Gallery</h1>
  <p>Relive our festivals, services and programmes</p>
</div>

<section>
  <div class="container">
    <div class="category-tabs reveal">
      <a href="<?= $base ?>gallery" class="<?= $activeCat === '' ? 'active' : '' ?>">All</a>
      <?php foreach ($cats as $c): ?>
      <a href="<?= $base ?>gallery?cat=<?= urlencode($c['slug']) ?>" class="<?= $activeCat === $c['slug'] ? 'active' : '' ?>"><?= h($c['title']) ?></a>
      <?php endforeach; ?>
    </div>
    <div class="event-grid">
      <?php $i = 0; foreach ($events as $ev): $i++; ?>
      <a class="event-card reveal reveal-delay-<?= min($i, 3) ?>" href="<?= $base ?>gallery?event=<?= (int)$ev['id'] ?>">
        <div class="event-thumb">
          <?php if ($ev['thumb']): ?>
          <img src="<?= $base ?>assets/img/gallery/<?= h($ev['folder']) ?>/<?= h($ev['thumb']) ?>" alt="<?= h($ev['title']) ?>" loading="lazy">
          <?php endif; ?>
          <span class="event-count">&#128247; <?= (int)$ev['cnt'] ?></span>
        </div>
        <div class="event-info">
          <span class="event-cat"><?= h($ev['cat_title']) ?></span>
          <h3><?= h($ev['title']) ?></h3>
          <?php if ($ev['event_date']): ?><span class="event-date">&#128197; <?= h(format_ordinal_date($ev['event_date'])) ?></span><?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
      <?php if (!$events): ?><p style="grid-column:1/-1; text-align:center; color:var(--text-muted);">No events yet.</p><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
