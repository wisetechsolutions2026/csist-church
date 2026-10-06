<?php
$pageTitle = 'Gallery';
require __DIR__ . '/_layout_top.php';

$flash = '';
$galleryRoot = __DIR__ . '/../assets/img/gallery/';
$base = '../';

function gallery_folder(int $categoryId): ?string {
    $stmt = db()->prepare('SELECT folder FROM gallery_categories WHERE id = ?');
    $stmt->bind_param('i', $categoryId);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    return $row ? $row['folder'] : null;
}

function store_gallery_image(string $tmp, string $dir, string $prefix): ?string {
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = @mime_content_type($tmp);
    if (!isset($allowed[$mime])) {
        return null;
    }
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    $name = $prefix . '-' . bin2hex(random_bytes(4)) . '.' . $allowed[$mime];
    $dest = $dir . $name;

    if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
        $info = @getimagesize($tmp);
        $orient = 1;
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($tmp);
            $orient = (int)($exif['Orientation'] ?? 1);
        }
        $maxSide = 1800;
        $needsResize = $info && max($info[0], $info[1]) > $maxSide;
        if ($needsResize || in_array($orient, [3, 6, 8], true)) {
            $img = @imagecreatefromjpeg($tmp);
            if ($img) {
                if ($orient === 3) { $img = imagerotate($img, 180, 0); }
                elseif ($orient === 6) { $img = imagerotate($img, -90, 0); }
                elseif ($orient === 8) { $img = imagerotate($img, 90, 0); }
                if ($needsResize) {
                    $w = imagesx($img); $h = imagesy($img);
                    $scale = $maxSide / max($w, $h);
                    $img = imagescale($img, (int)round($w * $scale), (int)round($h * $scale));
                }
                if ($img && imagejpeg($img, $dest, 85)) {
                    imagedestroy($img);
                    return $name;
                }
            }
        }
    }
    return move_uploaded_file($tmp, $dest) ? $name : null;
}

function save_event_photos(int $eventId, array $files): int {
    $stmt = db()->prepare('SELECT e.title, e.category_id FROM gallery_events e WHERE e.id = ?');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $ev = $stmt->get_result()->fetch_assoc();
    if (!$ev || empty($files['name'])) {
        return 0;
    }
    $folder = gallery_folder((int)$ev['category_id']);
    if ($folder === null) {
        return 0;
    }
    global $galleryRoot;
    $dir = $galleryRoot . $folder . '/';
    $count = 0;
    $n = count($files['name']);
    for ($i = 0; $i < $n; $i++) {
        if ($files['error'][$i] !== UPLOAD_ERR_OK) {
            continue;
        }
        $name = store_gallery_image($files['tmp_name'][$i], $dir, 'ev' . $eventId);
        if ($name === null) {
            continue;
        }
        $ins = db()->prepare('INSERT INTO gallery_images (category_id, event_id, filename, alt) VALUES (?, ?, ?, ?)');
        $cid = (int)$ev['category_id'];
        $ins->bind_param('iiss', $cid, $eventId, $name, $ev['title']);
        $ins->execute();
        $count++;
    }
    return $count;
}

function delete_photo_file(int $categoryId, string $filename): void {
    global $galleryRoot;
    $folder = gallery_folder($categoryId);
    if ($folder && $filename !== '' && strpos($filename, '/') === false && strpos($filename, '..') === false) {
        @unlink($galleryRoot . $folder . '/' . $filename);
    }
}

