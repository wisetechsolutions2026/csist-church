<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin-credentials.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    if (hash_equals(ADMIN_USERNAME, $username) && password_verify($password, ADMIN_PASSWORD_HASH)) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        header('Location: index.php');
        exit;
    }
    $error = 'Invalid username or password.';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Login</title>
<meta name="robots" content="noindex, nofollow">
<style>
  body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; font-family:-apple-system,'Segoe UI',sans-serif; background:#0a0e1a; }
  .login-box { background:#fffcf4; border-radius:12px; padding:36px 32px; width:100%; max-width:340px; box-shadow: 0 30px 70px -20px rgba(0,0,0,0.6); }
  .login-box h1 { font-size:1.2rem; margin:0 0 20px; color:#1c2540; text-align:center; }
  label { display:block; font-size:0.85rem; font-weight:600; margin:14px 0 6px; color:#3a2418; }
  input { width:100%; padding:10px 12px; border:1px solid #d8cdb0; border-radius:6px; font-size:0.95rem; box-sizing:border-box; }
  button { width:100%; margin-top:20px; background:#3a63c8; color:#fff; border:none; padding:11px; border-radius:6px; font-size:0.95rem; font-weight:600; cursor:pointer; }
  button:hover { background:#2e50a0; }
  .error { background:#f3dfe0; color:#a52a47; padding:10px 14px; border-radius:6px; font-size:0.85rem; margin-bottom:10px; }
</style>
</head>
<body>
  <div class="login-box">
    <h1>Admin Login</h1>
    <?php if ($error): ?><div class="error"><?= h($error) ?></div><?php endif; ?>
    <form method="post">
      <label>Username</label>
      <input type="text" name="username" required autofocus>
      <label>Password</label>
      <input type="password" name="password" required>
      <button type="submit">Log In</button>
    </form>
  </div>
</body>
</html>
