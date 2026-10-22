<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Branch Churches';
$branches = db()->query('SELECT * FROM branch_churches ORDER BY sort_order, id')->fetch_all(MYSQLI_ASSOC);
require __DIR__ . '/includes/header.php';
$base = base_url();
?>

<div class="page-hero">
  <h1>Branch Churches</h1>
  <p>Our worshipping communities around Porur</p>
</div>

<section>
  <div class="container">
    <div class="branch-grid">
      <?php $i = 0; foreach ($branches as $b): $i++; ?>
      <article class="branch-card reveal reveal-delay-<?= min($i, 3) ?>">
        <div class="branch-photo">
          <?php if (!empty($b['photo'])): ?>
          <img src="<?= $base ?>assets/img/branches/<?= h($b['photo']) ?>" alt="<?= h($b['name']) ?>" loading="lazy">
          <?php else: ?>
          <span class="branch-placeholder">&#9962;</span>
          <?php endif; ?>
        </div>
        <div class="branch-info">
          <h3><?= h($b['name']) ?></h3>
          <?php if ($b['location'] !== ''): ?><p class="branch-loc">&#128205; <?= h($b['location']) ?></p><?php endif; ?>
          <?php if ($b['catechist'] !== ''): ?>
          <div class="branch-cat"><span>Catechist</span><strong><?= h($b['catechist']) ?></strong></div>
          <?php endif; ?>
        </div>
      </article>
      <?php endforeach; ?>
      <?php if (!$branches): ?><p style="grid-column:1/-1; text-align:center; color:var(--text-muted);">Coming soon.</p><?php endif; ?>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
