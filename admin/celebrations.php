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

$allowedTitles = ['Mr.', 'Ms.', 'Mrs.', 'Dr.', 'Baby', 'Master'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $type = $_POST['type'] === 'anniversary' ? 'anniversary' : 'birthday';
        $titles = $_POST['title'] ?? [];
        $names = $_POST['pname'] ?? [];
        $people = [];
        foreach ($names as $i => $n) {
            $n = trim(preg_replace('/\s+/', ' ', str_replace('&', ' ', (string)$n)));
            if ($n === '') continue;
            $t = trim((string)($titles[$i] ?? ''));
            if (!in_array($t, $allowedTitles, true)) $t = '';
            $people[] = trim($t . ' ' . $n);
        }
        $people = array_slice($people, 0, 4);
        $extraBirthdays = [];
        if ($type === 'birthday' && count($people) > 1) {
            $extraBirthdays = array_slice($people, 1);
            $people = [$people[0]];
        }
        $name = implode(' & ', $people);
        $occasion_date = trim($_POST['occasion_date'] ?? '');
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $newPhoto = handle_photo_upload($_FILES['photo'] ?? null);
        $removePhoto = isset($_POST['remove_photo']);

        if ($name === '') {
            $flash = 'Please enter a name.';
        } elseif ($id > 0) {
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
        if (empty($flash)) {
            $stmt->execute();
            $flash = 'Saved.';
            foreach ($extraBirthdays as $extraName) {
                $ins = db()->prepare("INSERT INTO celebrations (type, name, occasion_date, is_active, photo, sort_order) VALUES ('birthday', ?, ?, ?, NULL, 0)");
                $ins->bind_param('ssi', $extraName, $occasion_date, $is_active);
                $ins->execute();
            }
            if ($extraBirthdays) {
                $flash = 'Saved ' . (count($extraBirthdays) + 1) . ' separate birthday cards (one per person).';
            }
        }
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

$rows = [['title' => '', 'name' => '']];
if ($editRow) {
    $rows = [];
    foreach (explode(' & ', $editRow['name']) as $part) {
        $part = trim($part);
        $title = '';
        foreach ($allowedTitles as $t) {
            if (stripos($part, $t . ' ') === 0) { $title = $t; $part = trim(substr($part, strlen($t))); break; }
        }
        $rows[] = ['title' => $title, 'name' => $part];
    }
}

$all = db()->query("SELECT * FROM celebrations ORDER BY type, sort_order, id DESC");
$base = '../';
?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit Card' : 'Add New Card' ?></h2>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow['id'] : 0 ?>">
    <label>Type</label>
    <select name="type" id="typeSel">
      <option value="birthday" <?= (!$editRow || $editRow['type'] === 'birthday') ? 'selected' : '' ?>>Birthday</option>
      <option value="anniversary" <?= ($editRow && $editRow['type'] === 'anniversary') ? 'selected' : '' ?>>Wedding Anniversary</option>
    </select>
    <label>Name</label>
    <div id="people">
<?php foreach ($rows as $i => $p): ?>
      <div class="person-row">
        <select name="title[]" class="title-sel">
          <option value="">--</option>
<?php foreach ($allowedTitles as $t): ?>
          <option value="<?= h($t) ?>" <?= $p['title'] === $t ? 'selected' : '' ?>><?= h($t) ?></option>
<?php endforeach; ?>
        </select>
        <input type="text" name="pname[]" value="<?= h($p['name']) ?>" placeholder="Name" <?= $i === 0 ? 'required' : '' ?>>
        <button type="button" class="add-person" title="Add another name to this card">+</button>
        <button type="button" class="rm-person" title="Remove this name">&times;</button>
      </div>
<?php endforeach; ?>
    </div>
    <p style="font-size:0.8rem; color:#6b5a4d; margin-top:6px;">Tap + to add more names. Birthday: each name becomes its own card. Anniversary: all names stay on one card (up to 4).</p>
    <label>Date</label>
    <input type="date" name="occasion_date" value="<?= h($editRow['occasion_date'] ?? '') ?>" required>
    <p style="font-size:0.8rem; color:#6b5a4d; margin-top:4px;">Will display on the home page as: <strong><?= h(!empty($editRow['occasion_date']) ? format_ordinal_date($editRow['occasion_date']) : '—') ?></strong></p>

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
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="celebrations">Cancel</a><?php endif; ?>
  </form>
</div>

<style>
  .person-row { display:flex; gap:8px; align-items:center; margin-bottom:8px; }
  .person-row .title-sel { width:92px; flex:none; font-weight:600; }
  .person-row input[type=text] { flex:1; min-width:0; }
  .rm-person, .add-person { width:44px; height:44px; border-radius:50%; font-size:1.4rem; line-height:1; cursor:pointer; padding:0; flex:none; transition:transform .2s, box-shadow .2s, background .2s; }
  .rm-person { background:#fff; border:1px solid var(--border); color:var(--ruby); }
  .rm-person:hover { background:#f3dfe0; }
  .add-person { border:none; background:var(--grad); color:#fff; box-shadow:0 10px 22px -10px rgba(138,79,209,0.7); }
  .add-person:hover { transform:scale(1.08); }
</style>
<script>
(function(){
  var box=document.getElementById('people');
  function rows(){ return box.querySelectorAll('.person-row'); }
  function sync(){
    var r=rows();
    for(var i=0;i<r.length;i++){
      r[i].querySelector('.add-person').style.display = (i===r.length-1 && r.length<4) ? '' : 'none';
      r[i].querySelector('.rm-person').style.display = i>0 ? '' : 'none';
    }
  }
  box.addEventListener('click',function(e){
    if(e.target.classList.contains('add-person')){
      var c=rows()[0].cloneNode(true), inp=c.querySelector('input');
      inp.value=''; inp.removeAttribute('required'); c.querySelector('select').value='';
      box.appendChild(c); sync(); inp.focus();
    } else if(e.target.classList.contains('rm-person')){
      e.target.closest('.person-row').remove(); sync();
    }
  });
  sync();
})();
</script>

<div class="admin-card">
  <h2>All Cards</h2>
  <div class="table-scroll">
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
      <td><?= h(format_ordinal_date($row['occasion_date'])) ?></td>
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
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
