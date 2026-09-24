<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();
$base = base_url();
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?>Admin</title>
<meta name="robots" content="noindex, nofollow">
<style>
  * { box-sizing: border-box; }
  body { margin:0; font-family: -apple-system, 'Segoe UI', sans-serif; background:#f4f2ec; color:#2c1810; }
  .admin-header { background:#1c2540; color:#fff; padding:14px 24px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px; }
  .admin-header a { color:#f2dc9b; text-decoration:none; margin-right:18px; font-size:0.92rem; }
  .admin-header a:hover { color:#fff; }
  .admin-header .brand { font-weight:700; font-size:1.05rem; color:#fff; }
  .admin-wrap { max-width: 900px; margin: 0 auto; padding: 30px 20px 60px; }
  .admin-card { background:#fff; border:1px solid #e4dcc4; border-radius:10px; padding:26px; margin-bottom:24px; box-shadow: 0 6px 20px -10px rgba(10,14,26,0.15); }
  .admin-card h2 { margin-top:0; font-size:1.2rem; }
  label { display:block; font-size:0.85rem; font-weight:600; margin: 14px 0 6px; color:#3a2418; }
  input[type=text], input[type=password], textarea, select {
    width:100%; padding:10px 12px; border:1px solid #d8cdb0; border-radius:6px; font-size:0.95rem; font-family:inherit;
  }
  .btn { display:inline-block; background:#3a63c8; color:#fff; border:none; padding:10px 22px; border-radius:6px; font-size:0.9rem; font-weight:600; cursor:pointer; text-decoration:none; }
  .btn:hover { background:#2e50a0; }
  .btn-danger { background:#c8385a; }
  .btn-danger:hover { background:#a52a47; }
  .btn-secondary { background:#8a4fd1; }
  .btn-secondary:hover { background:#6e3ba8; }
  table { width:100%; border-collapse: collapse; margin-top:10px; }
  th, td { text-align:left; padding:10px 8px; border-bottom:1px solid #eee2c8; font-size:0.9rem; }
  th { color:#6b5a4d; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; }
  .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; }
  .badge-on { background:#dff3e3; color:#1e7a3a; }
  .badge-off { background:#f3dfe0; color:#a52a47; }
  .flash { background:#dff3e3; border:1px solid #b6e3c0; color:#1e7a3a; padding:10px 16px; border-radius:6px; margin-bottom:18px; font-size:0.9rem; }
  .row-actions a { margin-right:10px; font-size:0.85rem; }
</style>
</head>
<body>
<div class="admin-header">
  <div>
    <span class="brand">CSI St. Matthew's — Admin</span>
  </div>
  <nav>
    <a href="index.php">Dashboard</a>
    <a href="service.php">Sunday Service Card</a>
    <a href="celebrations.php">Birthday / Anniversary</a>
    <a href="logout.php">Logout</a>
  </nav>
</div>
<div class="admin-wrap">