$eventId = (int)($_GET['event'] ?? 0);

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create_event') {
        $title = trim($_POST['title'] ?? '');
        $date = trim($_POST['event_date'] ?? '');
        $catId = (int)($_POST['category_id'] ?? 0);
        if ($title === '' || gallery_folder($catId) === null) {
            $flash = 'Please enter a title and choose a category.';
        } else {
            $dateVal = $date === '' ? null : $date;
            $stmt = db()->prepare('INSERT INTO gallery_events (category_id, title, event_date) VALUES (?, ?, ?)');
            $stmt->bind_param('iss', $catId, $title, $dateVal);
            $stmt->execute();
            $eventId = (int)db()->insert_id;
            $n = save_event_photos($eventId, $_FILES['photos'] ?? []);
            $flash = 'Event created with ' . $n . ' photo' . ($n === 1 ? '' : 's') . '.';
        }
    } elseif ($action === 'update_event') {
        $eventId = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $date = trim($_POST['event_date'] ?? '');
        $dateVal = $date === '' ? null : $date;
        if ($eventId > 0 && $title !== '') {
            $stmt = db()->prepare('UPDATE gallery_events SET title = ?, event_date = ? WHERE id = ?');
            $stmt->bind_param('ssi', $title, $dateVal, $eventId);
            $stmt->execute();
            $alt = db()->prepare('UPDATE gallery_images SET alt = ? WHERE event_id = ?');
            $alt->bind_param('si', $title, $eventId);
            $alt->execute();
            $n = save_event_photos($eventId, $_FILES['photos'] ?? []);
            $flash = 'Saved' . ($n ? ' — ' . $n . ' photo' . ($n === 1 ? '' : 's') . ' added.' : '.');
        }
    } elseif ($action === 'delete_photo') {
        $pid = (int)($_POST['photo_id'] ?? 0);
        $stmt = db()->prepare('SELECT * FROM gallery_images WHERE id = ?');
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        if ($p) {
            $eventId = (int)$p['event_id'];
            delete_photo_file((int)$p['category_id'], $p['filename']);
            $del = db()->prepare('DELETE FROM gallery_images WHERE id = ?');
            $del->bind_param('i', $pid);
            $del->execute();
            $cv = db()->prepare('UPDATE gallery_events SET cover = NULL WHERE id = ? AND cover = ?');
            $cv->bind_param('is', $eventId, $p['filename']);
            $cv->execute();
            $flash = 'Photo deleted.';
        }
    } elseif ($action === 'set_cover') {
        $pid = (int)($_POST['photo_id'] ?? 0);
        $stmt = db()->prepare('SELECT event_id, filename FROM gallery_images WHERE id = ?');
        $stmt->bind_param('i', $pid);
        $stmt->execute();
        $p = $stmt->get_result()->fetch_assoc();
        if ($p) {
            $eventId = (int)$p['event_id'];
            $cv = db()->prepare('UPDATE gallery_events SET cover = ? WHERE id = ?');
            $cv->bind_param('si', $p['filename'], $eventId);
            $cv->execute();
            $flash = 'Cover photo updated.';
        }
    } elseif ($action === 'delete_event') {
        $did = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare('SELECT category_id, filename FROM gallery_images WHERE event_id = ?');
        $stmt->bind_param('i', $did);
        $stmt->execute();
        $res = $stmt->get_result();
        while ($p = $res->fetch_assoc()) {
            delete_photo_file((int)$p['category_id'], $p['filename']);
        }
        $d1 = db()->prepare('DELETE FROM gallery_images WHERE event_id = ?');
        $d1->bind_param('i', $did);
        $d1->execute();
        $d2 = db()->prepare('DELETE FROM gallery_events WHERE id = ?');
        $d2->bind_param('i', $did);
        $d2->execute();
        $eventId = 0;
        $flash = 'Event deleted.';
    }
}

