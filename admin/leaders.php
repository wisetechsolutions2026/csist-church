<?php
$pageTitle = 'Leadership';
require __DIR__ . '/_layout_top.php';
require_super_admin();

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

function clamp_int($val, int $min, int $max, int $default): int {
    if (!is_numeric($val)) return $default;
    return max($min, min($max, (int)$val));
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
        $posX = clamp_int($_POST['photo_pos_x'] ?? null, 0, 100, 50);
        $posY = clamp_int($_POST['photo_pos_y'] ?? null, 0, 100, 20);
        $zoom = clamp_int($_POST['photo_zoom'] ?? null, 100, 250, 100);

        if ($id > 0) {
            if ($newPhoto !== null) {
                $stmt = db()->prepare("UPDATE leaders SET role=?, name=?, contact=?, photo=?, photo_pos_x=?, photo_pos_y=?, photo_zoom=? WHERE id=?");
                $stmt->bind_param('ssssiiii', $role, $name, $contact, $newPhoto, $posX, $posY, $zoom, $id);
            } elseif ($removePhoto) {
                $stmt = db()->prepare("UPDATE leaders SET role=?, name=?, contact=?, photo=NULL, photo_pos_x=?, photo_pos_y=?, photo_zoom=? WHERE id=?");
                $stmt->bind_param('sssiiii', $role, $name, $contact, $posX, $posY, $zoom, $id);
            } else {
                $stmt = db()->prepare("UPDATE leaders SET role=?, name=?, contact=?, photo_pos_x=?, photo_pos_y=?, photo_zoom=? WHERE id=?");
                $stmt->bind_param('sssiiii', $role, $name, $contact, $posX, $posY, $zoom, $id);
            }
        } else {
            $nextOrder = (int)(db()->query("SELECT COALESCE(MAX(sort_order),0)+1 n FROM leaders")->fetch_assoc()['n']);
            $stmt = db()->prepare("INSERT INTO leaders (role, name, contact, photo, photo_pos_x, photo_pos_y, photo_zoom, sort_order) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param('ssssiiii', $role, $name, $contact, $newPhoto, $posX, $posY, $zoom, $nextOrder);
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
$base = '../';

$curPosX = (int)($editRow['photo_pos_x'] ?? 50);
$curPosY = (int)($editRow['photo_pos_y'] ?? 20);
$curZoom = (int)($editRow['photo_zoom'] ?? 100);
?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit Leader' : 'Add New Leader' ?></h2>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data" id="leaderForm">
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
      <label style="display:inline-block; font-weight:400; margin-bottom:10px;"><input type="checkbox" name="remove_photo"> Remove current photo</label>
    <?php endif; ?>
    <input type="file" name="photo" accept="image/png, image/jpeg, image/webp" id="photoInput">

    <div id="cropSection" style="<?= (!$editRow || empty($editRow['photo'])) ? 'display:none;' : '' ?> margin-top:14px;">
      <label style="margin:0 0 6px;">Preview &amp; Position (crops the same way it appears on the site)</label>
      <div id="cropPreviewWrap" style="width:220px; height:250px; border-radius:12px; overflow:hidden; border:1px solid #d8cdb0; background:#eee2c8;">
        <img id="cropPreviewImg" src="<?= $editRow && !empty($editRow['photo']) ? $base . 'assets/img/leaders/' . h($editRow['photo']) : '' ?>"
             style="width:100%; height:100%; object-fit:cover; object-position: <?= $curPosX ?>% <?= $curPosY ?>%; transform: scale(<?= $curZoom / 100 ?>); transform-origin: <?= $curPosX ?>% <?= $curPosY ?>%;">
      </div>

      <label style="margin-top:14px;">Horizontal position</label>
      <input type="range" id="posXRange" min="0" max="100" value="<?= $curPosX ?>" style="width:100%;">
      <label>Vertical position</label>
      <input type="range" id="posYRange" min="0" max="100" value="<?= $curPosY ?>" style="width:100%;">
      <label>Zoom</label>
      <input type="range" id="zoomRange" min="100" max="250" value="<?= $curZoom ?>" style="width:100%;">

      <input type="hidden" name="photo_pos_x" id="posXInput" value="<?= $curPosX ?>">
      <input type="hidden" name="photo_pos_y" id="posYInput" value="<?= $curPosY ?>">
      <input type="hidden" name="photo_zoom" id="zoomInput" value="<?= $curZoom ?>">
    </div>

    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update' : 'Add' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="leaders.php">Cancel</a><?php endif; ?>
  </form>
</div>

<script>
(function() {
  var cropSection = document.getElementById('cropSection');
  var previewImg = document.getElementById('cropPreviewImg');
  var photoInput = document.getElementById('photoInput');
  var posXRange = document.getElementById('posXRange');
  var posYRange = document.getElementById('posYRange');
  var zoomRange = document.getElementById('zoomRange');
  var posXInput = document.getElementById('posXInput');
  var posYInput = document.getElementById('posYInput');
  var zoomInput = document.getElementById('zoomInput');

  function applyPreview() {
    var x = posXRange.value, y = posYRange.value, z = zoomRange.value;
    previewImg.style.objectPosition = x + '% ' + y + '%';
    previewImg.style.transform = 'scale(' + (z / 100) + ')';
    previewImg.style.transformOrigin = x + '% ' + y + '%';
    posXInput.value = x;
    posYInput.value = y;
    zoomInput.value = z;
  }

  [posXRange, posYRange, zoomRange].forEach(function(el) {
    el.addEventListener('input', applyPreview);
  });

  photoInput.addEventListener('change', function() {
    var file = photoInput.files && photoInput.files[0];
    if (!file) return;
    var reader = new FileReader();
    reader.onload = function(e) {
      previewImg.src = e.target.result;
      cropSection.style.display = '';
      posXRange.value = 50; posYRange.value = 20; zoomRange.value = 100;
      applyPreview();
    };
    reader.readAsDataURL(file);
  });
})();
</script>

<div class="admin-card">
  <h2>All Leaders</h2>
  <div class="table-scroll">
  <table>
    <tr><th>Photo</th><th>Role</th><th>Name</th><th>Phone</th><th></th></tr>
    <?php while ($row = $all->fetch_assoc()): ?>
    <tr>
      <td>
        <?php if (!empty($row['photo'])): ?>
          <span style="display:inline-block; width:40px; height:40px; border-radius:8px; overflow:hidden;">
            <img src="<?= $base ?>assets/img/leaders/<?= h($row['photo']) ?>" alt=""
                 style="width:100%; height:100%; object-fit:cover; object-position: <?= (int)($row['photo_pos_x'] ?? 50) ?>% <?= (int)($row['photo_pos_y'] ?? 20) ?>%; transform: scale(<?= ((int)($row['photo_zoom'] ?? 100)) / 100 ?>);">
          </span>
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
