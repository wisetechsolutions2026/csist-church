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
    $years = [];
    $yr = db()->query("SELECT DISTINCT YEAR(e.event_date) y FROM gallery_events e WHERE e.event_date IS NOT NULL AND EXISTS (SELECT 1 FROM gallery_images gi WHERE gi.event_id = e.id) ORDER BY y DESC");
    while ($r = $yr->fetch_assoc()) { $years[] = (int)$r['y']; }
    $fYear = (int)($_GET['year'] ?? 0);
    $fMonth = (int)($_GET['month'] ?? 0);
    if (!in_array($fYear, $years, true)) { $fYear = 0; }
    if ($fMonth < 1 || $fMonth > 12) { $fMonth = 0; }
    $monthNames = [1 => 'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];

    $sql = "SELECT e.id, e.title, e.event_date, c.folder,
                   (SELECT COUNT(*) FROM gallery_images gi WHERE gi.event_id = e.id) AS cnt,
                   COALESCE(e.cover, (SELECT gi.filename FROM gallery_images gi WHERE gi.event_id = e.id ORDER BY gi.id LIMIT 1)) AS thumb
            FROM gallery_events e JOIN gallery_categories c ON e.category_id = c.id WHERE 1=1";
    $types = '';
    $args = [];
    if ($fYear) { $sql .= ' AND YEAR(e.event_date) = ?'; $types .= 'i'; $args[] = $fYear; }
    if ($fMonth) { $sql .= ' AND MONTH(e.event_date) = ?'; $types .= 'i'; $args[] = $fMonth; }
    $stmt = db()->prepare($sql . ' HAVING cnt > 0 ORDER BY (e.event_date IS NULL), e.event_date DESC, e.id DESC');
    if ($args) { $stmt->bind_param($types, ...$args); }
    $stmt->execute();
    $events = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

require __DIR__ . '/includes/header.php';
?>

<?php if ($event): ?>
<div class="page-hero">
  <h1>Gallery</h1>
  <p>Relive our festivals, services and programmes</p>
</div>

<section>
  <div class="container">
    <div class="event-back reveal"><a class="btn btn-outline-dark" href="<?= $base ?>gallery">&larr; All events</a></div>
    <div class="event-header reveal">
      <h2><?= h($event['title']) ?></h2>
      <div class="event-meta">
        <?php if ($event['event_date']): ?><span class="event-meta-chip">&#128197; <?= h(format_ordinal_date($event['event_date'])) ?></span><?php endif; ?>
        <span class="event-meta-chip">&#128247; <?= count($photos) ?> photo<?= count($photos) === 1 ? '' : 's' ?></span>
      </div>
    </div>
    <div class="gallery-grid">
      <?php $i = 0; foreach ($photos as $img): $i++; ?>
      <a class="lightbox-link reveal reveal-delay-<?= min($i, 3) ?>" href="<?= $base ?>assets/img/gallery/<?= h($img['folder']) ?>/<?= h($img['filename']) ?>" data-caption="<?= h($event['title']) ?><?= $event['event_date'] ? ' — ' . h(format_ordinal_date($event['event_date'])) : '' ?>">
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
    <form class="gallery-filter reveal" method="get" action="<?= $base ?>gallery">
      <div class="gf-field">
        <label for="gfYear">Year</label>
        <select id="gfYear" name="year" onchange="this.form.submit()">
          <option value="">All years</option>
          <?php foreach ($years as $y): ?><option value="<?= $y ?>" <?= $fYear === $y ? 'selected' : '' ?>><?= $y ?></option><?php endforeach; ?>
        </select>
      </div>
      <div class="gf-field">
        <label for="gfMonth">Month</label>
        <select id="gfMonth" name="month" onchange="this.form.submit()">
          <option value="">All months</option>
          <?php foreach ($monthNames as $mn => $mname): ?><option value="<?= $mn ?>" <?= $fMonth === $mn ? 'selected' : '' ?>><?= $mname ?></option><?php endforeach; ?>
        </select>
      </div>
      <?php if ($fYear || $fMonth): ?><a class="gf-clear" href="<?= $base ?>gallery">&times; Clear</a><?php endif; ?>
      <noscript><button type="submit" class="btn">Filter</button></noscript>
      <span class="gf-count"><?= count($events) ?> event<?= count($events) === 1 ? '' : 's' ?></span>
    </form>
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
          <h3><?= h($ev['title']) ?></h3>
          <?php if ($ev['event_date']): ?><span class="event-date">&#128197; <?= h(format_ordinal_date($ev['event_date'])) ?></span><?php endif; ?>
        </div>
      </a>
      <?php endforeach; ?>
      <?php if (!$events): ?><p style="grid-column:1/-1; text-align:center; color:var(--text-muted);">No events found &mdash; try another year or month.</p><?php endif; ?>
    </div>
  </div>
</section>
<?php endif; ?>

<?php require __DIR__ . '/includes/footer.php'; ?>
