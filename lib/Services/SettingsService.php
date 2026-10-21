<?php

declare(strict_types=1);

/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */

namespace OCA\DailySplash\Services;

use OCA\DailySplash\AppInfo\Application;
use OCA\DailySplash\ProviderHandler\Provider;
use OCA\DailySplash\ProviderHandler\ProviderDefinitions;
use OCP\Defaults;
use OCP\IAppConfig;

class SettingsService
{
    public const STYLE_LOGIN = 'style_login';
    public const STYLE_DASHBOARD = 'style_dashboard';
    public const STYLE_LOGIN_HIGH_VISIBILITY = 'style_login_high_visibility';
    public const STYLE_TINT = 'style_tint';
    public const STYLE_STRENGTH_COLOR = 'style_strength_color';
    public const STYLE_STRENGTH_BLUR = 'style_strength_blur';
    public const PROVIDER_SELECTED = 'provider';
    public const IMAGE_VERSION_PREFIX = 'image_version_';
    public const LAST_FETCH_ATTEMPT = 'last_fetch_attempt';
    public const LAST_FETCH_ERROR = 'last_fetch_error';

    public const STYLE_STRENGTH_COLOR_DEFAULT = 30; // equals 30%

    public function __construct(
        private IAppConfig $appConfig,
        private ProviderDefinitions $providers,
        private Defaults $defaults,
    )
    {
    }

    public function getServerStyleLoginEnabled(): bool
    {
        return $this->appConfig->getValueBool(Application::APP_ID, self::STYLE_LOGIN, true);
    }

    public function setServerStyleLoginEnabled(bool $enabled): void
    {
        $this->appConfig->setValueBool(Application::APP_ID, self::STYLE_LOGIN, $enabled);
    }

    public function getServerStyleDashboardEnabled(): bool
    {
        return $this->appConfig->getValueBool(Application::APP_ID, self::STYLE_DASHBOARD, false);
    }

    public function setServerStyleDashboardEnabled(bool $enabled): void
    {
        $this->appConfig->setValueBool(Application::APP_ID, self::STYLE_DASHBOARD, $enabled);
    }

    public function getImageProviderId(): string
    {
        $id = $this->appConfig->getValueString(Application::APP_ID, self::PROVIDER_SELECTED, ProviderDefinitions::DEFAULT_PROVIDER);
        return $this->providers->exists($id) ? $id : ProviderDefinitions::DEFAULT_PROVIDER;
    }

    /**
     * Set the selected imageprovider; unknown ids are ignored.
     */
    public function setImageProvider(string $id): bool
    {
        if (!$this->providers->exists($id)) {
            return false;
        }
        $this->appConfig->setValueString(Application::APP_ID, self::PROVIDER_SELECTED, $id);
        return true;
    }

    public function getSelectedImageProvider(): Provider
    {
        return $this->providers->getProviderById($this->getImageProviderId());
    }

    /**
     * @return array<string, Provider>
     */
    public function getAllImageProviders(): array
    {
        return $this->providers->getAll();
    }

    public function isTintEnabled(): bool
    {
        return $this->appConfig->getValueBool(Application::APP_ID, self::STYLE_TINT, false);
    }

    public function setTint(bool $enabled): void
    {
        $this->appConfig->setValueBool(Application::APP_ID, self::STYLE_TINT, $enabled);
    }

    public function getColorStrength(): int
    {
        return $this->appConfig->getValueInt(Application::APP_ID, self::STYLE_STRENGTH_COLOR, self::STYLE_STRENGTH_COLOR_DEFAULT);
    }

    public function setColorStrength(int $strength): void
    {
        $this->appConfig->setValueInt(Application::APP_ID, self::STYLE_STRENGTH_COLOR, max(0, min(100, $strength)));
    }

    public function getBlurStrength(): int
    {
        return $this->appConfig->getValueInt(Application::APP_ID, self::STYLE_STRENGTH_BLUR, 0);
    }

    public function setBlurStrength(int $strength): void
    {
        $this->appConfig->setValueInt(Application::APP_ID, self::STYLE_STRENGTH_BLUR, max(0, min(25, $strength)));
    }

    /**
     * If the login page should be styled as High Visibility for Legal reasons
     */
    public function isHighVisibilityLogin(): bool
    {
        return $this->appConfig->getValueBool(Application::APP_ID, self::STYLE_LOGIN_HIGH_VISIBILITY, false);
    }

    public function setHighVisibilityLogin(bool $enabled): void
    {
        $this->appConfig->setValueBool(Application::APP_ID, self::STYLE_LOGIN_HIGH_VISIBILITY, $enabled);
    }

    /**
     * Nextcloud theming main color, as #rrggbb
     */
    public function getInstanceColor(): string
    {
        $color = $this->defaults->getColorPrimary();
        return preg_match('/^#[0-9a-f]{6}$/i', $color) ? $color : '#00679e';
    }

    /**
     * Version (content hash) of the cached image of a provider, '' if nothing is cached.
     * Not lazy: it is read on every page that may get the background.
     */
    public function getImageVersion(string $providerId): string
    {
        return $this->appConfig->getValueString(Application::APP_ID, self::IMAGE_VERSION_PREFIX . $providerId, '');
    }

    public function setImageVersion(string $providerId, string $version): void
    {
        $this->appConfig->setValueString(Application::APP_ID, self::IMAGE_VERSION_PREFIX . $providerId, $version);
    }

    public function recordFetchAttempt(int $timestamp, string $error): void
    {
        $this->appConfig->setValueInt(Application::APP_ID, self::LAST_FETCH_ATTEMPT, $timestamp, lazy: true);
        $this->appConfig->setValueString(Application::APP_ID, self::LAST_FETCH_ERROR, $error, lazy: true);
    }

    public function getLastFetchAttempt(): int
    {
        return $this->appConfig->getValueInt(Application::APP_ID, self::LAST_FETCH_ATTEMPT, 0, lazy: true);
    }

    public function getLastFetchError(): string
    {
        return $this->appConfig->getValueString(Application::APP_ID, self::LAST_FETCH_ERROR, '', lazy: true);
    }

    /**
     * Short hash of everything that changes the generated CSS, used as cache buster.
     */
    public function getStyleVersion(string $imageVersion): string
    {
        return substr(sha1(implode('|', [
            $imageVersion,
            (int)$this->isTintEnabled(),
            $this->getColorStrength(),
            $this->getBlurStrength(),
            (int)$this->isHighVisibilityLogin(),
            $this->getInstanceColor(),
        ])), 0, 12);
    }
}
