<?php
$pageTitle = 'Magazines';
require __DIR__ . '/_layout_top.php';

$flash = '';
$uploadDir = __DIR__ . '/../assets/pdf/';
if (!is_dir($uploadDir)) {
    mkdir($uploadDir, 0755, true);
}

function handle_pdf_upload(?array $file): ?string {
    global $uploadDir;
    if (!$file || $file['error'] === UPLOAD_ERR_NO_FILE) {
        return null;
    }
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return null;
    }
    $mime = mime_content_type($file['tmp_name']);
    if ($mime !== 'application/pdf') {
        return null;
    }
    $safeName = preg_replace('/[^a-zA-Z0-9_\-]/', '-', pathinfo($file['name'], PATHINFO_FILENAME));
    $filename = $safeName . '-' . bin2hex(random_bytes(3)) . '.pdf';
    move_uploaded_file($file['tmp_name'], $uploadDir . $filename);
    return $filename;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $title = trim($_POST['title'] ?? '');
        $period = trim($_POST['period'] ?? '');
        $newPdf = handle_pdf_upload($_FILES['pdf'] ?? null);

        if ($id > 0) {
            if ($newPdf !== null) {
                $stmt = db()->prepare("UPDATE magazines SET title=?, period=?, filename=? WHERE id=?");
                $stmt->bind_param('sssi', $title, $period, $newPdf, $id);
            } else {
                $stmt = db()->prepare("UPDATE magazines SET title=?, period=? WHERE id=?");
                $stmt->bind_param('ssi', $title, $period, $id);
            }
            $stmt->execute();
        } elseif ($newPdf !== null) {
            $nextOrder = (int)(db()->query("SELECT COALESCE(MIN(sort_order),1)-1 n FROM magazines")->fetch_assoc()['n']);
            $stmt = db()->prepare("INSERT INTO magazines (title, period, filename, sort_order) VALUES (?, ?, ?, ?)");
            $stmt->bind_param('sssi', $title, $period, $newPdf, $nextOrder);
            $stmt->execute();
        } else {
            $flash = 'Please choose a PDF file when adding a new magazine.';
        }
        if (empty($flash)) $flash = 'Saved.';
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $row = db()->query("SELECT filename FROM magazines WHERE id=" . $id)->fetch_assoc();
        if ($row && $row['filename'] && file_exists($uploadDir . $row['filename'])) {
            @unlink($uploadDir . $row['filename']);
        }
        $stmt = db()->prepare("DELETE FROM magazines WHERE id=?");
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $flash = 'Deleted.';
    }
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare("SELECT * FROM magazines WHERE id=?");
    $id = (int)$_GET['edit'];
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
}

$all = db()->query("SELECT * FROM magazines ORDER BY sort_order");
$base = '../';
?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit Magazine' : 'Add New Magazine' ?></h2>
  <?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
  <form method="post" enctype="multipart/form-data">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow['id'] : 0 ?>">
    <label>Title</label>
    <input type="text" name="title" value="<?= h($editRow['title'] ?? 'Monthly Magazine') ?>" required>
    <label>Period (e.g. October 2026)</label>
    <input type="text" name="period" value="<?= h($editRow['period'] ?? '') ?>" required>
    <label>PDF File<?= $editRow ? ' (leave empty to keep current file)' : '' ?></label>
    <?php if ($editRow && !empty($editRow['filename'])): ?>
      <p style="font-size:0.85rem; margin: 4px 0 10px;">Current: <a href="<?= $base ?>assets/pdf/<?= h($editRow['filename']) ?>" target="_blank"><?= h($editRow['filename']) ?></a></p>
    <?php endif; ?>
    <input type="file" name="pdf" accept="application/pdf">
    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update' : 'Add' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="magazines">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <h2>All Magazines</h2>
  <div class="table-scroll">
  <table>
    <tr><th>Title</th><th>Period</th><th>File</th><th></th></tr>
    <?php while ($row = $all->fetch_assoc()): ?>
    <tr>
      <td><?= h($row['title']) ?></td>
      <td><?= h($row['period']) ?></td>
      <td><a href="<?= $base ?>assets/pdf/<?= h($row['filename']) ?>" target="_blank">View</a></td>
      <td class="row-actions">
        <a href="?edit=<?= (int)$row['id'] ?>">Edit</a>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this magazine?');">
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
