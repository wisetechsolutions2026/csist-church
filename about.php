<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'About Us';
$leaders = db()->query('SELECT * FROM leaders ORDER BY sort_order');
require __DIR__ . '/includes/header.php';
$base = base_url();

function initials(string $name): string {
    $name = preg_replace('/^(Rev\.|Mr\.|Mrs\.|Ms\.)\s*/i', '', $name);
    $parts = preg_split('/\s+/', trim($name));
    $letters = array_map(fn($p) => mb_strtoupper(mb_substr($p, 0, 1)), array_slice($parts, 0, 2));
    return implode('', $letters);
}
?>

<div class="page-hero">
  <h1>About Us</h1>
  <p><?= h(setting('site_name')) ?>, <?= h(setting('site_location')) ?></p>
</div>

<section>
  <div class="container">
    <span class="section-eyebrow reveal">Since the 1990s</span>
    <h2 class="section-title reveal">Our History</h2>
    <div class="divider reveal"><span></span></div>
    <div class="grid grid-2" style="align-items: center; gap: 50px;">
      <div class="reveal">
        <?php foreach (explode("\n\n", setting('history')) as $para): ?>
          <p><?= h($para) ?></p>
        <?php endforeach; ?>
      </div>
      <div class="reveal reveal-delay-1">
        <img src="<?= $base ?>assets/img/gallery/church-history/Untitled-design-2020-05-01T201522.258-1024x614.jpg" alt="CSI St. Matthew's Church building" style="border-radius: 14px; width: 100%; box-shadow: var(--shadow);">
      </div>
    </div>
  </div>
</section>

<section class="dark">
  <div class="container">
    <div class="stats-strip reveal">
      <div><div class="stat-num">700+</div><div class="stat-label">Families</div></div>
      <div><div class="stat-num">3</div><div class="stat-label">Sunday Services</div></div>
      <div><div class="stat-num">1990s</div><div class="stat-label">Founded</div></div>
    </div>
  </div>
</section>

<section class="alt">
  <div class="container">
    <span class="section-eyebrow reveal">Leadership</span>
    <h2 class="section-title reveal">Church Leadership</h2>
    <div class="divider reveal"><span></span></div>
    <p class="section-sub reveal">Serving our congregation</p>
    <div class="grid grid-3">
      <?php $i = 0; while ($l = $leaders->fetch_assoc()): $i++; ?>
      <div class="card reveal reveal-delay-<?= $i ?>">
        <div class="avatar-ring"><div class="avatar-inner"><?= h(initials($l['name'])) ?></div></div>
        <div class="role"><?= h($l['role']) ?></div>
        <h3><?= h($l['name']) ?></h3>
        <p>Ph: <?= h($l['contact']) ?></p>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
