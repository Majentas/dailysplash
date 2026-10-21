<?php

declare(strict_types=1);

/**
 * This file is part of the Unpslash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */

namespace OCA\DailySplash\EventListener;

use OCA\DailySplash\AppInfo\Application;
use OCA\DailySplash\Services\ImageCacheService;
use OCA\DailySplash\Services\SettingsService;
use OCP\AppFramework\Http\Events\BeforeLoginTemplateRenderedEvent;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;
use OCP\Config\IUserConfig;
use OCP\EventDispatcher\Event;
use OCP\EventDispatcher\IEventListener;
use OCP\IRequest;
use OCP\IURLGenerator;
use OCP\IUserSession;
use OCP\Util;

/**
 * @template-implements IEventListener<BeforeTemplateRenderedEvent|BeforeLoginTemplateRenderedEvent>
 */
class BeforeTemplateRenderedEventListener implements IEventListener
{
    // compared lowercase: the login route is "core.login.showLoginForm"
    private const LOGIN_ROUTES = [
        'core.login.showloginform',
        'core.twofactorchallenge.showchallenge',
        'core.twofactorchallenge.selectchallenge',
        'files_sharing.share.authenticate',
        'files_sharing.share.showauthenticate',
    ];

    /** both events can fire for the same page */
    private bool $added = false;

    public function __construct(
        private SettingsService $settingsService,
        private ImageCacheService $imageCache,
        private IRequest $request,
        private IURLGenerator $urlGenerator,
        private IUserSession $userSession,
        private IUserConfig $userConfig,
    )
    {
    }

    public function handle(Event $event): void
    {
        if ($this->added || (!$event instanceof BeforeTemplateRenderedEvent && !$event instanceof BeforeLoginTemplateRenderedEvent)) {
            return;
        }

        $version = $this->imageCache->getCurrentVersion();
        if ($version === '') {
            // nothing cached (or "Nextcloud default" selected): keep the normal background
            return;
        }

        $route = strtolower((string)$this->request->getParam('_route', ''));
        $isLogin = $event instanceof BeforeLoginTemplateRenderedEvent || in_array($route, self::LOGIN_ROUTES, true);

        if ($isLogin) {
            if ($this->settingsService->getServerStyleLoginEnabled()) {
                $this->addHeaderFor('login', $version);
                $this->addMetadata();
            }
            return;
        }

        if ($event instanceof BeforeTemplateRenderedEvent && $event->isLoggedIn() && $this->isDashboardStyledForUser()) {
            $this->addHeaderFor('dashboard', $version);
            if ($route === 'dashboard.dashboard.index') {
                $this->addMetadata();
            }
        }
    }

    /**
     * Only when enabled by the admin, and only for users who kept the default theming background.
     */
    private function isDashboardStyledForUser(): bool
    {
        if (!$this->settingsService->getServerStyleDashboardEnabled()) {
            return false;
        }
        $user = $this->userSession->getUser();
        if ($user === null) {
            return false;
        }
        try {
            $background = $this->userConfig->getValueString($user->getUID(), 'theming', 'background_image', 'default');
        } catch (\Exception) {
            return false;
        }
        return $background === 'default';
    }

    /**
     * Link the dynamic (versioned) and the static css.
     */
    private function addHeaderFor(string $target, string $imageVersion): void
    {
        $this->added = true;
        $linkToCSS = $this->urlGenerator->linkToRoute(Application::APP_ID . '.css.' . $target, [
            'v' => $this->settingsService->getStyleVersion($imageVersion),
        ]);

        Util::addHeader('link', [
            'rel' => 'stylesheet',
            'href' => $linkToCSS,
        ]);

        Util::addStyle(Application::APP_ID, $target . '_static');
    }

    /**
     * Insert links to metadata scripts and styles
     */
    private function addMetadata(): void
    {
        Util::addScript(Application::APP_ID, 'metadata');
        Util::addStyle(Application::APP_ID, 'metadata');
    }
}
