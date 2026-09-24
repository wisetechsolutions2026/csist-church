<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Home';
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

<?php
$slides = [
    ['img' => 'stock-cathedral.jpg', 'caption' => 'Engaging God\'s world through faith'],
    ['img' => 'stock-stainedglass.jpg', 'caption' => 'Light of the Gospel, ever shining'],
    ['img' => 'stock-candle.jpg', 'caption' => 'Gathered in prayer and worship'],
    ['img' => 'stock-cross.jpg', 'caption' => 'For God so loved the world'],
    ['img' => 'stock-bible.jpg', 'caption' => 'Rooted in His word'],
];
?>
<div class="hero-slider">
  <?php foreach ($slides as $i => $s): ?>
  <div class="slide <?= $i === 0 ? 'active' : '' ?>">
    <div class="slide-img" style="background-image: url('<?= $base ?>assets/img/slider/<?= h($s['img']) ?>');"></div>
    <div class="slide-overlay"></div>
  </div>
  <?php endforeach; ?>

  <div class="hero-content">
    <span class="hero-eyebrow">Est. in the 1990s &middot; Madras Diocese</span>
    <h1 class="word-reveal"><?= h(setting('site_name')) ?></h1>
    <p><?= h(setting('site_location')) ?> &mdash; <?= h(setting('tagline')) ?></p>
    <div class="hero-cta">
      <a class="btn btn-outline" href="<?= $base ?>live-streaming.php">&#9658; Watch Live</a>
      <a class="btn" href="<?= $base ?>about.php" style="margin-left:14px;">Our Story</a>
    </div>
  </div>

  <div class="slider-nav">
    <button class="slider-prev" aria-label="Previous slide">&#8249;</button>
    <button class="slider-next" aria-label="Next slide">&#8250;</button>
  </div>
  <div class="slider-dots"></div>
</div>

<div class="service-flash-wrap">
  <div class="service-flash-card reveal">
    <span class="service-flash-ribbon">This Sunday</span>
    <div class="service-flash-date">27<sup>th</sup> September 2026</div>
    <div class="service-flash-times">
      <div class="service-time-badge">
        <span class="service-time-icon">&#9728;&#65039;</span>
        <span class="service-time-label">Morning Service</span>
        <span class="service-time-value">8:30 AM</span>
      </div>
      <div class="service-time-badge">
        <span class="service-time-icon">&#127769;</span>
        <span class="service-time-label">Evening Service</span>
        <span class="service-time-value">6:30 PM</span>
      </div>
    </div>
  </div>
</div>

<section class="dark">
  <div class="container">
    <div class="stats-strip reveal">
      <div><div class="stat-num">700+</div><div class="stat-label">Families</div></div>
      <div><div class="stat-num">3</div><div class="stat-label">Sunday Services</div></div>
      <div><div class="stat-num">1990s</div><div class="stat-label">Founded</div></div>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <span class="section-eyebrow reveal">Our Presbyter</span>
    <h2 class="section-title reveal">Leadership in Faith</h2>
    <div class="divider reveal"><span></span></div>
    <blockquote class="verse reveal">
      For I am convinced that neither angels nor demons, neither death nor life, neither the present nor the future,
      nor anything else in all creation, will be able to separate us from the love of God.
      <cite>Romans 8:38&ndash;39</cite>
    </blockquote>
    <div class="grid grid-3">
      <?php $i = 0; $leaders->data_seek(0); while ($l = $leaders->fetch_assoc()): $i++; ?>
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

<section class="alt">
  <div class="container">
    <span class="section-eyebrow reveal">Welcome</span>
    <h2 class="section-title reveal">A Community Gathered in Worship</h2>
    <div class="divider reveal"><span></span></div>
    <div style="max-width: 760px; margin: 0 auto; text-align: center;" class="reveal">
      <p>CSI St. Matthew's Church, Porur has grown from a handful of families into a congregation of over 700 families,
      gathering every Sunday for worship across three services at different timings. We warmly welcome you to join us
      in worship, fellowship, and service.</p>
      <a class="btn" href="<?= $base ?>about.php">Read Our History</a>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <span class="section-eyebrow reveal">Fellowships</span>
    <h2 class="section-title reveal">Growing Together</h2>
    <div class="divider reveal"><span></span></div>
    <div class="grid grid-3">
      <?php
      $fellowships = db()->query('SELECT * FROM fellowships ORDER BY sort_order LIMIT 3');
      $i = 0;
      while ($f = $fellowships->fetch_assoc()):
        $i++;
        $img = db()->query("SELECT gi.filename FROM gallery_images gi JOIN gallery_categories gc ON gi.category_id=gc.id WHERE gc.slug='" . db()->real_escape_string($f['gallery_slug']) . "' LIMIT 1")->fetch_assoc();
      ?>
      <div class="fcard reveal reveal-delay-<?= $i ?>">
        <?php if ($img): ?>
        <div class="fimg-wrap">
          <img src="<?= $base ?>assets/img/gallery/<?= h($f['gallery_slug']) ?>/<?= h($img['filename']) ?>" alt="<?= h($f['title']) ?>">
        </div>
        <?php endif; ?>
        <div class="fcard-body">
          <h3><?= h($f['title']) ?></h3>
          <p style="color: var(--text-muted); font-style: italic;">"<?= h($f['tagline']) ?>"</p>
          <a href="<?= $base ?>fellowships.php?slug=<?= urlencode($f['slug']) ?>">Learn more &rarr;</a>
        </div>
      </div>
      <?php endwhile; ?>
    </div>
    <div style="text-align:center; margin-top: 40px;" class="reveal">
      <a class="btn" href="<?= $base ?>fellowships.php">View All Fellowships</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