$categories = db()->query('SELECT * FROM gallery_categories ORDER BY sort_order')->fetch_all(MYSQLI_ASSOC);
$event = null;
$photos = [];
if ($eventId > 0) {
    $stmt = db()->prepare('SELECT e.*, c.title AS cat_title, c.folder FROM gallery_events e JOIN gallery_categories c ON e.category_id = c.id WHERE e.id = ?');
    $stmt->bind_param('i', $eventId);
    $stmt->execute();
    $event = $stmt->get_result()->fetch_assoc();
    if ($event) {
        $stmt = db()->prepare('SELECT * FROM gallery_images WHERE event_id = ? ORDER BY id');
        $stmt->bind_param('i', $eventId);
        $stmt->execute();
        $photos = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    }
}
$uploadHint = 'Select many photos at once (Ctrl/Shift-click, or Ctrl+A). Large photos are shrunk automatically. Server limit: ' . ini_get('upload_max_filesize') . ' per photo, ' . ini_get('post_max_size') . ' per upload — upload very big events in a few batches.';
?>
<style>
  .photo-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(130px, 1fr)); gap:14px; margin-top:16px; }
  .photo-tile { position:relative; border-radius:12px; overflow:hidden; border:1px solid var(--border); background:#fff; }
  .photo-tile img { width:100%; aspect-ratio:1/1; object-fit:cover; display:block; }
  .photo-tile .tile-actions { display:flex; gap:4px; padding:6px; }
  .photo-tile .tile-actions form { flex:1; margin:0; }
  .photo-tile button { width:100%; border:none; border-radius:8px; padding:6px 4px; font-size:0.72rem; font-weight:600; cursor:pointer; background:var(--paper); color:var(--ink-3); }
  .photo-tile button.danger { background:#f3dfe0; color:var(--ruby); }
  .cover-badge { position:absolute; top:8px; left:8px; background:var(--grad); color:#fff; font-size:0.65rem; font-weight:700; padding:3px 9px; border-radius:20px; letter-spacing:.05em; }
  .thumb-sm { width:64px; height:48px; border-radius:8px; object-fit:cover; display:block; background:var(--ink-3); }
  .hint { font-size:0.8rem; color:var(--muted); margin-top:6px; }
  .event-head { display:flex; justify-content:space-between; align-items:center; gap:12px; flex-wrap:wrap; }
</style>

<?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>

<?php if ($event): ?>
<div class="admin-card">
  <div class="event-head">
    <h2>Edit event</h2>
    <a class="btn btn-secondary" href="gallery">&larr; All events</a>
  </div>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="update_event">
    <input type="hidden" name="id" value="<?= (int)$event['id'] ?>">
    <label>Event title</label>
    <input type="text" name="title" value="<?= h($event['title']) ?>" required>
    <label>Event date</label>
    <input type="date" name="event_date" value="<?= h($event['event_date'] ?? '') ?>">
    <label>Category</label>
    <input type="text" value="<?= h($event['cat_title']) ?>" disabled>
    <label>Add more photos</label>
    <input type="file" name="photos[]" accept="image/png, image/jpeg, image/webp" multiple>
    <p class="hint"><?= h($uploadHint) ?></p>
    <button type="submit" class="btn" style="margin-top:20px;">Save</button>
  </form>
</div>

<div class="admin-card">
  <h2>Photos (<?= count($photos) ?>)</h2>
  <p class="hint">The cover photo is the thumbnail shown on the Gallery page. Without one, the first photo is used.</p>
  <div class="photo-grid">
    <?php foreach ($photos as $idx => $p):
      $isCover = ($event['cover'] === $p['filename']) || (!$event['cover'] && $idx === 0); ?>
    <div class="photo-tile">
      <img src="<?= $base ?>assets/img/gallery/<?= h($event['folder']) ?>/<?= h($p['filename']) ?>" alt="" loading="lazy">
      <?php if ($isCover): ?><span class="cover-badge">COVER</span><?php endif; ?>
      <div class="tile-actions">
        <form method="post"><input type="hidden" name="action" value="set_cover"><input type="hidden" name="photo_id" value="<?= (int)$p['id'] ?>"><button type="submit">Cover</button></form>
        <form method="post" onsubmit="return confirm('Delete this photo?');"><input type="hidden" name="action" value="delete_photo"><input type="hidden" name="photo_id" value="<?= (int)$p['id'] ?>"><button type="submit" class="danger">Delete</button></form>
      </div>
    </div>
    <?php endforeach; ?>
    <?php if (!$photos): ?><p class="hint">No photos yet — add some above.</p><?php endif; ?>
  </div>
</div>

<div class="admin-card">
  <h2>Delete event</h2>
  <p class="hint">Removes this event and all its photos from the website.</p>
  <form method="post" onsubmit="return confirm('Delete this whole event and all its photos?');">
    <input type="hidden" name="action" value="delete_event">
    <input type="hidden" name="id" value="<?= (int)$event['id'] ?>">
    <button type="submit" class="btn btn-danger">Delete this event</button>
  </form>
</div>

<?php else: ?>
<div class="admin-card">
  <h2>Add new event</h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="create_event">
    <label>Event title</label>
    <input type="text" name="title" placeholder="e.g. Christmas Carnival" required>
    <label>Event date</label>
    <input type="date" name="event_date" required>
    <label>Category</label>
    <select name="category_id">
      <?php foreach ($categories as $c): ?>
      <option value="<?= (int)$c['id'] ?>" <?= $c['slug'] === 'church-event' ? 'selected' : '' ?>><?= h($c['title']) ?></option>
      <?php endforeach; ?>
    </select>
    <label>Photos</label>
    <input type="file" name="photos[]" accept="image/png, image/jpeg, image/webp" multiple>
    <p class="hint"><?= h($uploadHint) ?></p>
    <button type="submit" class="btn" style="margin-top:20px;">Create event</button>
  </form>
</div>

<?php
$list = db()->query("SELECT e.id, e.title, e.event_date, e.cover, c.title AS cat_title, c.folder,
        (SELECT COUNT(*) FROM gallery_images gi WHERE gi.event_id = e.id) AS cnt,
        COALESCE(e.cover, (SELECT gi.filename FROM gallery_images gi WHERE gi.event_id = e.id ORDER BY gi.id LIMIT 1)) AS thumb
        FROM gallery_events e JOIN gallery_categories c ON e.category_id = c.id
        ORDER BY (e.event_date IS NULL), e.event_date DESC, e.id DESC");
?>
<div class="admin-card">
  <h2>All events</h2>
  <div class="table-scroll">
  <table>
    <tr><th>Cover</th><th>Event</th><th>Date</th><th>Category</th><th>Photos</th><th></th></tr>
    <?php while ($row = $list->fetch_assoc()): ?>
    <tr>
      <td>
        <?php if ($row['thumb']): ?>
        <img class="thumb-sm" src="<?= $base ?>assets/img/gallery/<?= h($row['folder']) ?>/<?= h($row['thumb']) ?>" alt="" loading="lazy">
        <?php else: ?><span class="thumb-sm"></span><?php endif; ?>
      </td>
      <td><?= h($row['title']) ?></td>
      <td><?= $row['event_date'] ? h(format_ordinal_date($row['event_date'])) : '—' ?></td>
      <td><?= h($row['cat_title']) ?></td>
      <td><?= (int)$row['cnt'] ?></td>
      <td class="row-actions"><a href="gallery?event=<?= (int)$row['id'] ?>">Manage</a></td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
</div>
<?php endif; ?>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
