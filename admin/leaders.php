<?php
$pageTitle = 'Leadership';
require __DIR__ . '/_layout_top.php';

$flash = '';
$uploadDir = __DIR__ . '/../assets/img/leaders/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function handle_leader_photo_upload(?array $file): ?string {
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
    $filename = 'leader-' . bin2hex(random_bytes(6)) . '.' . $allowed[$mime];
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $role = trim($_POST['role'] ?? '');
        $name = trim($_POST['name'] ?? '');
        $contact = trim($_POST['contact'] ?? '');
        $newPhoto = handle_leader_photo_upload($_FILES['photo'] ?? null);
        $removePhoto = isset($_POST['remove_photo']);

        if ($id > 0) {
            if ($newPhoto !== null) {
                $stmt = db()->prepare("UPDATE leaders SET role=?, name=?, contact=?, photo=? WHERE id=?");
                $stmt->bind_param('ssssi', $role, $name, $contact, $newPhoto, $id);
            } elseif ($removePhoto) {
                $stmt = db()->prepare("UPDATE leaders SET role=?, name=?, contact=?, photo=NULL WHERE id=?");
                $stmt->bind_param('sssi', $role, $name, $contact, $id);
            } else {
                $stmt = db()->prepare("UPDATE leaders SET role=?, name=?, contact=? WHERE id=?");
                $stmt->bind_param('sssi', $role, $name, $contact, $id);
            }
        } else {
            $nextOrder = (int)(db()->query("SELECT COALESCE(MAX(sort_order),0)+1 n FROM leaders")->fetch_assoc()['n']);
            $stmt = db()->prepare("INSERT INTO leaders (role, name, contact, photo, sort_order) VALUES (?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssi', $role, $name, $contact, $newPhoto, $nextOrder);
        }
        $stmt->execute();
        $flash = 'Saved.';
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $row = db()->query("SELECT photo FROM leaders WHERE id=" . $id)->fetch_assoc();
        if ($row && $row['photo'] && file_exists($uploadDir . $row['photo'])) {
            @unlink($uploadDir . $row['photo']);
        }
        $stmt = db()->prepare("DELETE FROM leaders WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $flash = 'Deleted.';
    }
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM leaders WHERE id=?");
    $id = (int)$_GET['edit'];
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
}

$all = db()->query("SELECT * FROM leaders ORDER BY sort_order");
$base = base_url();
?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit Leader' : 'Add New Leader' ?></h2>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow['id'] : 0 ?>">
    <label>Role (e.g. Presbyter & Chairman)</label>
    <input type="text" name="role" value="<?= h($editRow['role'] ?? '') ?>" required>
    <label>Name</label>
    <input type="text" name="name" value="<?= h($editRow['name'] ?? '') ?>" required>
    <label>Phone</label>
    <input type="text" name="contact" value="<?= h($editRow['contact'] ?? '') ?>" required>

    <label>Photo (optional)</label>
    <?php if ($editRow && !empty($editRow['photo'])): ?>
      <div style="margin-bottom:8px;">
        <img src="<?= $base ?>assets/img/leaders/<?= h($editRow['photo']) ?>" alt="" style="width:70px; height:70px; object-fit:cover; border-radius:10px; border:1px solid #d8cdb0;">
        <label style="display:inline-block; font-weight:400; margin-left:10px;"><input type="checkbox" name="remove_photo"> Remove current photo</label>
      </div>
    <?php endif; ?>
    <input type="file" name="photo" accept="image/png, image/jpeg, image/webp">

    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update' : 'Add' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="leaders.php">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <h2>All Leaders</h2>
  <div class="table-scroll">
  <table>
    <tr><th>Photo</th><th>Role</th><th>Name</th><th>Phone</th><th></th></tr>
    <?php while ($row = $all->fetch_assoc()): ?>
    <tr>
      <td>
        <?php if (!empty($row['photo'])): ?>
          <img src="<?= $base ?>assets/img/leaders/<?= h($row['photo']) ?>" alt="" style="width:40px; height:40px; object-fit:cover; border-radius:8px;">
        <?php else: ?>
          <span style="color:#999; font-size:0.8rem;">None</span>
        <?php endif; ?>
      </td>
      <td><?= h($row['role']) ?></td>
      <td><?= h($row['name']) ?></td>
      <td><?= h($row['contact']) ?></td>
      <td class="row-actions">
        <a href="?edit=<?= (int)$row['id'] ?>">Edit</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this leader?');">
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
