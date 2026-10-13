<?php
$pageTitle = 'Fellowships';
$requireSuper = true;
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

        if ($id > 0) {
            $stmt = db()->prepare("UPDATE fellowships SET slug=?, title=?, tagline=?, description=? WHERE id=?");
            $stmt->bind_param('ssssi', $slug, $title, $tagline, $description, $id);
        } else {
            $nextOrder = (int)(db()->query("SELECT COALESCE(MAX(sort_order),0)+1 n FROM fellowships")->fetch_assoc()['n']);
            $stmt = db()->prepare("INSERT INTO fellowships (slug, title, tagline, description, gallery_slug, sort_order) VALUES (?, ?, ?, ?, '', ?)");
            $stmt->bind_param('ssssi', $slug, $title, $tagline, $description, $nextOrder);
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
    <label>Page content</label>
    <textarea name="description" rows="16"><?= h($editRow['description'] ?? '') ?></textarea>
    <p style="font-size:0.8rem; color:#6b5a4d; margin-top:6px; line-height:1.6;">Leave a blank line between paragraphs. Start a line with <code>## </code> for a heading, <code>- </code> for each name in a list, or <code>&gt; Label : Value</code> for rows like "Organist : Mr. X". A line that is just a web link becomes a button.</p>
    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update' : 'Add' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="fellowships">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <h2>All Fellowships</h2>
  <div class="table-scroll">
  <table>
    <tr><th>Title</th><th>Slug</th><th></th></tr>
    <?php while ($row = $all->fetch_assoc()): ?>
    <tr>
      <td><?= h($row['title']) ?></td>
      <td><?= h($row['slug']) ?></td>
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
