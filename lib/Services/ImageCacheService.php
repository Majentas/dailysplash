<?php

declare(strict_types=1);

/**
 * This file is part of the DailySplash App
 * and licensed under the AGPL-3.0-or-later (see LICENSE.md and NOTICE).
 */

namespace OCA\DailySplash\Services;

use OCA\DailySplash\ProviderHandler\Provider;
use OCA\DailySplash\ProviderHandler\ProviderMetadata;
use OCP\AppFramework\Utility\ITimeFactory;
use OCP\Files\IAppData;
use OCP\Files\NotFoundException;
use OCP\Files\SimpleFS\ISimpleFile;
use OCP\Files\SimpleFS\ISimpleFolder;
use OCP\Http\Client\IClientService;
use Psr\Log\LoggerInterface;

/**
 * Downloads today's image of the selected provider, validates it and stores it
 * in app data (appdata_<instanceid>/dailysplash/<provider id>/).
 *
 * The previous image is only replaced once a new one has been downloaded and
 * validated, so a provider outage keeps yesterday's image on the login page.
 */
class ImageCacheService
{
    public const STATUS_UPDATED = 'updated';
    public const STATUS_FRESH = 'fresh';
    public const STATUS_UNCHANGED = 'unchanged';
    public const STATUS_FAILED = 'failed';
    public const STATUS_DISABLED = 'disabled';

    public const MAX_IMAGE_BYTES = 15 * 1024 * 1024;
    public const DOWNLOAD_TIMEOUT = 20;
    private const MIN_WIDTH = 640;
    private const MIN_HEIGHT = 360;
    private const ALLOWED_TYPES = [
        IMAGETYPE_JPEG => 'image/jpeg',
        IMAGETYPE_PNG => 'image/png',
        IMAGETYPE_WEBP => 'image/webp',
    ];
    private const META_FILE = 'meta.json';

    public function __construct(
        private IAppData $appData,
        private IClientService $clientService,
        private SettingsService $settings,
        private ITimeFactory $timeFactory,
        private LoggerInterface $logger,
    )
    {
    }

    /**
     * Make sure today's image of the selected provider is cached.
     *
     * @param bool $force download even if the cached image is already today's
     * @return string one of the STATUS_* constants
     */
    public function refresh(bool $force = false): string
    {
        $provider = $this->settings->getSelectedImageProvider();
        if (!$provider->isCached()) {
            return self::STATUS_DISABLED;
        }

        $today = $this->timeFactory->now()->setTimezone(new \DateTimeZone('UTC'));
        $state = $this->readState($provider);
        if (!$force && $state !== null && $state['metadata']->getImageDate() === $today->format('Y-m-d')) {
            return self::STATUS_FRESH;
        }

        $metadata = $provider->resolveDailyImage($today);
        if ($metadata === null) {
            return $this->fail('could not get today\'s image from ' . $provider->getName());
        }

        if (!$force && $state !== null && $state['metadata']->getImageUrl() === $metadata->getImageUrl()) {
            // provider has not published a new image yet (e.g. Bing changes at its own local morning),
            // or it re-dated the same image: remember the new date so we stop asking today
            if ($state['metadata']->getImageDate() !== $metadata->getImageDate()) {
                try {
                    $this->writeMeta($this->getFolder($provider), $state['file'], $state['mime'], $state['version'], $metadata);
                } catch (\Exception $e) {
                    return $this->fail('could not update metadata: ' . $e->getMessage());
                }
            }
            $this->settings->recordFetchAttempt($this->timeFactory->getTime(), '');
            return self::STATUS_UNCHANGED;
        }

        $error = $this->checkImageUrl($provider, $metadata->getImageUrl());
        if ($error !== null) {
            return $this->fail($error);
        }

        $data = $this->download($metadata->getImageUrl(), $error);
        if ($data === null) {
            return $this->fail((string)$error);
        }

        $mime = $this->validateImage($data, $error);
        if ($mime === null) {
            return $this->fail((string)$error);
        }

        try {
            $this->store($provider, $metadata, $data, $mime, $state);
        } catch (\Exception $e) {
            return $this->fail('could not store image: ' . $e->getMessage());
        }

        $this->settings->recordFetchAttempt($this->timeFactory->getTime(), '');
        $this->logger->info('DailySplash: cached new image from ' . $provider->getName() . ' for ' . $metadata->getImageDate(), ['app' => 'dailysplash']);
        return self::STATUS_UPDATED;
    }

    /**
     * Version (content hash) of the currently served image, '' if there is none.
     */
    public function getCurrentVersion(): string
    {
        $provider = $this->settings->getSelectedImageProvider();
        if (!$provider->isCached()) {
            return '';
        }
        return $this->settings->getImageVersion($provider->getId());
    }

    /**
     * The currently served image and its description.
     *
     * @return array{file: ISimpleFile, mime: string, version: string, metadata: ProviderMetadata}|null
     */
    public function getCurrent(): ?array
    {
        $provider = $this->settings->getSelectedImageProvider();
        if (!$provider->isCached()) {
            return null;
        }
        $state = $this->readState($provider);
        if ($state === null) {
            return null;
        }
        try {
            $file = $this->getFolder($provider)->getFile($state['file']);
        } catch (NotFoundException) {
            return null;
        }
        return [
            'file' => $file,
            'mime' => $state['mime'],
            'version' => $state['version'],
            'metadata' => $state['metadata'],
        ];
    }

