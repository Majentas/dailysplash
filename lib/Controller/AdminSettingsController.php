<?php

declare(strict_types=1);

/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */

namespace OCA\DailySplash\Controller;

use OCA\DailySplash\Services\ImageCacheService;
use OCA\DailySplash\Services\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\JSONResponse;
use OCP\IRequest;
use OCP\IURLGenerator;

/**
 * Admin only (no NoAdminRequired attribute), CSRF protected.
 *
 * @package OCA\DailySplash\Controller
 */
class AdminSettingsController extends Controller
{
    public function __construct(
        string $appName,
        IRequest $request,
        private SettingsService $settings,
        private ImageCacheService $imageCache,
        private IURLGenerator $urlGenerator,
    )
    {
        parent::__construct($appName, $request);
    }

    /**
     * Update one setting
     *
     * @param string $key
     * @param mixed $value
     */
    public function set(string $key, mixed $value = null): JSONResponse
    {
        $bool = filter_var($value, FILTER_VALIDATE_BOOLEAN);
        $int = filter_var($value, FILTER_VALIDATE_INT, ['options' => ['default' => 0]]);

        switch ($key) {
            case 'style/login':
                $this->settings->setServerStyleLoginEnabled($bool);
                break;
            case 'style/dashboard':
                $this->settings->setServerStyleDashboardEnabled($bool);
                break;
            case 'style/tint':
                $this->settings->setTint($bool);
                break;
            case 'style/strength/color':
                $this->settings->setColorStrength($int);
                break;
            case 'style/strength/blur':
                $this->settings->setBlurStrength($int);
                break;
            case 'style/login/highvisibility':
                $this->settings->setHighVisibilityLogin($bool);
                break;
            case 'provider/provider':
                if (!is_string($value) || !$this->settings->setImageProvider($value)) {
                    return new JSONResponse(['status' => 'error', 'message' => 'Unknown provider'], Http::STATUS_BAD_REQUEST);
                }
                // admin-triggered: fetch now if this provider has no image yet
                if ($this->imageCache->getCurrentVersion() === '') {
                    $this->imageCache->refresh();
                }
                break;
            case 'refresh':
                $this->imageCache->refresh(true);
                break;
            default:
                return new JSONResponse(['status' => 'error', 'message' => 'Unknown setting'], Http::STATUS_BAD_REQUEST);
        }

        return new JSONResponse(['status' => 'ok'] + $this->getState());
    }

    /**
     * What the settings page shows about the cached image.
     */
    private function getState(): array
    {
        $provider = $this->settings->getSelectedImageProvider();
        $current = $this->imageCache->getCurrent();
        return [
            'provider' => $provider->getId(),
            'isCached' => $provider->isCached(),
            'imageURL' => $current === null ? '' : $this->urlGenerator->linkToRoute('dailysplash.image.get', ['v' => $current['version']]),
            'imageDate' => $current === null ? '' : $current['metadata']->getImageDate(),
            'lastError' => $this->settings->getLastFetchError(),
        ];
    }
}
