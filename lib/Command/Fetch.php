<?php

declare(strict_types=1);

/**
 * This file is part of the DailySplash App
 * and licensed under the AGPL-3.0-or-later (see LICENSE.md and NOTICE).
 */

namespace OCA\DailySplash\Command;

use OCA\DailySplash\Services\ImageCacheService;
use OCA\DailySplash\Services\SettingsService;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * occ dailysplash:fetch [--force]
 */
class Fetch extends Command
{
    public function __construct(
        private ImageCacheService $imageCache,
        private SettingsService $settings,
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this->setName('dailysplash:fetch')
            ->setDescription('Download today\'s background image for the selected provider')
            ->addOption('force', 'f', InputOption::VALUE_NONE, 'Download again even if today\'s image is already cached');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $provider = $this->settings->getSelectedImageProvider();
        $status = $this->imageCache->refresh((bool)$input->getOption('force'));
        $output->writeln('Provider: ' . $provider->getName());
        $output->writeln('Status: ' . $status);

        $current = $this->imageCache->getCurrent();
        if ($current !== null) {
            $output->writeln('Image date: ' . $current['metadata']->getImageDate() . ', version ' . $current['version'] . ', ' . (string)$current['file']->getSize() . ' bytes');
        }
        if ($status === ImageCacheService::STATUS_FAILED) {
            $output->writeln('<error>' . $this->settings->getLastFetchError() . '</error>');
            return 1;
        }
        return 0;
    }
}