    /**
     * @return array{file: string, mime: string, version: string, fetchedAt: int, metadata: ProviderMetadata}|null
     */
    private function readState(Provider $provider): ?array
    {
        try {
            $json = json_decode($this->getFolder($provider)->getFile(self::META_FILE)->getContent(), true);
        } catch (\Exception) {
            return null;
        }
        if (!is_array($json) || !is_string($json['file'] ?? null) || !is_string($json['version'] ?? null)
            || !in_array($json['mime'] ?? null, self::ALLOWED_TYPES, true) || !is_array($json['metadata'] ?? null)) {
            return null;
        }
        return [
            'file' => $json['file'],
            'mime' => $json['mime'],
            'version' => $json['version'],
            'fetchedAt' => (int)($json['fetchedAt'] ?? 0),
            'metadata' => ProviderMetadata::fromArray($json['metadata']),
        ];
    }

    private function store(Provider $provider, ProviderMetadata $metadata, string $data, string $mime, ?array $previous): void
    {
        $folder = $this->getFolder($provider);
        $version = substr(hash('sha256', $data), 0, 16);
        $fileName = 'image-' . $version;

        // 1. new image, 2. metadata pointing to it, 3. version used by the CSS, 4. drop the old image
        $folder->newFile($fileName, $data);
        $this->writeMeta($folder, $fileName, $mime, $version, $metadata);
        $this->settings->setImageVersion($provider->getId(), $version);

        if ($previous !== null && $previous['file'] !== $fileName) {
            try {
                $folder->getFile($previous['file'])->delete();
            } catch (\Exception) {
                // a leftover old image is harmless
            }
        }
    }

    private function writeMeta(ISimpleFolder $folder, string $fileName, string $mime, string $version, ProviderMetadata $metadata): void
    {
        $folder->newFile(self::META_FILE, json_encode([
            'file' => $fileName,
            'mime' => $mime,
            'version' => $version,
            'fetchedAt' => $this->timeFactory->getTime(),
            'metadata' => $metadata->toArray(),
        ], JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
    }

    private function checkImageUrl(Provider $provider, string $url): ?string
    {
        $parts = parse_url($url);
        if (($parts['scheme'] ?? '') !== 'https' || !in_array($parts['host'] ?? '', $provider->getAllowedImageHosts(), true)
            || isset($parts['user']) || isset($parts['port'])) {
            return 'refusing image URL outside the provider\'s allowed hosts: ' . $url;
        }
        return null;
    }

    /**
     * Download at most MAX_IMAGE_BYTES, without following redirects.
     */
    private function download(string $url, ?string &$error): ?string
    {
        try {
            $response = $this->clientService->newClient()->get($url, [
                'timeout' => self::DOWNLOAD_TIMEOUT,
                'connect_timeout' => 5,
                'allow_redirects' => false,
                'stream' => true,
                'headers' => [
                    'User-Agent' => Provider::USER_AGENT,
                    'Accept' => implode(',', self::ALLOWED_TYPES),
                ],
            ]);
        } catch (\Exception $e) {
            $error = 'image download failed: ' . $e->getMessage();
            return null;
        }

        if ($response->getStatusCode() !== 200) {
            $error = 'image download returned HTTP ' . $response->getStatusCode();
            return null;
        }
        $length = $response->getHeader('Content-Length');
        if ($length !== '' && (int)$length > self::MAX_IMAGE_BYTES) {
            $error = 'image too large (' . $length . ' bytes)';
            return null;
        }

        $body = $response->getBody();
        if (is_string($body)) {
            $data = $body;
        } elseif (!is_resource($body)) {
            $error = 'image download returned no body';
            return null;
        } else {
            $data = '';
            while (!feof($body) && strlen($data) <= self::MAX_IMAGE_BYTES) {
                $chunk = fread($body, 65536);
                if ($chunk === false) {
                    break;
                }
                $data .= $chunk;
            }
            fclose($body);
        }

        if (strlen($data) > self::MAX_IMAGE_BYTES) {
            $error = 'image larger than ' . self::MAX_IMAGE_BYTES . ' bytes';
            return null;
        }
        return $data;
    }

    /**
     * Check the bytes really are a reasonably sized JPEG, PNG or WebP image.
     *
     * @return string|null MIME type, or null if invalid
     */
    private function validateImage(string $data, ?string &$error): ?string
    {
        $info = @getimagesizefromstring($data);
        if (!is_array($info)) {
            $error = 'downloaded file is not an image';
            return null;
        }
        [$width, $height, $type] = $info;
        if (!isset(self::ALLOWED_TYPES[$type])) {
            $error = 'downloaded file is not a JPEG, PNG or WebP image';
            return null;
        }
        if ($width < self::MIN_WIDTH || $height < self::MIN_HEIGHT) {
            $error = 'downloaded image is too small (' . $width . 'x' . $height . ')';
            return null;
        }
        return self::ALLOWED_TYPES[$type];
    }

    private function fail(string $error): string
    {
        // Warning, not error: provider outages are expected and the old image stays in place
        $this->logger->warning('DailySplash: ' . $error . ' (keeping the previous image)', ['app' => 'dailysplash']);
        $this->settings->recordFetchAttempt($this->timeFactory->getTime(), $error);
        return self::STATUS_FAILED;
    }

    private function getFolder(Provider $provider): ISimpleFolder
    {
        try {
            return $this->appData->getFolder($provider->getId());
        } catch (NotFoundException) {
            return $this->appData->newFolder($provider->getId());
        }
    }
}
