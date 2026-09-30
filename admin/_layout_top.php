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
  .admin-header { background:#1c2540; color:#fff; padding:14px 20px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px 0; }
  .admin-header nav { display:flex; flex-wrap:wrap; gap:4px 16px; }
  .admin-header a { color:#f2dc9b; text-decoration:none; font-size:0.9rem; padding:4px 0; }
  .admin-header a:hover { color:#fff; }
  .admin-header .brand { font-weight:700; font-size:1.05rem; color:#fff; }
  .admin-wrap { max-width: 900px; margin: 0 auto; padding: 24px 16px 60px; }
  .admin-card { background:#fff; border:1px solid #e4dcc4; border-radius:10px; padding:22px; margin-bottom:24px; box-shadow: 0 6px 20px -10px rgba(10,14,26,0.15); }
  .admin-card h2 { margin-top:0; font-size:1.15rem; }
  label { display:block; font-size:0.85rem; font-weight:600; margin: 14px 0 6px; color:#3a2418; }
  input[type=text], input[type=password], input[type=date], textarea, select {
    width:100%; padding:10px 12px; border:1px solid #d8cdb0; border-radius:6px; font-size:0.95rem; font-family:inherit;
  }
  input[type=file] { width:100%; font-size:0.85rem; }
  .btn { display:inline-block; background:#3a63c8; color:#fff; border:none; padding:11px 22px; border-radius:6px; font-size:0.9rem; font-weight:600; cursor:pointer; text-decoration:none; text-align:center; }
  .btn:hover { background:#2e50a0; }
  .btn-danger { background:#c8385a; }
  .btn-danger:hover { background:#a52a47; }
  .btn-secondary { background:#8a4fd1; }
  .btn-secondary:hover { background:#6e3ba8; }
  .table-scroll { overflow-x: auto; -webkit-overflow-scrolling: touch; margin-top:10px; }
  table { width:100%; border-collapse: collapse; min-width: 560px; }
  th, td { text-align:left; padding:10px 8px; border-bottom:1px solid #eee2c8; font-size:0.9rem; white-space: nowrap; }
  th { color:#6b5a4d; font-size:0.75rem; text-transform:uppercase; letter-spacing:0.05em; }
  .badge { display:inline-block; padding:3px 10px; border-radius:20px; font-size:0.72rem; font-weight:600; }
  .badge-on { background:#dff3e3; color:#1e7a3a; }
  .badge-off { background:#f3dfe0; color:#a52a47; }
  .flash { background:#dff3e3; border:1px solid #b6e3c0; color:#1e7a3a; padding:10px 16px; border-radius:6px; margin-bottom:18px; font-size:0.9rem; }
  .row-actions a { margin-right:10px; font-size:0.85rem; }

  @media (max-width: 600px) {
    .admin-header { flex-direction:column; align-items:flex-start; padding:14px 16px; }
    .admin-header nav { width:100%; gap:2px 14px; }
    .admin-card { padding:18px 16px; border-radius:8px; }
    .admin-card h2 { font-size:1.05rem; }
    .btn, .btn-secondary, .btn-danger { display:block; width:100%; margin: 8px 0 0 !important; }
    .admin-card form > .btn:first-of-type,
    .admin-card > p > .btn { margin-top:20px !important; }
    th, td { font-size:0.82rem; padding:8px 6px; }
  }
</style>
</head>
<body>
<div class="admin-header">
  <div>
    <span class="brand">CSI St. Matthew's — Admin</span>
  </div>
  <nav>
    <a href="index.php">Dashboard</a>
    <a href="settings.php">Site Info</a>
    <a href="leaders.php">Leadership</a>
    <a href="fellowships.php">Fellowships</a>
    <a href="magazines.php">Magazines</a>
    <a href="service.php">Sunday Service</a>
    <a href="celebrations.php">Birthday / Anniv.</a>
    <a href="logout.php">Logout</a>
  </nav>
</div>
<div class="admin-wrap">
