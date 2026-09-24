<?php
$pageTitle = 'Birthday / Anniversary Cards';
require __DIR__ . '/_layout_top.php';

$flash = '';
$uploadDir = __DIR__ . '/../assets/img/celebrations/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function handle_photo_upload(?array $file): ?string {
    global $uploadDir;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return null;
    }
    $filename = 'celeb-' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $type = $_POST['type'] === 'anniversary' ? 'anniversary' : 'birthday';
        $name = trim($_POST['name'] ?? '');
        $occasion_date = trim($_POST['occasion_date'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $newPhoto = handle_photo_upload($_FILES['photo'] ?? null);
        $removePhoto = isset($_POST['remove_photo']);

        if ($id > 0) {
            if ($newPhoto !== null) {
                $stmt = db()->prepare("UPDATE celebrations SET type=?, name=?, occasion_date=?, is_active=?, photo=? WHERE id=?");
                $stmt->bind_param('sssisi', $type, $name, $occasion_date, $is_active, $newPhoto, $id);
            } elseif ($removePhoto) {
                $stmt = db()->prepare("UPDATE celebrations SET type=?, name=?, occasion_date=?, is_active=?, photo=NULL WHERE id=?");
                $stmt->bind_param('sssii', $type, $name, $occasion_date, $is_active, $id);
            } else {
                $stmt = db()->prepare("UPDATE celebrations SET type=?, name=?, occasion_date=?, is_active=? WHERE id=?");
                $stmt->bind_param('sssii', $type, $name, $occasion_date, $is_active, $id);
            }
        } else {
            $stmt = db()->prepare("INSERT INTO celebrations (type, name, occasion_date, is_active, photo, sort_order) VALUES (?, ?, ?, ?, ?, 0)");
            $stmt->bind_param('sssis', $type, $name, $occasion_date, $is_active, $newPhoto);
        }
        $stmt->execute();
        $flash = 'Saved.';
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $row = db()->query("SELECT photo FROM celebrations WHERE id=" . $id)->fetch_assoc();
        if ($row && $row['photo'] && file_exists($uploadDir . $row['photo'])) {
            @unlink($uploadDir . $row['photo']);
        }
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
$base = base_url();
?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit Card' : 'Add New Card' ?></h2>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
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

    <label>Photo (optional)</label>
    <?php if ($editRow && !empty($editRow['photo'])): ?>
      <div style="margin-bottom:8px;">
        <img src="<?= $base ?>assets/img/celebrations/<?= h($editRow['photo']) ?>" alt="" style="width:70px; height:70px; object-fit:cover; border-radius:50%; border:1px solid #d8cdb0;">
        <label style="display:inline-block; font-weight:400; margin-left:10px;"><input type="checkbox" name="remove_photo"> Remove current photo</label>
      </div>
    <?php endif; ?>
    <input type="file" name="photo" accept="image/png, image/jpeg, image/webp">

    <label style="margin-top:16px;"><input type="checkbox" name="is_active" <?= (!$editRow || $editRow['is_active']) ? 'checked' : '' ?>> Show on home page</label>
    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update' : 'Add' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="celebrations.php">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <h2>All Cards</h2>
  <table>
    <tr><th>Photo</th><th>Type</th><th>Name</th><th>Date</th><th>Status</th><th></th></tr>
    <?php while ($row = $all->fetch_assoc()): ?>
    <tr>
      <td>
        <?php if (!empty($row['photo'])): ?>
          <img src="<?= $base ?>assets/img/celebrations/<?= h($row['photo']) ?>" alt="" style="width:40px; height:40px; object-fit:cover; border-radius:50%;">
        <?php else: ?>
          <span style="color:#999; font-size:0.8rem;">None</span>
        <?php endif; ?>
      </td>
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
