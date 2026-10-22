<?php
require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/quiz.php';
$pageTitle = 'Quiz Results';
$base = base_url();

$months = max(1, min(12, (int)setting('quiz_months', '9')));
$rows = quiz_load();

$maxMark = 0.0;
foreach ($rows as $r) {
    foreach ($r['marks'] as $m) {
        if ($m !== null && $m > $maxMark) { $maxMark = $m; }
    }
}
$fullMarks = $maxMark * $months;
usort($rows, fn($a, $b) => [$b['total'], $a['name']] <=> [$a['total'], $b['name']]);
$prev = null;
$rankNo = 0;
foreach ($rows as $i => &$r) {
    if ($prev === null || $r['total'] < $prev) { $rankNo = $i + 1; }
    $r['rank'] = $rankNo;
    $prev = $r['total'];
}
unset($r);
$perfect = count(array_filter($rows, fn($r) => $fullMarks > 0 && $r['total'] >= $fullMarks));
$top = $rows ? $rows[0]['total'] : 0;

function quiz_fmt($v): string {
    return rtrim(rtrim(number_format((float)$v, 1, '.', ''), '0'), '.');
}

require __DIR__ . '/includes/header.php';
?>

<div class="page-hero">
  <h1>Quiz Results</h1>
  <p>Consolidated 2026 &middot; <?= QUIZ_MONTHS[0] ?> to <?= QUIZ_MONTHS[$months - 1] ?></p>
</div>

<section>
  <div class="container quiz-wrap">
    <div class="quiz-stats reveal">
      <div><strong><?= count($rows) ?></strong><span>Participants</span></div>
      <div><strong><?= $months ?></strong><span>Months</span></div>
      <div><strong><?= quiz_fmt($top) ?></strong><span>Highest total<?= $fullMarks > 0 ? ' / ' . quiz_fmt($fullMarks) : '' ?></span></div>
      <div><strong><?= $perfect ?></strong><span>Full marks</span></div>
    </div>

    <div class="quiz-tools reveal">
      <input type="search" id="quizSearch" placeholder="Search your name&hellip;" aria-label="Search by name" autocomplete="off">
      <div class="quiz-sort" role="group" aria-label="Sort">
        <button type="button" data-sort="rank" class="active">By rank</button>
        <button type="button" data-sort="name">A &ndash; Z</button>
      </div>
    </div>

    <div class="quiz-table-wrap">
      <table class="quiz-table" id="quizTable">
        <thead>
          <tr>
            <th class="q-rank">Rank</th>
            <th class="q-name">Name</th>
            <?php for ($m = 0; $m < $months; $m++): ?><th class="q-mark"><?= QUIZ_MONTHS[$m] ?></th><?php endfor; ?>
            <th class="q-total">Total</th>
          </tr>
        </thead>
        <tbody>
          <?php foreach ($rows as $r): $isFull = $fullMarks > 0 && $r['total'] >= $fullMarks; ?>
          <tr data-rank="<?= (int)$r['rank'] ?>" data-name="<?= h(mb_strtolower($r['name'])) ?>">
            <td class="q-rank"><span class="rank-pill<?= $r['rank'] <= 3 ? ' rank-top' : '' ?>"><?= (int)$r['rank'] ?></span></td>
            <td class="q-name"><?= h($r['name']) ?><?= $isFull ? ' <span class="full-badge" title="Full marks">&#9733;</span>' : '' ?></td>
            <?php for ($m = 0; $m < $months; $m++): $v = $r['marks'][$m] ?? null; ?>
              <?php if ($v === null): ?><td class="q-mark absent" title="Absent">A</td>
              <?php else: ?><td class="q-mark<?= $maxMark > 0 && $v >= $maxMark ? ' full' : '' ?>"><?= quiz_fmt($v) ?></td><?php endif; ?>
            <?php endfor; ?>
            <td class="q-total"><?= quiz_fmt($r['total']) ?></td>
          </tr>
          <?php endforeach; ?>
        </tbody>
      </table>
      <p class="quiz-empty" id="quizEmpty" hidden>No name matches your search.</p>
    </div>
    <p class="quiz-note">A = absent for that month. &#9733; = full marks in every month so far.</p>
  </div>
</section>

<script>
(function () {
  var tbody = document.querySelector('#quizTable tbody');
  var rows = Array.prototype.slice.call(tbody.querySelectorAll('tr'));
  var search = document.getElementById('quizSearch');
  var empty = document.getElementById('quizEmpty');
  function sortBy(mode) {
    rows.sort(function (a, b) {
      if (mode === 'name') return a.dataset.name.localeCompare(b.dataset.name);
      return (a.dataset.rank - b.dataset.rank) || a.dataset.name.localeCompare(b.dataset.name);
    });
    rows.forEach(function (r) { tbody.appendChild(r); });
  }
  function filter() {
    var q = search.value.trim().toLowerCase(), shown = 0;
    rows.forEach(function (r) {
      var ok = !q || r.dataset.name.indexOf(q) !== -1;
      r.style.display = ok ? '' : 'none';
      if (ok) shown++;
    });
    empty.hidden = shown !== 0;
  }
  search.addEventListener('input', filter);
  document.querySelectorAll('.quiz-sort button').forEach(function (b) {
    b.addEventListener('click', function () {
      document.querySelectorAll('.quiz-sort button').forEach(function (x) { x.classList.toggle('active', x === b); });
      sortBy(b.dataset.sort);
    });
  });
})();
</script>

<?php require __DIR__ . '/includes/footer.php'; ?>
