<?php
declare(strict_types=1);
require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/admin-auth.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $admin = null;
    $dbUser = find_admin_user($username);
    if ($dbUser) {
        if (password_verify($password, $dbUser['password_hash'])) {
            $admin = ['id' => (int)$dbUser['id'], 'role' => $dbUser['role']];
        }
    } elseif (!admin_users_table_has_rows() && isset(ADMINS[$username]) && password_verify($password, ADMINS[$username]['hash'])) {
        $admin = ['id' => 0, 'role' => ADMINS[$username]['role']];
    }
    if ($admin) {
        session_regenerate_id(true);
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_id'] = $admin['id'];
        $_SESSION['admin_username'] = $username;
        $_SESSION['admin_role'] = $admin['role'];
        header('Location: ./');
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
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@700&family=Inter:wght@400;500;600&display=swap" rel="stylesheet">
<style>
  body { margin:0; min-height:100vh; display:flex; align-items:center; justify-content:center; padding:20px; font-family:'Inter',-apple-system,sans-serif;
    background:radial-gradient(circle at 25% 15%, #1c2540, #0a0e1a 70%); }
  .login-box { background:#fffcf4; border:1px solid rgba(242,182,50,0.45); border-radius:22px; padding:40px 34px; width:100%; max-width:380px; box-shadow:0 40px 80px -25px rgba(0,0,0,0.7); }
  .login-box h1 { font-family:'Cormorant Garamond',Georgia,serif; font-size:1.9rem; margin:0 0 4px; color:#1c2540; text-align:center; }
  .login-box .sub { text-align:center; font-size:0.7rem; letter-spacing:0.18em; text-transform:uppercase; color:#b98513; margin:0 0 18px; }
  label { display:block; font-size:0.72rem; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; margin:18px 0 7px; color:#6b6478; }
  input { width:100%; padding:12px 14px; border:1px solid #e4dcc4; border-radius:12px; font-size:0.95rem; font-family:inherit; box-sizing:border-box; background:#fff; }
  input:focus { outline:none; border-color:#3a63c8; box-shadow:0 0 0 4px rgba(58,99,200,0.14); }
  button { width:100%; margin-top:26px; background:linear-gradient(120deg,#3a63c8,#8a4fd1 55%,#c8385a); color:#fff; border:none; padding:14px; border-radius:30px; font-family:inherit; font-size:0.95rem; font-weight:600; letter-spacing:0.03em; cursor:pointer; box-shadow:0 14px 28px -12px rgba(138,79,209,0.6); transition:transform .25s; }
  button:hover { transform:translateY(-2px); }
  .error { background:#f3dfe0; color:#a52a47; padding:11px 14px; border-radius:12px; font-size:0.85rem; margin-top:10px; }
</style>
</head>
<body>
  <div class="login-box">
    <h1>CSI St. Matthew's</h1>
    <p class="sub">Admin Login</p>
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
