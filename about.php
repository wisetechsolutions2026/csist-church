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
  <div class="container" style="max-width: 820px;">
    <span class="section-eyebrow reveal">Since the 1990s</span>
    <h2 class="section-title reveal">Our History</h2>
    <div class="divider reveal"><span></span></div>
    <?php foreach (explode("\n\n", setting('history')) as $i => $para): ?>
      <p class="reveal"><?= h($para) ?></p>
    <?php endforeach; ?>
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

<section>
  <div class="container reveal">
    <img src="<?= $base ?>assets/img/gallery/church-history/Untitled-design-2020-05-01T201522.258-1024x614.jpg" alt="Church history" style="border-radius: 10px; max-width: 760px; margin: 0 auto; box-shadow: var(--shadow-lux);">
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
