<?php $base = base_url(); ?>
<footer class="site-footer">
  <div class="container footer-grid">
    <div>
      <h4><?= h(setting('site_name')) ?></h4>
      <p><?= h(setting('address')) ?></p>
      <p><em>"<?= h(setting('tagline')) ?>"</em></p>
    </div>
    <div>
      <h4>Quick Links</h4>
      <p><a href="<?= $base ?>about.php">About Us</a></p>
      <p><a href="<?= $base ?>fellowships.php">Fellowships</a></p>
      <p><a href="<?= $base ?>gallery.php">Gallery</a></p>
      <p><a href="<?= $base ?>live-streaming.php">Live Streaming</a></p>
    </div>
    <div>
      <h4>Contact</h4>
      <p><a href="mailto:<?= h(setting('email')) ?>"><?= h(setting('email')) ?></a></p>
      <p><a href="<?= h(setting('youtube_channel')) ?>" target="_blank" rel="noopener">YouTube Channel</a></p>
    </div>
  </div>
  <div class="footer-bottom">
    &copy; <?= date('Y') ?> <?= h(setting('site_name')) ?>, <?= h(setting('site_location')) ?>. All rights reserved.
  </div>
</footer>

<div class="lightbox-overlay" id="lightbox">
  <button class="lightbox-close" aria-label="Close">&times;</button>
  <button class="lightbox-arrow lightbox-prev" aria-label="Previous image">&#8249;</button>
  <figure class="lightbox-figure">
    <img src="" alt="">
    <figcaption></figcaption>
  </figure>
  <button class="lightbox-arrow lightbox-next" aria-label="Next image">&#8250;</button>
</div>

<script src="<?= $base ?>assets/js/main.js"></script>
</body>
</html>
