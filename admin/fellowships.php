<?php
$pageTitle = 'Fellowships';
require __DIR__ . '/_layout_top.php';

$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $slug = trim($_POST['slug'] ?? '');
        $title = trim($_POST['title'] ?? '');
        $tagline = trim($_POST['tagline'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $gallery_slug = trim($_POST['gallery_slug'] ?? '');

        if ($id > 0) {
            $stmt = db()->prepare("UPDATE fellowships SET slug=?, title=?, tagline=?, description=?, gallery_slug=? WHERE id=?");
            $stmt->bind_param('sssssi', $slug, $title, $tagline, $description, $gallery_slug, $id);
        } else {
            $nextOrder = (int)(db()->query("SELECT COALESCE(MAX(sort_order),0)+1 n FROM fellowships")->fetch_assoc()['n']);
            $stmt = db()->prepare("INSERT INTO fellowships (slug, title, tagline, description, gallery_slug, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('sssssi', $slug, $title, $tagline, $description, $gallery_slug, $nextOrder);
        }
        $stmt->execute();
        $flash = 'Saved.';
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare("DELETE FROM fellowships WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $flash = 'Deleted.';
    }
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM fellowships WHERE id=?");
    $id = (int)$_GET['edit'];
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
}

$galleryCats = db()->query("SELECT slug, title FROM gallery_categories ORDER BY sort_order");
$catOptions = [];
while ($c = $galleryCats->fetch_assoc()) { $catOptions[] = $c; }

$all = db()->query("SELECT * FROM fellowships ORDER BY sort_order");
?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit Fellowship' : 'Add New Fellowship' ?></h2>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow['id'] : 0 ?>">
    <label>Slug (used in the URL, e.g. youth)</label>
    <input type="text" name="slug" value="<?= h($editRow['slug'] ?? '') ?>" pattern="[a-z0-9\-]+" required>
    <label>Title</label>
    <input type="text" name="title" value="<?= h($editRow['title'] ?? '') ?>" required>
    <label>Tagline</label>
    <input type="text" name="tagline" value="<?= h($editRow['tagline'] ?? '') ?>">
    <label>Description</label>
    <textarea name="description" rows="4"><?= h($editRow['description'] ?? '') ?></textarea>
    <label>Gallery Category (links to a photo album)</label>
    <select name="gallery_slug">
      <option value="">— None —</option>
      <?php foreach ($catOptions as $c): ?>
        <option value="<?= h($c['slug']) ?>" <?= (($editRow['gallery_slug'] ?? '') === $c['slug']) ? 'selected' : '' ?>><?= h($c['title']) ?></option>
      <?php endforeach; ?>
    </select>
    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update' : 'Add' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="fellowships.php">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <h2>All Fellowships</h2>
  <div class="table-scroll">
  <table>
    <tr><th>Title</th><th>Slug</th><th>Gallery</th><th></th></tr>
    <?php while ($row = $all->fetch_assoc()): ?>
    <tr>
      <td><?= h($row['title']) ?></td>
      <td><?= h($row['slug']) ?></td>
      <td><?= h($row['gallery_slug'] ?: '—') ?></td>
      <td class="row-actions">
        <a href="?edit=<?= (int)$row['id'] ?>">Edit</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this fellowship?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <a href="#" onclick="this.closest('form').submit(); return false;" style="color:#a52a47;">Delete</a>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
