<?php
$pageTitle = 'Obituary Notices';
require __DIR__ . '/_layout_top.php';
require_once __DIR__ . '/../includes/obituary.php';

obit_ensure_table();
$flash = '';
$error = '';
$uploadDir = __DIR__ . '/../assets/img/obituaries/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function store_obit_photo(?array $file, string $dir): ?string {
    if (!$file || ($file['error'] ?? UPLOAD_ERR_NO_FILE) !== UPLOAD_ERR_OK) {
        return null;
    }
    $allowed = ['image/jpeg' => 'jpg', 'image/png' => 'png', 'image/webp' => 'webp'];
    $mime = @mime_content_type($file['tmp_name']);
    if (!isset($allowed[$mime])) {
        return null;
    }
    $name = 'obit-' . bin2hex(random_bytes(5)) . '.' . $allowed[$mime];
    $dest = $dir . $name;
    if ($mime === 'image/jpeg' && function_exists('imagecreatefromjpeg')) {
        $info = @getimagesize($file['tmp_name']);
        $orient = 1;
        if (function_exists('exif_read_data')) {
            $exif = @exif_read_data($file['tmp_name']);
            $orient = (int)($exif['Orientation'] ?? 1);
        }
        $max = 1200;
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
                if ($img && imagejpeg($img, $dest, 88)) {
                    imagedestroy($img);
                    return $name;
                }
            }
        }
    }
    return move_uploaded_file($file['tmp_name'], $dest) ? $name : null;
}

function delete_obit_photo(?string $name, string $dir): void {
    if ($name && strpos($name, '/') === false && strpos($name, '..') === false) {
        @unlink($dir . $name);
    }
}

$validDate = fn($d) => preg_match('/^\d{4}-\d{2}-\d{2}$/', $d) === 1 ? $d : null;
$validTime = fn($t) => preg_match('/^\d{2}:\d{2}$/', $t) === 1 ? $t : '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $name = trim($_POST['name'] ?? '');
        $age = trim($_POST['age'] ?? '');
        $passedOn = $validDate(trim($_POST['passed_on'] ?? ''));
        $address = trim($_POST['address'] ?? '');
        $phone = trim($_POST['phone'] ?? '');
        $sTitle = trim($_POST['service_title'] ?? '') ?: 'Funeral Service';
        $sPlace = trim($_POST['service_place'] ?? '');
        $sDate = $validDate(trim($_POST['service_date'] ?? ''));
        $sTime = $validTime(trim($_POST['service_time'] ?? ''));
        $note = trim($_POST['note'] ?? '');
        $active = isset($_POST['is_active']) ? 1 : 0;
        $newPhoto = store_obit_photo($_FILES['photo'] ?? null, $uploadDir);
        $removePhoto = isset($_POST['remove_photo']);

        if ($name === '') {
            $error = 'Please enter the name.';
        } elseif ($id > 0) {
            $old = db()->query('SELECT photo FROM obituaries WHERE id=' . $id)->fetch_assoc();
            $photoSql = '';
            if ($newPhoto !== null) {
                delete_obit_photo($old['photo'] ?? null, $uploadDir);
                $photoSql = ', photo=?';
            } elseif ($removePhoto) {
                delete_obit_photo($old['photo'] ?? null, $uploadDir);
                $photoSql = ', photo=NULL';
            }
            $stmt = db()->prepare('UPDATE obituaries SET name=?, age=?, passed_on=?, address=?, phone=?, service_title=?, service_place=?, service_date=?, service_time=?, note=?, is_active=?' . $photoSql . ' WHERE id=?');
            if ($newPhoto !== null) {
                $stmt->bind_param('ssssssssssisi', $name, $age, $passedOn, $address, $phone, $sTitle, $sPlace, $sDate, $sTime, $note, $active, $newPhoto, $id);
            } else {
                $stmt->bind_param('ssssssssssii', $name, $age, $passedOn, $address, $phone, $sTitle, $sPlace, $sDate, $sTime, $note, $active, $id);
            }
            $stmt->execute();
            $flash = 'Saved.';
        } else {
            $stmt = db()->prepare('INSERT INTO obituaries (name, age, passed_on, address, phone, service_title, service_place, service_date, service_time, note, is_active, photo) VALUES (?,?,?,?,?,?,?,?,?,?,?,?)');
            $stmt->bind_param('ssssssssssis', $name, $age, $passedOn, $address, $phone, $sTitle, $sPlace, $sDate, $sTime, $note, $active, $newPhoto);
            $stmt->execute();
            $flash = 'Notice published on the website.';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $old = db()->query('SELECT photo FROM obituaries WHERE id=' . $id)->fetch_assoc();
        delete_obit_photo($old['photo'] ?? null, $uploadDir);
        $stmt = db()->prepare('DELETE FROM obituaries WHERE id=?');
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $flash = 'Deleted.';
    }
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT * FROM obituaries WHERE id=?');
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
}
$all = db()->query('SELECT * FROM obituaries ORDER BY (service_date IS NULL) DESC, service_date DESC, id DESC')->fetch_all(MYSQLI_ASSOC);
$limit = (new DateTime(today_ist(), new DateTimeZone('Asia/Kolkata')))->modify('-1 day')->format('Y-m-d');
$base = '../';
$v = fn($k, $d = '') => h($editRow[$k] ?? $d);
?>

