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

<?php
$serviceEnabled = setting('service_card_enabled') === '1';
$birthdays = [];
$res = db()->query("SELECT * FROM celebrations WHERE type='birthday' AND is_active=1 ORDER BY sort_order, id DESC");
while ($row = $res->fetch_assoc()) { $birthdays[] = $row; }
$anniversaries = [];
$res = db()->query("SELECT * FROM celebrations WHERE type='anniversary' AND is_active=1 ORDER BY sort_order, id DESC");
while ($row = $res->fetch_assoc()) { $anniversaries[] = $row; }

function render_celebration_card(array $c, string $label, string $fallbackIcon, string $base, string $cls = 'reveal', int $delay = 0): void {
    ?>
    <div class="celebration-card <?= $cls ?>"<?= $delay ? ' style="animation-delay:' . $delay . 'ms"' : '' ?>>
      <div class="celebration-photo-wrap">
        <?php if (!empty($c['photo'])): ?>
          <img class="celebration-photo" src="<?= $base ?>assets/img/celebrations/<?= h($c['photo']) ?>" alt="<?= h($c['name']) ?>">
        <?php else: ?>
          <span class="celebration-icon"><?= $fallbackIcon ?></span>
        <?php endif; ?>
      </div>
      <div class="celebration-body">
        <span class="celebration-label"><?= h($label) ?></span>
        <?php if (strpos($c['name'], '&') !== false): ?>
          <?php $members = array_map('trim', explode('&', $c['name'])); ?>
          <span class="celebration-name is-couple"><?php foreach ($members as $mi => $member): ?><?php if ($mi > 0): ?><span class="couple-amp">&amp;</span><?php endif; ?><?= h($member) ?><?php endforeach; ?></span>
        <?php else: ?>
          <span class="celebration-name"><?= h($c['name']) ?></span>
        <?php endif; ?>
        <span class="celebration-date"><?= h(format_ordinal_date($c['occasion_date'])) ?></span>
      </div>
    </div>
    <?php
}
?>
<?php if ($birthdays || $anniversaries): ?>
<div id="celebSplash" class="celeb-splash" role="dialog" aria-modal="true" aria-label="Celebrations" hidden>
  <div class="splash-confetti" aria-hidden="true"></div>
  <div class="splash-balloons" aria-hidden="true">
    <span style="left:6%;--d:0s;--s:2.6rem">&#127880;</span><span style="left:18%;--d:1.2s;--s:3.2rem">&#127880;</span>
    <span style="left:82%;--d:.6s;--s:3rem">&#127880;</span><span style="left:92%;--d:1.8s;--s:2.4rem">&#127880;</span>
    <span style="left:50%;--d:2.4s;--s:2.2rem">&#127881;</span>
  </div>
  <div class="splash-inner">
    <div class="splash-title"><?= $birthdays ? 'Happy Birthday!' : 'Happy Anniversary!' ?></div>
    <p class="splash-sub">Wishing you God's richest blessings</p>
    <div class="splash-cards">
      <?php $di = 0; foreach ($birthdays as $birthday): $di++; render_celebration_card($birthday, 'Happy Birthday', '&#127874;', $base, 'splash-pop', 250 + $di * 160); endforeach; ?>
      <?php foreach ($anniversaries as $anniversary): $di++; render_celebration_card($anniversary, 'Happy Anniversary', '&#128141;', $base, 'splash-pop', 250 + $di * 160); endforeach; ?>
    </div>
    <button type="button" class="splash-close btn">Continue to site &rarr;</button>
  </div>
  <div class="splash-timer" aria-hidden="true"><span></span>
  </div>
