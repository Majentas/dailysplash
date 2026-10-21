<?php

declare(strict_types=1);

/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */

namespace OCA\DailySplash\AppInfo;

use OCA\DailySplash\EventListener\BeforeTemplateRenderedEventListener;
use OCP\AppFramework\App;
use OCP\AppFramework\Bootstrap\IBootContext;
use OCP\AppFramework\Bootstrap\IBootstrap;
use OCP\AppFramework\Bootstrap\IRegistrationContext;
use OCP\AppFramework\Http\Events\BeforeLoginTemplateRenderedEvent;
use OCP\AppFramework\Http\Events\BeforeTemplateRenderedEvent;

/**
 * Class Application
 *
 * @package OCA\DailySplash\AppInfo
 */
class Application extends App implements IBootstrap
{
    public const APP_ID = 'dailysplash';

    /**
     * Application constructor.
     *
     * @param array $urlParams
     */
    public function __construct(array $urlParams = [])
    {
        parent::__construct(self::APP_ID, $urlParams);
    }

    public function register(IRegistrationContext $context): void
    {
        $context->registerEventListener(BeforeTemplateRenderedEvent::class, BeforeTemplateRenderedEventListener::class);
        $context->registerEventListener(BeforeLoginTemplateRenderedEvent::class, BeforeTemplateRenderedEventListener::class);
    }

    public function boot(IBootContext $context): void
    {
    }
}
