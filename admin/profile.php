<?php
$pageTitle = 'My Profile';
require __DIR__ . '/_layout_top.php';

$flash = '';
$error = '';
$me = current_admin();
$canEdit = !empty($me['id']);

if ($canEdit && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $display = trim($_POST['display_name'] ?? '');
    $current = (string)($_POST['current_password'] ?? '');
    $new = (string)($_POST['new_password'] ?? '');
    $confirm = (string)($_POST['confirm_password'] ?? '');

    if ($display === '') {
        $error = 'Please enter your name.';
    } elseif ($new !== '' && strlen($new) < 8) {
        $error = 'The new password must be at least 8 characters.';
    } elseif ($new !== $confirm) {
        $error = 'The new password and its confirmation do not match.';
    } else {
        $stmt = db()->prepare('SELECT password_hash FROM admin_users WHERE id = ?');
        $uid = (int)$me['id'];
        $stmt->bind_param('i', $uid);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        if ($new !== '' && (!$row || !password_verify($current, $row['password_hash']))) {
            $error = 'Your current password is not correct.';
        } elseif ($new !== '') {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $up = db()->prepare('UPDATE admin_users SET display_name = ?, password_hash = ? WHERE id = ?');
            $up->bind_param('ssi', $display, $hash, $uid);
            $up->execute();
            $flash = 'Profile and password updated.';
        } else {
            $up = db()->prepare('UPDATE admin_users SET display_name = ? WHERE id = ?');
            $up->bind_param('si', $display, $uid);
            $up->execute();
            $flash = 'Profile updated.';
        }
        $me['display_name'] = $display;
    }
}
?>

<?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
<?php if ($error): ?><div class="flash" style="background:#f3dfe0; border-color:#e8b9c0; color:#a52a47;"><?= h($error) ?></div><?php endif; ?>

<div class="admin-card">
  <h2>My profile</h2>
  <?php if (!$canEdit): ?>
    <p>This account is not stored in the user database, so it can't be edited here.</p>
  <?php else: ?>
  <form method="post" autocomplete="off">
    <label>Full name</label>
    <input type="text" name="display_name" value="<?= h($me['display_name']) ?>" required>
    <label>Username</label>
    <input type="text" value="<?= h($me['username']) ?>" disabled>
    <label>Access level</label>
    <input type="text" value="<?= $me['role'] === 'super' ? 'Super Admin' : 'Limited' ?>" disabled>
    <label>Current password (only needed to change your password)</label>
    <input type="password" name="current_password" autocomplete="current-password">
    <label>New password (min 8 characters)</label>
    <input type="password" name="new_password" autocomplete="new-password">
    <label>Confirm new password</label>
    <input type="password" name="confirm_password" autocomplete="new-password">
    <button type="submit" class="btn" style="margin-top:20px;">Save profile</button>
  </form>
  <?php endif; ?>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
