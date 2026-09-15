<?php
require_once __DIR__ . '/config.php';
header('Cache-Control: no-cache, no-store, must-revalidate');
header('Pragma: no-cache');
header('Expires: 0');
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
<?php
$pageUrl = 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];
$ogImage = 'https://' . $_SERVER['HTTP_HOST'] . $base . 'assets/img/gallery/church-history/Untitled-design-2020-05-01T201522.258-1024x614.jpg';
$ogTitle = (isset($pageTitle) ? $pageTitle . ' — ' : '') . setting('site_name');
$ogDesc = setting('site_name') . ', ' . setting('site_location') . ' — ' . setting('tagline');
?>
<meta property="og:type" content="website">
<meta property="og:url" content="<?= h($pageUrl) ?>">
<meta property="og:title" content="<?= h($ogTitle) ?>">
<meta property="og:description" content="<?= h($ogDesc) ?>">
<meta property="og:image" content="<?= h($ogImage) ?>">
<meta name="twitter:card" content="summary_large_image">
<meta name="twitter:title" content="<?= h($ogTitle) ?>">
<meta name="twitter:description" content="<?= h($ogDesc) ?>">
<meta name="twitter:image" content="<?= h($ogImage) ?>">
<link rel="icon" type="image/png" href="<?= $base ?>assets/img/logo-header.png">
<link rel="apple-touch-icon" href="<?= $base ?>assets/img/logo-header.png">
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
        <span class="site-title"><?= h(setting('site_name')) ?></span>
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
