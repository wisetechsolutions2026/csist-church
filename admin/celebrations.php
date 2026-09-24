<?php
$pageTitle = 'Birthday / Anniversary Cards';
require __DIR__ . '/_layout_top.php';

$flash = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $type = $_POST['type'] === 'anniversary' ? 'anniversary' : 'birthday';
        $name = trim($_POST['name'] ?? '');
        $occasion_date = trim($_POST['occasion_date'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;

        if ($id > 0) {
            $stmt = db()->prepare("UPDATE celebrations SET type=?, name=?, occasion_date=?, is_active=? WHERE id=?");
            $stmt->bind_param('sssii', $type, $name, $occasion_date, $is_active, $id);
        } else {
            $stmt = db()->prepare("INSERT INTO celebrations (type, name, occasion_date, is_active, sort_order) VALUES (?, ?, ?, ?, 0)");
            $stmt->bind_param('sssi', $type, $name, $occasion_date, $is_active);
        }
        $stmt->execute();
        $flash = 'Saved.';
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $stmt = db()->prepare("DELETE FROM celebrations WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $flash = 'Deleted.';
    }
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM celebrations WHERE id=?");
    $id = (int)$_GET['edit'];
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
}

$all = db()->query("SELECT * FROM celebrations ORDER BY type, sort_order, id DESC");
?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit Card' : 'Add New Card' ?></h2>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow['id'] : 0 ?>">
    <label>Type</label>
    <select name="type">
      <option value="birthday" <?= (!$editRow || $editRow['type'] === 'birthday') ? 'selected' : '' ?>>Birthday</option>
      <option value="anniversary" <?= ($editRow && $editRow['type'] === 'anniversary') ? 'selected' : '' ?>>Wedding Anniversary</option>
    </select>
    <label>Name</label>
    <input type="text" name="name" value="<?= h($editRow['name'] ?? '') ?>" required>
    <label>Date (e.g. 27 September)</label>
    <input type="text" name="occasion_date" value="<?= h($editRow['occasion_date'] ?? '') ?>" required>
    <label><input type="checkbox" name="is_active" <?= (!$editRow || $editRow['is_active']) ? 'checked' : '' ?>> Show on home page</label>
    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update' : 'Add' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="celebrations.php">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <h2>All Cards</h2>
  <table>
    <tr><th>Type</th><th>Name</th><th>Date</th><th>Status</th><th></th></tr>
    <?php while ($row = $all->fetch_assoc()): ?>
    <tr>
      <td><?= $row['type'] === 'anniversary' ? 'Anniversary' : 'Birthday' ?></td>
      <td><?= h($row['name']) ?></td>
      <td><?= h($row['occasion_date']) ?></td>
      <td><span class="badge <?= $row['is_active'] ? 'badge-on' : 'badge-off' ?>"><?= $row['is_active'] ? 'Visible' : 'Hidden' ?></span></td>
      <td class="row-actions">
        <a href="?edit=<?= (int)$row['id'] ?>">Edit</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this card?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <a href="#" onclick="this.closest('form').submit(); return false;" style="color:#a52a47;">Delete</a>
        </form>
      </td>
    </tr>
    <?php endwhile; ?>
  </table>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
