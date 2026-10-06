<?php
$pageTitle = 'Users';
$requireSuper = true;
require __DIR__ . '/_layout_top.php';

$flash = '';
$error = '';
$me = current_admin();

function super_count(): int {
    return (int)db()->query("SELECT COUNT(*) c FROM admin_users WHERE role = 'super'")->fetch_assoc()['c'];
}

function valid_username(string $u): bool {
    return (bool)preg_match('/^[A-Za-z0-9._-]{3,30}$/', $u);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'save') {
        $id = (int)($_POST['id'] ?? 0);
        $display = trim($_POST['display_name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $role = ($_POST['role'] ?? '') === 'super' ? 'super' : 'limited';
        $password = (string)($_POST['password'] ?? '');

        $dup = db()->prepare('SELECT id FROM admin_users WHERE username = ? AND id <> ?');
        $dup->bind_param('si', $username, $id);
        $dup->execute();

        if ($display === '' || !valid_username($username)) {
            $error = 'Enter a name and a username (3–30 letters, numbers, dot, dash or underscore).';
        } elseif ($dup->get_result()->num_rows > 0) {
            $error = 'That username is already taken.';
        } elseif ($id === 0 && strlen($password) < 8) {
            $error = 'A new user needs a password of at least 8 characters.';
        } elseif ($password !== '' && strlen($password) < 8) {
            $error = 'The password must be at least 8 characters.';
        } elseif ($id > 0) {
            $cur = db()->prepare('SELECT role FROM admin_users WHERE id = ?');
            $cur->bind_param('i', $id);
            $cur->execute();
            $curRow = $cur->get_result()->fetch_assoc();
            if (!$curRow) {
                $error = 'User not found.';
            } elseif ($curRow['role'] === 'super' && $role !== 'super' && super_count() <= 1) {
                $error = 'There must always be at least one Super Admin.';
            } else {
                if ($password !== '') {
                    $hash = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = db()->prepare('UPDATE admin_users SET display_name=?, username=?, role=?, password_hash=? WHERE id=?');
                    $stmt->bind_param('ssssi', $display, $username, $role, $hash, $id);
                } else {
                    $stmt = db()->prepare('UPDATE admin_users SET display_name=?, username=?, role=? WHERE id=?');
                    $stmt->bind_param('sssi', $display, $username, $role, $id);
                }
                $stmt->execute();
                $flash = 'User updated' . ($password !== '' ? ' (password changed).' : '.');
            }
        } else {
            $hash = password_hash($password, PASSWORD_DEFAULT);
            $stmt = db()->prepare('INSERT INTO admin_users (display_name, username, password_hash, role) VALUES (?,?,?,?)');
            $stmt->bind_param('ssss', $display, $username, $hash, $role);
            $stmt->execute();
            $flash = 'User created.';
        }
    } elseif ($action === 'delete') {
        $id = (int)($_POST['id'] ?? 0);
        $cur = db()->prepare('SELECT role FROM admin_users WHERE id = ?');
        $cur->bind_param('i', $id);
        $cur->execute();
        $curRow = $cur->get_result()->fetch_assoc();
        if (!$curRow) {
            $error = 'User not found.';
        } elseif ($id === (int)$me['id']) {
            $error = 'You cannot delete your own account.';
        } elseif ($curRow['role'] === 'super' && super_count() <= 1) {
            $error = 'There must always be at least one Super Admin.';
        } else {
            $stmt = db()->prepare('DELETE FROM admin_users WHERE id = ?');
            $stmt->bind_param('i', $id);
            $stmt->execute();
            $flash = 'User deleted.';
        }
    }
}

$editRow = null;
if (isset($_GET['edit'])) {
    $stmt = db()->prepare('SELECT id, username, display_name, role FROM admin_users WHERE id = ?');
    $eid = (int)$_GET['edit'];
    $stmt->bind_param('i', $eid);
    $stmt->execute();
    $editRow = $stmt->get_result()->fetch_assoc();
}
$users = db()->query('SELECT id, username, display_name, role, created_at FROM admin_users ORDER BY role = \'super\' DESC, id')->fetch_all(MYSQLI_ASSOC);
$f = $_SERVER['REQUEST_METHOD'] === 'POST' && $error ? $_POST : [];
?>

<?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="flash" style="background:#f3dfe0; border-color:#e8b9c0; color:#a52a47;"><?= h($error) ?></div><?php endif; ?>

<div class="admin-card">
  <h2><?= $editRow ? 'Edit user' : 'Add new user' ?></h2>
  <form method="post" autocomplete="off">
    <input type="hidden" name="action" value="save">
    <input type="hidden" name="id" value="<?= $editRow ? (int)$editRow['id'] : 0 ?>">
    <label>Full name</label>
    <input type="text" name="display_name" value="<?= h($f['display_name'] ?? $editRow['display_name'] ?? '') ?>" required>
    <label>Username (used to log in)</label>
    <input type="text" name="username" value="<?= h($f['username'] ?? $editRow['username'] ?? '') ?>" required autocomplete="off">
    <label>Access level</label>
    <select name="role">
      <?php $curRole = $f['role'] ?? $editRow['role'] ?? 'limited'; ?>
      <option value="limited" <?= $curRole === 'limited' ? 'selected' : '' ?>>Limited — Sunday Service, Birthday/Anniversary, Magazines, Gallery</option>
      <option value="super" <?= $curRole === 'super' ? 'selected' : '' ?>>Super Admin — everything, including users</option>
    </select>
    <label><?= $editRow ? 'New password (leave blank to keep the current one)' : 'Password (min 8 characters)' ?></label>
    <input type="password" name="password" autocomplete="new-password" <?= $editRow ? '' : 'required' ?>>
    <button type="submit" class="btn" style="margin-top:20px;"><?= $editRow ? 'Update user' : 'Add user' ?></button>
    <?php if ($editRow): ?> <a class="btn btn-secondary" href="users">Cancel</a><?php endif; ?>
  </form>
</div>

<div class="admin-card">
  <h2>All users</h2>
  <div class="table-scroll">
  <table>
    <tr><th>Name</th><th>Username</th><th>Access</th><th>Created</th><th></th></tr>
    <?php foreach ($users as $u): ?>
    <tr>
      <td><?= h($u['display_name']) ?><?= (int)$u['id'] === (int)$me['id'] ? ' <span class="badge badge-on">You</span>' : '' ?></td>
      <td><?= h($u['username']) ?></td>
      <td><span class="badge <?= $u['role'] === 'super' ? 'badge-on' : 'badge-off' ?>" style="<?= $u['role'] === 'super' ? '' : 'background:#e4e8f6; color:#2e50a0;' ?>"><?= $u['role'] === 'super' ? 'Super Admin' : 'Limited' ?></span></td>
      <td><?= h(date('j M Y', strtotime($u['created_at']))) ?></td>
      <td class="row-actions">
        <a href="users?edit=<?= (int)$u['id'] ?>">Edit</a>
        <?php if ((int)$u['id'] !== (int)$me['id']): ?>
        <form method="post" style="display:inline" onsubmit="return confirm('Delete this user?');">
          <input type="hidden" name="action" value="delete">
          <input type="hidden" name="id" value="<?= (int)$u['id'] ?>">
          <a href="#" onclick="this.closest('form').submit(); return false;" style="color:#a52a47;">Delete</a>
        </form>
        <?php endif; ?>
      </td>
    </tr>
    <?php endforeach; ?>
  </table>
  </div>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
