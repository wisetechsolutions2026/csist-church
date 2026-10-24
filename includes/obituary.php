<?php
function obit_ensure_table(): void {
    db()->query("CREATE TABLE IF NOT EXISTS obituaries (
      id INT AUTO_INCREMENT PRIMARY KEY,
      name VARCHAR(150) NOT NULL,
      age VARCHAR(10) NOT NULL DEFAULT '',
      photo VARCHAR(255) DEFAULT NULL,
      passed_on DATE DEFAULT NULL,
      address VARCHAR(300) NOT NULL DEFAULT '',
      phone VARCHAR(60) NOT NULL DEFAULT '',
      service_title VARCHAR(120) NOT NULL DEFAULT 'Funeral Service',
      service_place VARCHAR(250) NOT NULL DEFAULT '',
      service_date DATE DEFAULT NULL,
      service_time VARCHAR(10) NOT NULL DEFAULT '',
      note VARCHAR(300) NOT NULL DEFAULT '',
      is_active TINYINT(1) NOT NULL DEFAULT 1,
      created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
}

function obit_time_12h(string $hhmm): string {
    if (!preg_match('/^(\d{1,2}):(\d{2})/', $hhmm, $m)) {
        return $hhmm;
    }
    $h = (int)$m[1];
    return (($h % 12) ?: 12) . ':' . $m[2] . ' ' . ($h >= 12 ? 'PM' : 'AM');
}

/** Notices shown on the home page: active, and the service day has not passed by more than a day. */
function obit_load_current(): array {
    obit_ensure_table();
    $limit = (new DateTime(today_ist(), new DateTimeZone('Asia/Kolkata')))->modify('-1 day')->format('Y-m-d');
    $stmt = db()->prepare('SELECT * FROM obituaries WHERE is_active = 1 AND (service_date IS NULL OR service_date >= ?) ORDER BY service_date IS NULL, service_date, id DESC');
    $stmt->bind_param('s', $limit);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function obit_whatsapp_link(array $o): string {
    $lines = ['Obituary - ' . $o['name'] . ($o['age'] !== '' ? ' (' . $o['age'] . ' yrs)' : '')];
    if ($o['service_date']) {
        $when = format_ordinal_date($o['service_date']) . ($o['service_time'] !== '' ? ', ' . obit_time_12h($o['service_time']) : '');
        $lines[] = ($o['service_title'] ?: 'Funeral Service') . ': ' . $when . ($o['service_place'] !== '' ? ' at ' . $o['service_place'] : '');
    }
    if ($o['address'] !== '') { $lines[] = 'Address: ' . $o['address']; }
    if ($o['phone'] !== '') { $lines[] = 'Contact: ' . $o['phone']; }
    $lines[] = "CSI St. Matthew's Church, Porur";
    return 'https://wa.me/?text=' . rawurlencode(implode("\n", $lines));
}

function render_obit_card(array $o, string $base, bool $archive = false): void {
    $title = $o['service_title'] !== '' ? $o['service_title'] : 'Funeral Service';
    ?>
    <article class="obit-card reveal<?= $archive ? ' obit-archive' : '' ?>">
      <div class="obit-photo">
        <?php if (!empty($o['photo'])): ?>
          <img src="<?= $base ?>assets/img/obituaries/<?= h($o['photo']) ?>" alt="<?= h($o['name']) ?>" loading="lazy">
        <?php else: ?>
          <span class="obit-candle">&#128367;&#65039;</span>
        <?php endif; ?>
      </div>
      <div class="obit-body">
        <span class="obit-label"><?= $archive ? 'In loving memory' : 'Obituary notice' ?></span>
        <h3 class="obit-name"><?= h($o['name']) ?></h3>
        <?php if ($o['age'] !== '' || $o['passed_on']): ?>
        <p class="obit-meta">
          <?= $o['age'] !== '' ? 'Aged ' . h($o['age']) : '' ?><?= ($o['age'] !== '' && $o['passed_on']) ? ' &middot; ' : '' ?><?= $o['passed_on'] ? 'Called to eternal rest on ' . h(format_ordinal_date($o['passed_on'])) : '' ?>
        </p>
        <?php endif; ?>
        <?php if ($o['service_date'] || $o['service_place'] !== ''): ?>
        <div class="obit-service">
          <strong><?= h($title) ?></strong>
          <?php if ($o['service_date']): ?>
            <span>&#128197; <?= h(format_ordinal_date($o['service_date'])) ?><?= $o['service_time'] !== '' ? ' &middot; &#128340; ' . h(obit_time_12h($o['service_time'])) : '' ?></span>
          <?php endif; ?>
          <?php if ($o['service_place'] !== ''): ?><span>&#128205; <?= h($o['service_place']) ?></span><?php endif; ?>
        </div>
        <?php endif; ?>
        <?php if ($o['address'] !== ''): ?><p class="obit-line"><em>Address</em><?= h($o['address']) ?></p><?php endif; ?>
        <?php if ($o['phone'] !== ''): ?><p class="obit-line"><em>Contact</em><a href="tel:<?= h(preg_replace('/[^0-9+]/', '', $o['phone'])) ?>"><?= h($o['phone']) ?></a></p><?php endif; ?>
        <?php if ($o['note'] !== ''): ?><p class="obit-note">&ldquo;<?= h($o['note']) ?>&rdquo;</p><?php endif; ?>
        <a class="obit-share" href="<?= h(obit_whatsapp_link($o)) ?>" target="_blank" rel="noopener">Share on WhatsApp</a>
      </div>
    </article>
    <?php
}
