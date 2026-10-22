<?php
$pageTitle = 'Quiz Results';
require __DIR__ . '/_layout_top.php';
require_once __DIR__ . '/../includes/quiz.php';

$flash = '';
$errors = [];
$warnings = [];
$currentMonths = max(1, min(12, (int)setting('quiz_months', '9')));
$pasted = '';
quiz_ensure_table();
$existingCount = (int)db()->query('SELECT COUNT(*) c FROM quiz_results')->fetch_assoc()['c'];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $monthsSel = max(1, min(12, (int)($_POST['months'] ?? $currentMonths)));
    $pasted = (string)($_POST['data'] ?? '');
    [$rows, $errors, $warnings] = parse_quiz_text($pasted, $monthsSel);
    if (!$rows) {
        $errors[] = 'No result lines were found. Paste the table rows (S.No, name, marks for each month, total).';
    } elseif ($errors) {
        $flash = '';
        $errors[] = 'Nothing was saved. Fix the lines above and try again.';
    } elseif ($existingCount > 0 && count($rows) < $existingCount * 0.5 && !isset($_POST['allow_fewer'])) {
        $errors[] = 'This list has only ' . count($rows) . ' participants but the website has ' . $existingCount . '. Nothing was saved. If that is really what you want, tick "Allow a much shorter list" and save again.';
    } else {
        $n = quiz_import($rows, $monthsSel);
        $currentMonths = $monthsSel;
        $flash = 'Quiz results updated: ' . $n . ' participants, ' . QUIZ_MONTHS[0] . ' to ' . QUIZ_MONTHS[$monthsSel - 1] . '.';
        $pasted = '';
    }
}

quiz_ensure_table();
$count = (int)db()->query('SELECT COUNT(*) c FROM quiz_results')->fetch_assoc()['c'];
?>

<?php if ($flash): ?><div class="flash"><?= h($flash) ?></div><?php endif; ?>
<?php if ($errors): ?>
<div class="flash" style="background:#f3dfe0; border-color:#e8b9c0; color:#a52a47;">
  <?php foreach ($errors as $e): ?><div><?= h($e) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>
<?php if ($warnings): ?>
<div class="flash" style="background:#fff4d6; border-color:#f0dc9a; color:#7a5b00;">
  <?php foreach ($warnings as $w): ?><div><?= h($w) ?></div><?php endforeach; ?>
</div>
<?php endif; ?>

<div class="admin-card">
  <h2>Quiz Results</h2>
  <p><strong><?= $count ?></strong> participants are on the website now (<?= QUIZ_MONTHS[0] ?> to <?= QUIZ_MONTHS[$currentMonths - 1] ?>).
     <a href="../quiz" target="_blank">View the Quiz page &rarr;</a></p>
  <form method="post">
    <label>Months included in the list you are pasting</label>
    <select name="months">
      <?php for ($m = 1; $m <= 12; $m++): ?>
        <option value="<?= $m ?>" <?= $m === $currentMonths ? 'selected' : '' ?>><?= QUIZ_MONTHS[0] ?> to <?= QUIZ_MONTHS[$m - 1] ?> (<?= $m ?> month<?= $m > 1 ? 's' : '' ?>)</option>
      <?php endfor; ?>
    </select>
    <label>Results table</label>
    <textarea name="data" rows="14" placeholder="1 Ajay Kumar J 1502 25 25 25 25 24 24 24 25 25 222&#10;2 Allen Athavan 1384 24 24 25 24 25 24 25 24 25 220&#10;3 Amutha B 1152 AAA AAA AAA 23 AAA AAA AAA AAA AAA 23" style="font-family:Consolas,monospace; font-size:0.85rem;"><?= h($pasted) ?></textarea>
    <p style="font-size:0.8rem; color:#6b5a4d; margin-top:6px; line-height:1.6;">
      Paste one participant per line: <code>S.No, name, (optional number), one mark per month, total</code>. Write <code>AAA</code> for a month the person was absent.
      A heading line starting with "S No" is ignored. <strong>Saving replaces the whole list</strong>, so paste every participant each time.
    </p>
    <label style="margin-top:14px;"><input type="checkbox" name="allow_fewer"> Allow a much shorter list than the one on the website now</label>
    <button type="submit" class="btn" style="margin-top:18px;">Replace results</button>
  </form>
</div>

<?php require __DIR__ . '/_layout_bottom.php'; ?>
