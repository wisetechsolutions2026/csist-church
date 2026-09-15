<?php
require_once __DIR__ . '/includes/config.php';
$pageTitle = 'Live Streaming';
require __DIR__ . '/includes/header.php';
$base = base_url();
?>

<div class="page-hero">
  <h1>Live Streaming</h1>
  <p>Join our Sunday Worship online</p>
</div>

<section>
  <div class="container" style="max-width: 820px;">
    <?php
    $channelId = basename(setting('youtube_channel'));
    $uploadsPlaylist = 'UU' . substr($channelId, 2);
    ?>
    <div class="video-embed">
      <iframe src="https://www.youtube.com/embed/videoseries?list=<?= h($uploadsPlaylist) ?>" title="CSI St. Matthew's Church Live Streaming" allowfullscreen></iframe>
    </div>
    <div style="text-align:center; margin-top: 30px;">
      <p>Watch our Sunday Worship live, and browse our archive of past services on our YouTube channel.</p>
      <a class="btn" href="<?= h(setting('youtube_channel')) ?>" target="_blank" rel="noopener">Visit Our YouTube Channel</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
