<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/_layout_top.php';
?>

<div class="admin-card">
  <h2>Welcome</h2>
  <p>Use the links above (or below) to manage the site's content.</p>
  <?php if (is_super_admin()): ?>
  <p>
    <a class="btn" href="settings.php">Edit Site Info</a>
    &nbsp; <a class="btn btn-secondary" href="leaders.php">Manage Leadership</a>
  </p>
  <p>
    <a class="btn" href="fellowships.php">Manage Fellowships</a>
    &nbsp; <a class="btn btn-secondary" href="magazines.php">Manage Magazines</a>
  </p>
  <?php endif; ?>
  <p>
    <a class="btn" href="service.php">Edit Sunday Service Card</a>
    &nbsp; <a class="btn btn-secondary" href="celebrations.php">Manage Birthday / Anniversary Cards</a>
  </p>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
