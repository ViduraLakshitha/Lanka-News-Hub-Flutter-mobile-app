<?php
$val = fn(string $name, ?string $default = '') => (string)($edit[$name] ?? setting($name, (string)($default ?? '')));
?>
<form method="post" class="panel form-stack narrow left">
    <?= csrf() ?>
    <h2>Publication details</h2>
    <?php
    $field('site_name', 'Publication name', 'text', $val('site_name', 'Lanka News Hub'), 'required maxlength="100"');
    $field('contact_email', 'Public contact email', 'email', $val('contact_email'));
    ?>

    <h2>Language editions</h2>
    <p class="muted">Enable the editions available to readers. Article translations are entered separately by the newsroom.</p>
    <?php foreach (['en' => 'English', 'si' => 'සිංහල (Sinhala)', 'ta' => 'தமிழ் (Tamil)'] as $l => $label): ?>
        <label class="check">
            <input type="checkbox" name="language_<?= e($l) ?>" <?= $val('language_' . $l, '1') === '1' ? 'checked' : '' ?>>
            <?= e($label) ?>
        </label>
    <?php endforeach ?>

    <h2>Google AdSense configuration</h2>
    <p class="muted">Publisher account client ID for programmatic ad units. Leave blank to disable. Never hardcode live publisher IDs.</p>
    <?php
    $defaultAdsense = (string)(env('GOOGLE_ADSENSE_ID') ?: env('ADSENSE_PUBLISHER_ID') ?: '');
    $adsenseVal = $edit['adsense_id'] ?? (setting('adsense_id') !== '' ? setting('adsense_id') : $defaultAdsense);
    $field('adsense_id', 'Google AdSense publisher ID', 'text', $adsenseVal, 'placeholder="ca-pub-XXXXXXXXXXXXXXXX" maxlength="40"');
    ?>

    <h2>Optional analytics</h2>
    <p class="muted">Scripts load only after the reader accepts analytics. Leave IDs blank to disable. Avoid adding the same Analytics property directly and through Tag Manager.</p>
    <?php
    $field('ga_id', 'Google Analytics measurement ID', 'text', $val('ga_id'), 'placeholder="G-XXXXXXXXXX" maxlength="40"');
    $field('gtm_id', 'Google Tag Manager container ID', 'text', $val('gtm_id'), 'placeholder="GTM-XXXXXXX" maxlength="40"');
    ?>

    <button class="button">Save settings</button>
</form>
