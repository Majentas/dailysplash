<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2019 Felix Nüsse <felix.nuesse@t-online.de>
 * Modified for DailySplash in 2026 (see NOTICE).
 *
 * @license GNU AGPL version 3 or any later version
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU Affero General Public License as
 * published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU Affero General Public License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 */

namespace OCA\DailySplash\ProviderHandler;

use OCP\Http\Client\IClientService;
use Psr\Log\LoggerInterface;

/**
 * A provider only tells DailySplash which image is "today's" image.
 * Downloading, validating and caching is done by ImageCacheService, and
 * page requests never call a provider.
 */
abstract class Provider
{
    public const USER_AGENT = 'DailySplash/1.0 (Nextcloud app; server-side daily background fetch)';
    public const JSON_TIMEOUT = 10;
    public const JSON_MAX_BYTES = 1024 * 1024;

    public function __construct(
        protected IClientService $clientService,
        protected LoggerInterface $logger,
    )
    {
    }

    /**
     * Stable identifier, used in settings and as app data folder name.
     */
    abstract public function getId(): string;

    /**
     * Human readable name, shown in the admin settings and the attribution box.
     */
    abstract public function getName(): string;

    /**
     * Whether this provider delivers images that DailySplash caches and serves.
     * The "Nextcloud default" provider does not: it leaves the login page alone.
     */
    public function isCached(): bool
    {
        return true;
    }

    /**
     * Hosts that the image itself may be downloaded from (https only).
     *
     * @return string[]
     */
    abstract public function getAllowedImageHosts(): array;

    /**
     * Look up today's image (metadata only, no image download).
     *
     * @param \DateTimeImmutable $today current date in UTC
     * @return ProviderMetadata|null null if the provider could not be reached or returned garbage
     */
    abstract public function resolveDailyImage(\DateTimeImmutable $today): ?ProviderMetadata;

    /**
     * Fetch and decode a small JSON document from the provider API.
     */
    protected function getJson(string $url): ?array
    {
        try {
            $client = $this->clientService->newClient();
            $response = $client->get($url, [
                'timeout' => self::JSON_TIMEOUT,
                'connect_timeout' => 5,
                'allow_redirects' => false,
                'headers' => [
                    'User-Agent' => self::USER_AGENT,
                    'Accept' => 'application/json',
                ],
            ]);
            $body = (string)$response->getBody();
        } catch (\Exception $e) {
            $this->logger->warning('DailySplash: ' . $this->getName() . ' API request failed: ' . $e->getMessage(), ['app' => 'dailysplash']);
            return null;
        }

        if (strlen($body) > self::JSON_MAX_BYTES) {
            $this->logger->warning('DailySplash: ' . $this->getName() . ' API response too large', ['app' => 'dailysplash']);
            return null;
        }

        $json = json_decode($body, true);
        if (!is_array($json)) {
            $this->logger->warning('DailySplash: ' . $this->getName() . ' API returned invalid JSON', ['app' => 'dailysplash']);
            return null;
        }
        return $json;
    }

    /**
     * Strip HTML (Wikimedia metadata is HTML) and collapse whitespace.
     */
    protected static function plainText(?string $html, int $maxLength = 300): string
    {
        $text = html_entity_decode(strip_tags((string)$html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string)preg_replace('/\s+/u', ' ', $text));
        if (mb_strlen($text) > $maxLength) {
            $text = mb_substr($text, 0, $maxLength - 1) . '…';
        }
        return $text;
    }
}
