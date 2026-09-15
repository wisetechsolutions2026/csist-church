<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Contact Us';
$leaders = db()->query('SELECT * FROM leaders ORDER BY sort_order');
$sent = false;
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Placeholder: wire this up to mail() or a mail service once the site is live on real hosting.
    $sent = true;
}
require __DIR__ . '/includes/header.php';
$base = base_url();
?>

<div class="page-hero">
  <h1>Contact Us</h1>
  <p>We'd love to hear from you</p>
</div>

<section>
  <div class="container">
    <div class="grid grid-3">
      <?php while ($l = $leaders->fetch_assoc()): ?>
      <div class="card">
        <div class="role"><?= h($l['role']) ?></div>
        <h3><?= h($l['name']) ?></h3>
        <p>Mb: <?= h($l['contact']) ?></p>
      </div>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<section class="alt">
  <div class="container grid grid-2">
    <div>
      <h2>Drop a Line</h2>
      <?php if ($sent): ?>
        <p style="color: var(--sapphire); font-weight: bold;">Thank you! Your message has been received.</p>
      <?php endif; ?>
      <form method="post" style="display:flex; flex-direction:column; gap:14px;">
        <input type="text" name="name" placeholder="Your Name" required style="padding:12px; border:1px solid var(--border); border-radius:4px; font-family:inherit;">
        <input type="email" name="email" placeholder="Your Email" required style="padding:12px; border:1px solid var(--border); border-radius:4px; font-family:inherit;">
        <textarea name="message" rows="5" placeholder="Your Message" required style="padding:12px; border:1px solid var(--border); border-radius:4px; font-family:inherit;"></textarea>
        <button type="submit" class="btn" style="cursor:pointer;">Send Message</button>
      </form>
    </div>
    <div>
      <h2>Reach Us</h2>
      <table class="contact-table">
        <tr><td>Church Location</td><td><?= h(setting('address')) ?></td></tr>
        <tr><td>Presbyter's E-mail</td><td><a href="mailto:<?= h(setting('email')) ?>"><?= h(setting('email')) ?></a></td></tr>
      </table>
    </div>
  </div>
</section>

<section>
  <div class="container">
    <iframe class="map-frame" src="https://www.google.com/maps?q=CSI+St+Matthews+Church+Porur+Chennai&output=embed" allowfullscreen loading="lazy"></iframe>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
