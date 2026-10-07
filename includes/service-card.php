<?php
function render_service_card_body(): void {
    $early = trim(setting('service_early'));
    $spName = trim(setting('special_name'));
    $spDate = trim(setting('special_date'));
    $spTime = trim(setting('special_time'));
    $showSpecial = setting('special_enabled') === '1' && $spName !== '' && !celebration_is_expired($spDate ?: null);
    ?>
    <span class="service-flash-ribbon">This Sunday</span>
    <div class="service-flash-date"><?= h(format_ordinal_date(setting('service_date'))) ?></div>
    <div class="service-flash-times<?= $early !== '' ? ' three' : '' ?>">
      <?php if ($early !== ''): ?>
      <div class="service-time-badge">
        <span class="service-time-icon">&#127749;</span>
        <span class="service-time-label">Early Morning Service</span>
        <span class="service-time-value"><?= h($early) ?></span>
      </div>
      <?php endif; ?>
      <div class="service-time-badge">
        <span class="service-time-icon">&#9728;&#65039;</span>
        <span class="service-time-label">Morning Service</span>
        <span class="service-time-value"><?= h(setting('service_morning')) ?></span>
      </div>
      <div class="service-time-badge">
        <span class="service-time-icon">&#127769;</span>
        <span class="service-time-label">Evening Service</span>
        <span class="service-time-value"><?= h(setting('service_evening')) ?></span>
      </div>
    </div>
    <?php if ($showSpecial): ?>
    <div class="service-special">
      <span class="service-special-tag">&#10024; Special Service</span>
      <div class="service-special-name"><?= h($spName) ?></div>
      <div class="service-special-when">
        <?php if ($spDate !== ''): ?><span>&#128197; <?= h(format_ordinal_date($spDate)) ?></span><?php endif; ?>
        <?php if ($spTime !== ''): ?><span>&#128340; <?= h($spTime) ?></span><?php endif; ?>
      </div>
    </div>
    <?php endif; ?>
    <?php
}
