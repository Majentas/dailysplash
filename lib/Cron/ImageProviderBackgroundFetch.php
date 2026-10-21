<?php

declare(strict_types=1);

/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */

namespace OCA\DailySplash\Cron;

use OCA\DailySplash\Services\ImageCacheService;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\BackgroundJob\TimedJob;

/**
 * Runs hourly but only downloads when the cached image is not today's yet,
 * so a failed fetch is retried an hour later while the old image stays.
 */
class ImageProviderBackgroundFetch extends TimedJob
{
    public function __construct(
        ITimeFactory $time,
        private ImageCacheService $imageCache,
    )
    {
        parent::__construct($time);
        $this->setInterval(3600);
        $this->setAllowParallelRuns(false);
    }

    protected function run($argument): void
    {
        $this->imageCache->refresh();
    }
}
