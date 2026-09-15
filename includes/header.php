<?php
require_once __DIR__ . '/config.php';
$base = base_url();
$current = basename($_SERVER['SCRIPT_NAME']);
function navlink(string $file, string $label, string $current, string $base): string {
    $active = $current === $file ? ' class="active"' : '';
    return '<li><a href="' . $base . $file . '"' . $active . '>' . $label . '</a></li>';
}
?><!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?= isset($pageTitle) ? h($pageTitle) . ' — ' : '' ?><?= h(setting('site_name')) ?></title>
<meta name="description" content="<?= h(setting('site_name')) ?>, <?= h(setting('site_location')) ?> — <?= h(setting('tagline')) ?>">
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,600;0,700;1,600;1,700&family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="<?= $base ?>assets/css/style.css?v=<?= filemtime(__DIR__ . '/../assets/css/style.css') ?>">
</head>
<body>
<header class="site-header">
  <div class="header-inner">
    <a class="brand" href="<?= $base ?>index.php">
      <span class="logo-badge">
        <img src="<?= $base ?>assets/img/logo-header.png" alt="<?= h(setting('site_name')) ?> logo">
      </span>
      <span class="brand-text">
        <span class="site-title"><?= h(setting('site_name')) ?></span><br>
        <span class="site-sub"><?= h(setting('site_location')) ?></span>
      </span>
    </a>
    <button class="nav-toggle" aria-label="Toggle menu">&#9776;</button>
    <nav class="main-nav">
      <ul>
        <?= navlink('index.php', 'Home', $current, $base) ?>
        <?= navlink('about.php', 'About Us', $current, $base) ?>
        <?= navlink('fellowships.php', 'Fellowships', $current, $base) ?>
        <?= navlink('gallery.php', 'Gallery', $current, $base) ?>
        <?= navlink('live-streaming.php', 'Live Streaming', $current, $base) ?>
        <?= navlink('magazines.php', 'Magazine', $current, $base) ?>
        <?= navlink('contact.php', 'Contact', $current, $base) ?>
        <li class="nav-cta-item"><a class="nav-cta" href="<?= $base ?>live-streaming.php">Watch Live</a></li>
      </ul>
    </nav>
  </div>
</header>
