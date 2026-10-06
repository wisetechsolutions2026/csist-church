<?php
$pageTitle = 'Dashboard';
require __DIR__ . '/_layout_top.php';

$tiles = [
    ['service', '&#9728;&#65039;', 'Sunday Service', 'Update this Sunday\'s date and service timings.', false],
    ['celebrations', '&#127874;', 'Birthday / Anniversary', 'Add or edit celebration cards shown on the home page.', false],
    ['settings', '&#9881;&#65039;', 'Site Info', 'Church name, tagline, address and contact details.', true],
    ['leaders', '&#128081;', 'Leadership', 'Presbyter, Secretary, Treasurer and their photos.', true],
    ['fellowships', '&#129309;', 'Fellowships', 'Manage fellowship pages and descriptions.', true],
    ['magazines', '&#128214;', 'Magazines', 'Upload monthly magazine PDFs.', false],
    ['gallery', '&#128247;', 'Gallery', 'Create events with a date and upload their photos.', false],
];
?>
<style>
  .dash-hero { margin-bottom:26px; }
  .dash-hero h1 { font-family:'Cormorant Garamond',Georgia,serif; font-size:2.2rem; color:var(--ink-3); margin:0; }
  .dash-hero p { color:var(--muted); margin:4px 0 0; }
  .dash-grid { display:grid; grid-template-columns:repeat(auto-fill, minmax(260px, 1fr)); gap:22px; }
  .dash-card {
    position:relative; display:flex; flex-direction:column; gap:8px; overflow:hidden;
    padding:26px 24px 24px; border-radius:20px; color:#fff;
    background:radial-gradient(circle at 25% 15%, var(--ink-3), var(--ink) 80%);
    border:1px solid rgba(242,182,50,0.35); box-shadow:0 24px 50px -22px rgba(10,14,26,0.55);
    transition:transform .3s ease, box-shadow .3s ease;
  }
  .dash-card:hover { transform:translateY(-6px); box-shadow:0 32px 60px -20px rgba(10,14,26,0.65); color:#fff; }
  .dash-card::after { content:''; position:absolute; right:-40px; top:-40px; width:130px; height:130px; border-radius:50%; background:var(--grad); opacity:.18; }
  .dash-icon { width:52px; height:52px; border-radius:14px; display:flex; align-items:center; justify-content:center; font-size:1.7rem; background:rgba(255,255,255,0.08); border:1px solid rgba(247,214,133,0.35); }
  .dash-card h3 { font-family:'Cormorant Garamond',Georgia,serif; font-size:1.45rem; font-weight:700; color:#fff; margin:10px 0 0; }
  .dash-card p { margin:0; font-size:0.85rem; color:#c9c6dc; line-height:1.5; }
  .dash-go { margin-top:10px; font-size:0.78rem; font-weight:600; letter-spacing:0.12em; text-transform:uppercase; color:var(--amber-soft); }
  @media (max-width:600px) { .dash-hero h1 { font-size:1.8rem; } .dash-grid { gap:16px; } }
</style>

<div class="dash-hero">
  <h1>Welcome back</h1>
  <p>Choose a section to manage.</p>
</div>

<div class="dash-grid">
<?php foreach ($tiles as [$href, $icon, $title, $desc, $superOnly]): ?>
  <?php if ($superOnly && !is_super_admin()) continue; ?>
  <a class="dash-card" href="<?= $href ?>">
    <span class="dash-icon"><?= $icon ?></span>
    <h3><?= $title ?></h3>
    <p><?= $desc ?></p>
    <span class="dash-go">Open &rarr;</span>
  </a>
<?php endforeach; ?>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
