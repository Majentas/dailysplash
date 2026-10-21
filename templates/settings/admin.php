<?php
/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */

\OCP\Util::addScript('dailysplash', 'settings');
\OCP\Util::addStyle('dailysplash', 'settings');

$sourceLinks = [
    'bing' => ['Bing Wallpaper', 'https://bingwallpaper.microsoft.com/'],
    'wikimedia_potd' => ['Wikimedia Commons', 'https://commons.wikimedia.org/wiki/Commons:Picture_of_the_day'],
];
?>
<div class="section" id="dailysplash-settings" data-save="<?php p($_['saveSettingsUrl']); ?>">
    <h2>
        <?php p($l->t('DailySplash: Daily Background Images')); ?>
        <span class="msg success"><?php p($l->t('Saved')); ?></span>
        <span class="msg error"><?php p($l->t('Failed')); ?></span>
    </h2>
    <p class="settings-hint">
        <?php p($l->t('Show a new picture of the day as background. Images are downloaded once a day by the Nextcloud background job and served by this server, so visitors never contact the image provider.')); ?>
    </p>
    <form>
        <div class="dailysplash-checkboxes">
            <input id="dailysplash-style-login" name="dailysplash-style-login" data-setting="style/login"
                   type="checkbox" <?php if ($_['styleLogin']) { p('checked'); } ?>>
            <label for="dailysplash-style-login"><?php p($l->t('Set daily image as login background')); ?></label>
        </div>
        <div class="dailysplash-checkboxes">
            <input id="dailysplash-style-dashboard" name="dailysplash-style-dashboard" data-setting="style/dashboard"
                   type="checkbox" <?php if ($_['styleDashboard']) { p('checked'); } ?>>
            <label for="dailysplash-style-dashboard"><?php p($l->t('Set daily image as dashboard background (for users who kept the default background)')); ?></label>
        </div>
        <div class="dailysplash-providerselect">
            <label class="dailysplash-label" for="dailysplash-provider-selection"><?php p($l->t('Set the image provider:')); ?></label>
            <select class="dailysplash-input" id="dailysplash-provider-selection" data-setting="provider/provider">
                <?php foreach ($_['availableProviders'] as $id => $name): ?>
                    <option value="<?php p($id); ?>" <?php if ($id === $_['selectedProvider']) { p('selected'); } ?>><?php p($l->t($name)); ?></option>
                <?php endforeach; ?>
            </select>
            <p class="settings-hint">
                <?php foreach ($sourceLinks as $id => [$label, $href]): ?>
                    <span class="dailysplash-source" data-provider="<?php p($id); ?>" <?php if ($id !== $_['selectedProvider']) { p('hidden'); } ?>>
                        <?php p($l->t('These images are provided by %s. Each image has its own license.', [$label])); ?>
                        <a class="dailysplash-documentation-link" href="<?php p($href); ?>" target="_blank" rel="noreferrer noopener"><?php p($href); ?></a>
                    </span>
                <?php endforeach; ?>
            </p>
        </div>

        <div id="dailysplash-cache" <?php if (!$_['isCached']) { p('hidden'); } ?>>
            <p><?php p($l->t('Preview:')); ?></p>
            <img src="<?php p($_['imageURL']); ?>" id="dailysplash-preview" class="dailysplash-preview" alt=""
                <?php if ($_['imageURL'] === '') { p('hidden'); } ?>>
            <p class="dailysplash-status">
                <?php p($l->t('Image date:')); ?> <span id="dailysplash-image-date"><?php p($_['imageDate'] !== '' ? $_['imageDate'] : '—'); ?></span>
                <?php if ($_['lastAttempt'] !== ''): ?>
                    · <?php p($l->t('Last check:')); ?> <?php p($_['lastAttempt']); ?>
                <?php endif; ?>
            </p>
            <p class="dailysplash-error" id="dailysplash-last-error" <?php if ($_['lastError'] === '') { p('hidden'); } ?>>
                <?php p($l->t('Last download failed, the previous image is kept:')); ?> <span><?php p($_['lastError']); ?></span>
            </p>
            <input type="button" id="dailysplash-refresh" data-action="refresh" value="<?php p($l->t('Download today\'s image now')); ?>">
        </div>

        <h2><?php p($l->t('DailySplash: Advanced Theming')); ?></h2>
        <p class="settings-hint">
            <?php p($l->t('You can apply effects to your background images.')); ?>
        </p>
        <div class="dailysplash-checkboxes">
            <input id="dailysplash-style-tinting" name="dailysplash-style-tinting" data-setting="style/tint"
                   type="checkbox" <?php if ($_['styleTint']) { p('checked'); } ?>>
            <label for="dailysplash-style-tinting"><?php p($l->t('Enable Tint')); ?></label>
        </div>
        <div class="dailysplash-slider">
            <label for="dailysplash-style-color-strength"><?php p($l->t('Set the vibrancy of the color')); ?></label>
            <input id="dailysplash-style-color-strength" type="range" name="dailysplash-style-color-strength"
                   data-setting="style/strength/color" min="0" max="100"
                   value="<?php p((string)$_['styleStrengthColor']); ?>" <?php if (!$_['styleTint']) { p('disabled'); } ?>>
        </div>
        <div class="dailysplash-slider">
            <label for="dailysplash-style-blur"><?php p($l->t('Set the blur of the image')); ?></label>
            <input id="dailysplash-style-blur" type="range" name="dailysplash-style-blur"
                   data-setting="style/strength/blur" min="0" max="25"
                   value="<?php p((string)$_['styleStrengthBlur']); ?>">
        </div>

        <h2><?php p($l->t('DailySplash: High Visibility')); ?></h2>
        <p class="settings-hint">
            <?php p($l->t('You can enable a High Visibility Mode for Legal Reasons. This will highlight the Privacy and Data Protection links on the login screen.')); ?>
        </p>
        <div class="dailysplash-checkboxes">
            <input id="dailysplash-style-highvisibility" name="dailysplash-style-highvisibility"
                   data-setting="style/login/highvisibility"
                   type="checkbox" <?php if ($_['styleHighVisibility']) { p('checked'); } ?>>
            <label for="dailysplash-style-highvisibility"><?php p($l->t('Enable High-Visibility Mode')); ?></label>
        </div>
    </form>
</div>