<?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="flash" style="background:#f3dfe0; border-color:#e8b9c0; color:#a52a47;"><?= h($error) ?></div><?php endif; ?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit obituary notice' : 'Add obituary notice' ?></h2>
  <p>The notice appears on the home page until the day after the funeral, and stays on the Obituary page as "In loving memory".</p>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow['id'] : 0 ?>">
    <label>Name</label>
    <input type="text" name="name" value="<?= $v('name') ?>" placeholder="e.g. Mr. A. B. Kumar" required>
    <label>Age (optional)</label>
    <input type="text" name="age" value="<?= $v('age') ?>" placeholder="e.g. 72" style="max-width:160px;">
    <label>Passed away on (optional)</label>
    <input type="date" name="passed_on" value="<?= $v('passed_on') ?>">
    <label>Photo</label>
    <?php if ($editRow && !empty($editRow['photo'])): ?>
      <div style="margin-bottom:10px;">
        <img src="<?= $base ?>assets/img/obituaries/<?= h($editRow['photo']) ?>" alt="" style="width:130px; border-radius:12px; border:1px solid var(--border);">
        <label style="margin-top:10px;"><input type="checkbox" name="remove_photo"> Remove current photo</label>
      </div>
    <?php endif; ?>
    <input type="file" name="photo" accept="image/png, image/jpeg, image/webp">
    <label>Address</label>
    <textarea name="address" rows="2" placeholder="House / street / area"><?= $v('address') ?></textarea>
    <label>Phone number</label>
    <input type="text" name="phone" value="<?= $v('phone') ?>" placeholder="e.g. 98xxxxxxxx">

    <h2 style="margin-top:30px; font-size:1.3rem;">Funeral / service</h2>
    <label>Service name</label>
    <input type="text" name="service_title" value="<?= $v('service_title', 'Funeral Service') ?>">
    <label>Date</label>
    <input type="date" name="service_date" value="<?= $v('service_date') ?>">
    <label>Time</label>
    <input type="time" name="service_time" value="<?= $v('service_time') ?>" style="max-width:200px;">
    <label>Place</label>
    <input type="text" name="service_place" value="<?= $v('service_place') ?>" placeholder="e.g. Residence, then CSI St. Matthew's Church, Porur">
    <label>Short message (optional)</label>
    <input type="text" name="note" value="<?= $v('note') ?>" placeholder="e.g. The Lord is my shepherd; I shall not want.">
    <label style="margin-top:16px;"><input type="checkbox" name="is_active" <?= (!$editRow || $editRow['is_active']) ? 'checked' : '' ?>> Show on the website</label>
    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update' : 'Publish notice' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="obituaries">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <h2>All notices</h2>
  <div class="table-scroll">
  <table>
    <tr><th>Photo</th><th>Name</th><th>Funeral</th><th>Status</th><th></th></tr>
    <?php foreach ($all as $row):
      $onHome = $row['is_active'] && ($row['service_date'] === null || $row['service_date'] >= $limit); ?>
    <tr>
      <td>
        <?php if (!empty($row['photo'])): ?>
          <img src="<?= $base ?>assets/img/obituaries/<?= h($row['photo']) ?>" alt="" style="width:44px; height:44px; object-fit:cover; border-radius:50%; display:block;">
        <?php else: ?><span style="color:#999; font-size:0.8rem;">None</span><?php endif; ?>
      </td>
      <td><?= h($row['name']) ?></td>
      <td><?= $row['service_date'] ? h(format_ordinal_date($row['service_date'])) . ($row['service_time'] !== '' ? ', ' . h(obit_time_12h($row['service_time'])) : '') : '—' ?></td>
      <td><span class="badge <?= $onHome ? 'badge-on' : 'badge-off' ?>"><?= !$row['is_active'] ? 'Hidden' : ($onHome ? 'On home page' : 'Archive') ?></span></td>
      <td class="row-actions">
        <a href="obituaries?edit=<?= (int)$row['id'] ?>">Edit</a>
        <a href="../obituaries?preview=<?= (int)$row['id'] ?>" target="_blank">Preview</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this notice?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$row['id'] ?>">
          <a href="#" onclick="this.closest('form').submit(); return false;" style="color:#a52a47;">Delete</a>
        </form>
      </td>
    </tr>
    <?php endforeach; ?>
    <?php if (!$all): ?><tr><td colspan="5" style="color:#999;">No notices yet.</td></tr><?php endif; ?>
  </table>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