</div>
<script>
(function () {
  var s = document.getElementById('celebSplash');
  if (!s) return;
  var key = 'celebSplash:<?= date('Y-m-d') ?>:<?= md5(json_encode([$birthdays, $anniversaries])) ?>';
  try { if (sessionStorage.getItem(key)) return; } catch (e) {}
  s.hidden = false;
  document.documentElement.style.overflow = 'hidden';
  var box = s.querySelector('.splash-confetti');
  var colors = ['#f2b632', '#f7d685', '#3a63c8', '#8a4fd1', '#c8385a', '#ffffff'];
  for (var i = 0; i < 70; i++) {
    var p = document.createElement('i');
    p.style.left = Math.random() * 100 + '%';
    p.style.background = colors[i % colors.length];
    p.style.animationDelay = (Math.random() * 4) + 's';
    p.style.animationDuration = (3.5 + Math.random() * 3) + 's';
    p.style.setProperty('--sway', (Math.random() * 120 - 60) + 'px');
    p.style.width = (6 + Math.random() * 7) + 'px';
    p.style.height = (9 + Math.random() * 9) + 'px';
    box.appendChild(p);
  }
  function close() {
    try { sessionStorage.setItem(key, '1'); } catch (e) {}
    s.classList.add('closing');
    setTimeout(function () { s.hidden = true; document.documentElement.style.overflow = ''; }, 500);
    document.removeEventListener('keydown', onKey);
  }
  function onKey(e) { if (e.key === 'Escape') close(); }
  s.querySelector('.splash-close').addEventListener('click', close);
  s.addEventListener('click', function (e) { if (e.target === s) close(); });
  document.addEventListener('keydown', onKey);
  if (typeof fitCelebrationNames === 'function') setTimeout(fitCelebrationNames, 50);
  var auto = setTimeout(close, 5000);
  s.querySelector('.splash-close').addEventListener('click', function () { clearTimeout(auto); });
})();
</script>
<?php endif; ?>
<?php if ($birthdays || $anniversaries): ?>
<div class="service-flash-wrap">
  <?php foreach ($birthdays as $birthday): ?>
    <?php render_celebration_card($birthday, 'Happy Birthday', '&#127874;', $base); ?>
  <?php endforeach; ?>
  <?php foreach ($anniversaries as $anniversary): ?>
    <?php render_celebration_card($anniversary, 'Happy Anniversary', '&#128141;', $base); ?>
  <?php endforeach; ?>
</div>
<?php endif; ?>

<?php if ($serviceEnabled): ?>
<div class="service-flash-wrap<?= ($birthdays || $anniversaries) ? ' service-flash-wrap-below' : '' ?>">
  <div class="service-flash-card reveal">
    <span class="service-flash-ribbon">This Sunday</span>
    <div class="service-flash-date"><?= h(format_ordinal_date(setting('service_date'))) ?></div>
    <div class="service-flash-times">
      <div class="service-time-badge">
        <span class="service-time-icon">&#9728;&#65039;</span>
        <span class="service-time-label">Morning Service</span>
        <span class="service-time-value"><?= h(setting('service_morning')) ?></span>
      </div>
      <div class="service-time-badge">
        <span class="service-time-icon">&#127769;</span>
        <span class="service-time-label">Evening Service</span>
        <span class="service-time-value"><?= h(setting('service_evening')) ?></span>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>

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
      <div class="card leader-card reveal reveal-delay-<?= $i ?>">
        <div class="leader-photo-wrap">
          <?php if (!empty($l['photo'])): ?>
            <?php
              $lx = (int)($l['photo_pos_x'] ?? 50);
              $ly = (int)($l['photo_pos_y'] ?? 20);
              $lz = ((int)($l['photo_zoom'] ?? 100)) / 100;
            ?>
            <img class="leader-photo" src="<?= $base ?>assets/img/leaders/<?= h($l['photo']) ?>" alt="<?= h($l['name']) ?>"
                 style="object-position: <?= $lx ?>% <?= $ly ?>%; transform: scale(<?= $lz ?>); transform-origin: <?= $lx ?>% <?= $ly ?>%;">
          <?php else: ?>
            <span class="leader-initials"><?= h(initials($l['name'])) ?></span>
          <?php endif; ?>
        </div>
        <div class="leader-body">
          <div class="role"><?= h($l['role']) ?></div>
          <h3><?= h($l['name']) ?></h3>
          <p>Ph: <?= h($l['contact']) ?></p>
        </div>
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
