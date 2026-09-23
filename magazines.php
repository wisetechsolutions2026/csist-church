<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Monthly Magazine';
$mags = db()->query('SELECT * FROM magazines ORDER BY sort_order');
require __DIR__ . '/includes/header.php';
$base = base_url();
?>

<div class="page-hero">
  <h1>Monthly Magazine</h1>
  <p>Click a magazine to download</p>
</div>

<section>
  <div class="container">
    <div class="magazine-grid">
      <?php $i = 0; while ($m = $mags->fetch_assoc()): $i++; ?>
      <a class="magazine-card reveal reveal-delay-<?= min($i, 3) ?>" href="<?= $base ?>assets/pdf/<?= h($m['filename']) ?>" target="_blank">
        <div class="pdf-icon">
          <svg viewBox="0 0 48 48" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M12 4h16l8 8v28a2 2 0 0 1-2 2H12a2 2 0 0 1-2-2V6a2 2 0 0 1 2-2z" fill="url(#pdfGrad)"/>
            <path d="M28 4v6a2 2 0 0 0 2 2h6" fill="#fff" fill-opacity="0.35"/>
            <rect x="10" y="26" width="20" height="12" rx="2" fill="var(--ink-3)"/>
            <text x="20" y="35" text-anchor="middle" font-family="Inter, sans-serif" font-size="8" font-weight="700" fill="#fff">PDF</text>
            <defs>
              <linearGradient id="pdfGrad" x1="10" y1="4" x2="38" y2="42" gradientUnits="userSpaceOnUse">
                <stop stop-color="var(--sapphire)"/>
                <stop offset="1" stop-color="var(--amethyst)"/>
              </linearGradient>
            </defs>
          </svg>
        </div>
        <div><?= h($m['title']) ?></div>
        <div style="color: var(--sapphire); font-family:'Cormorant Garamond', serif; font-weight: 700;"><?= h($m['period']) ?></div>
      </a>
      <?php endwhile; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
