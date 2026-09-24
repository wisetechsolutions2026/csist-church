<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/_layout_top.php';
?>

<div class="admin-card">
  <h2>Welcome</h2>
  <p>Use the links above to manage the home page's Sunday service announcement and the birthday / anniversary cards.</p>
  <p><a class="btn" href="service.php">Edit Sunday Service Card</a>
  &nbsp; <a class="btn btn-secondary" href="celebrations.php">Manage Birthday / Anniversary Cards</a></p>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
