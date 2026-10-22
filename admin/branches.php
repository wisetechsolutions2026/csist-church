<?php
$pageTitle = 'Branch Churches';
require __DIR__ . '/_layout_top.php';

$flash = '';
$error = '';
$uploadDir = __DIR__ . '/../assets/img/branches/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function store_branch_photo(?array $file, string $dir): ?string {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = @mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return null;
    }
    $name = 'branch-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
    $dest = $dir . $name;
    if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
        $info = @getimagesize($file['tmp_name']);
        $orient = 1;
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($file['tmp_name']);
            $orient = (int)($exif['Orientation'] ?? 1);
        }
        $max = 1600;
        $resize = $info && max($info[0], $info[1]) > $max;
        if ($resize || in_array($orient, [3, 6, 8], true)) {
            $img = @imagecreatefromjpeg($file['tmp_name']);
            if ($img) {
                if ($orient === 3) { $img = imagerotate($img, 180, 0); }
                elseif ($orient === 6) { $img = imagerotate($img, -90, 0); }
                elseif ($orient === 8) { $img = imagerotate($img, 90, 0); }
                if ($resize) {
                    $w = imagesx($img); $h = imagesy($img); $sc = $max / max($w, $h);
                    $img = imagescale($img, (int)round($w * $sc), (int)round($h * $sc));
                }
                if ($img && imagejpeg($img, $dest, 85)) {
                    imagedestroy($img);
                    return $name;
                }
            }
        }
    }
    return move_uploaded_file($file['tmp_name'], $dest) ? $name : null;
}

function delete_branch_photo(?string $name, string $dir): void {
    if ($name && strpos($name, '/') === false && strpos($name, '..') === false) {
        @unlink($dir . $name);
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $location = trim($_POST['location'] ?? '');
        $catechist = trim($_POST['catechist'] ?? '');
        $newPhoto = store_branch_photo($_FILES['photo'] ?? null, $uploadDir);
        $removePhoto = isset($_POST['remove_photo']);
        if ($name === '') {
            $error = 'Please enter the church name.';
        } elseif ($id > 0) {
            $old = db()->query('SELECT photo FROM branch_churches WHERE id=' . $id)->fetch_assoc();
            if ($newPhoto !== null) {
                delete_branch_photo($old['photo'] ?? null, $uploadDir);
                $stmt = db()->prepare('UPDATE branch_churches SET name=?, location=?, catechist=?, photo=? WHERE id=?');
                $stmt->bind_param('ssssi', $name, $location, $catechist, $newPhoto, $id);
            } elseif ($removePhoto) {
                delete_branch_photo($old['photo'] ?? null, $uploadDir);
                $stmt = db()->prepare('UPDATE branch_churches SET name=?, location=?, catechist=?, photo=NULL WHERE id=?');
                $stmt->bind_param('sssi', $name, $location, $catechist, $id);
            } else {
                $stmt = db()->prepare('UPDATE branch_churches SET name=?, location=?, catechist=? WHERE id=?');
                $stmt->bind_param('sssi', $name, $location, $catechist, $id);
            }
            $stmt->execute();
            $flash = 'Saved.';
        } else {
            $next = (int)db()->query('SELECT COALESCE(MAX(sort_order),0)+1 n FROM branch_churches')->fetch_assoc()['n'];
            $stmt = db()->prepare('INSERT INTO branch_churches (name, location, catechist, photo, sort_order) VALUES (?,?,?,?,?)');
            $stmt->bind_param('ssssi', $name, $location, $catechist, $newPhoto, $next);
            $stmt->execute();
            $flash = 'Branch church added.';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $old = db()->query('SELECT photo FROM branch_churches WHERE id=' . $id)->fetch_assoc();
        delete_branch_photo($old['photo'] ?? null, $uploadDir);
        $stmt = db()->prepare('DELETE FROM branch_churches WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $flash = 'Deleted.';
    }
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM branch_churches WHERE id=?');
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
}
$all = db()->query('SELECT * FROM branch_churches ORDER BY sort_order, id')->fetch_all(MYSQLI_ASSOC);
$base = '../';
?>

<?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="flash" style="background:#f3dfe0; border-color:#e8b9c0; color:#a52a47;"><?= h($error) ?></div><?php endif; ?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit branch church' : 'Add branch church' ?></h2>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow['id'] : 0 ?>">
    <label>Church name</label>
    <input type="text" name="name" value="<?= h($editRow['name'] ?? '') ?>" required>
    <label>Place</label>
    <input type="text" name="location" value="<?= h($editRow['location'] ?? '') ?>" placeholder="e.g. Iyappanthangal">
    <label>Catechist</label>
    <input type="text" name="catechist" value="<?= h($editRow['catechist'] ?? '') ?>" placeholder="e.g. Mr. K. Arul Prabakar">
    <label>Photo</label>
    <?php if ($editRow && !empty($editRow['photo'])): ?>
      <div style="margin-bottom:10px;">
        <img src="<?= $base ?>assets/img/branches/<?= h($editRow['photo']) ?>" alt="" style="width:220px; max-width:100%; border-radius:12px; border:1px solid var(--border);">
        <label style="margin-top:10px;"><input type="checkbox" name="remove_photo"> Remove current photo</label>
      </div>
    <?php endif; ?>
    <input type="file" name="photo" accept="image/png, image/jpeg, image/webp">
    <p style="font-size:0.8rem; color:#6b5a4d; margin-top:6px;">Choose a photo of the church (large photos are shrunk automatically). Choosing a new one replaces the old photo.</p>
    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update' : 'Add' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="branches">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <h2>All branch churches</h2>
  <div class="table-scroll">
  <table>
    <tr><th>Photo</th><th>Church</th><th>Place</th><th>Catechist</th><th></th></tr>
    <?php foreach ($all as $row): ?>
    <tr>
      <td>
        <?php if (!empty($row['photo'])): ?>
          <img src="<?= $base ?>assets/img/branches/<?= h($row['photo']) ?>" alt="" style="width:64px; height:48px; object-fit:cover; border-radius:8px; display:block;">
        <?php else: ?><span style="color:#999; font-size:0.8rem;">No photo</span><?php endif; ?>
      </td>
      <td><?= h($row['name']) ?></td>
      <td><?= h($row['location']) ?></td>
      <td><?= h($row['catechist']) ?></td>
      <td class="row-actions">
        <a href="branches?edit=<?= (int)$row['id'] ?>"><?= empty($row['photo']) ? 'Add photo' : 'Edit' ?></a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this branch church?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <a href="#" onclick="this.closest('form').submit(); return false;" style="color:#a52a47;">Delete</a>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
