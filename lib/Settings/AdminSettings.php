<?php

declare(strict_types=1);

/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */

namespace OCA\DailySplash\Settings;

use OCA\DailySplash\AppInfo\Application;
use OCA\DailySplash\Services\ImageCacheService;
use OCA\DailySplash\Services\SettingsService;
use OCP\AppFramework\Http\TemplateResponse;
use OCP\IURLGenerator;
use OCP\Settings\ISettings;

/**
 * Class AdminSettings
 *
 * @package OCA\DailySplash\Settings
 */
class AdminSettings implements ISettings
{
    public function __construct(
        private IURLGenerator $urlGenerator,
        private SettingsService $settings,
        private ImageCacheService $imageCache,
    )
    {
    }

    /**
     * @return TemplateResponse returns the instance with all parameters set, ready to be rendered
     */
    public function getForm(): TemplateResponse
    {
        $providers = [];
        foreach ($this->settings->getAllImageProviders() as $id => $provider) {
            $providers[$id] = $provider->getName();
        }
        $current = $this->imageCache->getCurrent();
        $lastAttempt = $this->settings->getLastFetchAttempt();

        return new TemplateResponse(Application::APP_ID, 'settings/admin', [
            'saveSettingsUrl' => $this->urlGenerator->linkToRoute('dailysplash.admin_settings.set'),
            'styleLogin' => $this->settings->getServerStyleLoginEnabled(),
            'styleDashboard' => $this->settings->getServerStyleDashboardEnabled(),
            'availableProviders' => $providers,
            'selectedProvider' => $this->settings->getImageProviderId(),
            'isCached' => $this->settings->getSelectedImageProvider()->isCached(),
            'imageURL' => $current === null ? '' : $this->urlGenerator->linkToRoute('dailysplash.image.get', ['v' => $current['version']]),
            'imageDate' => $current === null ? '' : $current['metadata']->getImageDate(),
            'lastAttempt' => $lastAttempt > 0 ? gmdate('Y-m-d H:i', $lastAttempt) . ' UTC' : '',
            'lastError' => $this->settings->getLastFetchError(),
            'styleTint' => $this->settings->isTintEnabled(),
            'styleStrengthColor' => $this->settings->getColorStrength(),
            'styleStrengthBlur' => $this->settings->getBlurStrength(),
            'styleHighVisibility' => $this->settings->isHighVisibilityLogin(),
        ]);
    }

    /**
     * @return string the section ID, e.g. 'sharing'
     */
    public function getSection(): string
    {
        return 'theming';
    }

    /**
     * @return int whether the form should be rather on the top or bottom of
     * the admin section. The forms are arranged in ascending order of the
     * priority values. It is required to return a value between 0 and 100.
     */
    public function getPriority(): int
    {
        return 9;
    }
}
