<?php
require_once __DIR__ . '/../includes/admin-auth.php';
require_admin();
if (!empty($requireSuper)) require_super_admin();
$base = base_url();
$currentPage = basename($_SERVER['SCRIPT_NAME']);
function nav_link(string $file, string $label): void {
    global $currentPage;
    echo '<a href="' . ($file === 'index.php' ? './' : substr($file, 0, -4)) . '"' . ($currentPage === $file ? ' class="active"' : '') . '>' . $label . '</a>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?>Admin</title>
<meta name="robots" content="noindex, nofollow">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --ink:#0a0e1a; --ink-2:#131a2e; --ink-3:#1c2540; --paper:#f6f1e4; --paper-card:#fffcf4;
    --sapphire:#3a63c8; --amethyst:#8a4fd1; --amber:#f2b632; --amber-soft:#f7d685; --ruby:#c8385a;
    --text:#201a2e; --muted:#6b6478; --border:#e4dcc4;
    --grad: linear-gradient(120deg, var(--sapphire), var(--amethyst) 55%, var(--ruby) 100%);
  }
  * { box-sizing: border-box; }
  body { margin:0; font-family:'Inter',-apple-system,'Segoe UI',sans-serif; background:var(--paper); color:var(--text); line-height:1.55; }
  a { color:var(--sapphire); text-decoration:none; }

  .admin-header {
    position:sticky; top:0; z-index:50;
    background:radial-gradient(circle at 20% 0%, var(--ink-3), var(--ink) 80%);
    color:#fff; padding:14px 28px; display:flex; align-items:center; justify-content:space-between; flex-wrap:wrap; gap:10px 24px;
    border-bottom:1px solid rgba(242,182,50,0.35); box-shadow:0 10px 30px -15px rgba(10,14,26,0.6);
  }
  .admin-header .brand { font-family:'Cormorant Garamond',Georgia,serif; font-weight:700; font-size:1.35rem; color:#fff; letter-spacing:0.01em; }
  .admin-header .brand small { display:block; font-family:'Inter',sans-serif; font-size:0.62rem; font-weight:500; letter-spacing:0.18em; text-transform:uppercase; color:var(--amber-soft); margin-top:-2px; }
  .admin-header nav { display:flex; flex-wrap:wrap; gap:6px; }
  .admin-header nav a { color:#d9d6e6; font-size:0.85rem; font-weight:500; padding:7px 15px; border-radius:20px; transition:background .25s, color .25s; }
  .admin-header nav a:hover { background:rgba(255,255,255,0.1); color:#fff; }
  .admin-header nav a.active { background:var(--grad); color:#fff; box-shadow:0 8px 20px -10px rgba(138,79,209,0.8); }
  .admin-header nav a.logout { color:var(--amber-soft); }

  .admin-wrap { max-width:960px; margin:0 auto; padding:34px 20px 70px; }
  .admin-card {
    background:var(--paper-card); border:1px solid var(--border); border-radius:18px; padding:28px 30px; margin-bottom:26px;
    box-shadow:0 25px 55px -30px rgba(10,14,26,0.35);
  }
  .admin-card h2 { font-family:'Cormorant Garamond',Georgia,serif; font-weight:700; font-size:1.7rem; color:var(--ink-3); margin:0 0 6px; }
  .admin-card h2::after { content:''; display:block; width:46px; height:3px; border-radius:2px; margin-top:8px; background:linear-gradient(90deg,var(--amber),var(--amber-soft)); }
  .admin-card > h2 + * { margin-top:18px; }

  label { display:block; font-size:0.72rem; font-weight:600; letter-spacing:0.08em; text-transform:uppercase; margin:20px 0 7px; color:var(--muted); }
  input[type=text], input[type=password], input[type=date], input[type=number], textarea, select {
    width:100%; padding:11px 14px; border:1px solid var(--border); border-radius:12px; font-size:0.95rem; font-family:inherit;
    background:#fff; color:var(--text); transition:border-color .2s, box-shadow .2s;
  }
  input:focus, textarea:focus, select:focus { outline:none; border-color:var(--sapphire); box-shadow:0 0 0 4px rgba(58,99,200,0.14); }
  input[type=file] { width:100%; font-size:0.85rem; padding:10px; border:1px dashed var(--border); border-radius:12px; background:#fff; }
  input[type=checkbox] { accent-color:var(--sapphire); width:16px; height:16px; vertical-align:-3px; margin-right:6px; }
  label:has(> input[type=checkbox]) { text-transform:none; letter-spacing:0; font-size:0.9rem; font-weight:500; color:var(--text); }

  .btn {
    display:inline-flex; align-items:center; justify-content:center; gap:8px;
    background:var(--grad); background-size:200% auto; color:#fff; border:none; padding:12px 28px; border-radius:30px;
    font-family:inherit; font-size:0.9rem; font-weight:600; letter-spacing:0.03em; cursor:pointer; text-decoration:none; text-align:center;
    box-shadow:0 14px 28px -12px rgba(138,79,209,0.55); transition:background-position .5s, transform .25s, box-shadow .25s;
  }
  .btn:hover { background-position:right center; transform:translateY(-2px); color:#fff; }
  .btn-secondary { background:#fff; color:var(--ink-3); border:1px solid var(--border); box-shadow:none; }
  .btn-secondary:hover { background:var(--paper); color:var(--ink-3); box-shadow:none; }
  .btn-danger { background:var(--ruby); box-shadow:none; }

  .table-scroll { overflow-x:auto; -webkit-overflow-scrolling:touch; margin-top:14px; }
  table { width:100%; border-collapse:separate; border-spacing:0; min-width:560px; }
  th, td { text-align:left; padding:12px 10px; border-bottom:1px solid var(--border); font-size:0.9rem; white-space:nowrap; vertical-align:middle; }
  th { color:var(--muted); font-size:0.68rem; font-weight:600; text-transform:uppercase; letter-spacing:0.12em; background:rgba(228,220,196,0.25); }
  th:first-child { border-top-left-radius:10px; } th:last-child { border-top-right-radius:10px; }
  tr:hover td { background:rgba(58,99,200,0.04); }
  .badge { display:inline-block; padding:3px 12px; border-radius:20px; font-size:0.7rem; font-weight:600; letter-spacing:0.03em; }
  .badge-on { background:#dff3e3; color:#1e7a3a; }
  .badge-off { background:#f3dfe0; color:#a52a47; }
  .flash { background:#e8f6ec; border:1px solid #b6e3c0; color:#1e7a3a; padding:12px 18px; border-radius:12px; margin-bottom:18px; font-size:0.9rem; font-weight:500; }
  .row-actions a { margin-right:12px; font-size:0.85rem; font-weight:600; }

  @media (max-width: 600px) {
    .admin-header { padding:12px 16px; flex-direction:column; align-items:flex-start; position:static; }
    .admin-header nav { width:100%; }
    .admin-header nav a { padding:6px 12px; font-size:0.8rem; }
    .admin-wrap { padding:20px 14px 50px; }
    .admin-card { padding:22px 18px; border-radius:16px; }
    .admin-card h2 { font-size:1.45rem; }
    .btn, .btn-secondary, .btn-danger { display:flex; width:100%; margin:10px 0 0 !important; }
    .admin-card > p > .btn { margin-top:12px !important; }
    th, td { font-size:0.82rem; padding:10px 8px; }
  }
</style>
</head>
<body>
<div class="admin-header">
  <div class="brand">CSI St. Matthew's<small>Admin Panel</small></div>
  <nav>
    <?php nav_link('index.php', 'Dashboard'); ?>
    <?php if (is_super_admin()): ?>
    <?php nav_link('settings.php', 'Site Info'); ?>
    <?php nav_link('leaders.php', 'Leadership'); ?>
    <?php nav_link('fellowships.php', 'Fellowships'); ?>
    <?php endif; ?>
    <?php nav_link('magazines.php', 'Magazines'); ?>
    <?php nav_link('service.php', 'Sunday Service'); ?>
    <?php nav_link('celebrations.php', 'Birthday / Anniv.'); ?>
    <a href="logout" class="logout">Logout</a>
  </nav>
</div>
<div class="admin-wrap">
