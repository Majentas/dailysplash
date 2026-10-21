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

namespace OCA\DailySplash\Provider;

use OCA\DailySplash\ProviderHandler\Provider;
use OCA\DailySplash\ProviderHandler\ProviderMetadata;

class WikimediaCommonsDaily extends Provider
{
    public const ID = 'wikimedia_potd';
    // Wikimedia only serves a fixed set of thumbnail widths without rate limiting
    private const THUMB_WIDTH = 1920;

    public function getId(): string
    {
        return self::ID;
    }

    public function getName(): string
    {
        return 'WikimediaCommons - Picture of the Day';
    }

    public function getAllowedImageHosts(): array
    {
        // thumbnails are served from thumb.wikimedia.org, originals from upload.wikimedia.org
        return ['thumb.wikimedia.org', 'upload.wikimedia.org'];
    }

    public function resolveDailyImage(\DateTimeImmutable $today): ?ProviderMetadata
    {
        $date = $today->format('Y-m-d');
        $url = 'https://commons.wikimedia.org/w/api.php?' . http_build_query([
            'action' => 'query',
            'format' => 'json',
            'formatversion' => '2',
            'generator' => 'images',
            'titles' => 'Template:Potd/' . $date,
            'prop' => 'imageinfo',
            'iiprop' => 'url|mime|extmetadata',
            'iiurlwidth' => (string)self::THUMB_WIDTH,
            'iiextmetadatafilter' => 'ImageDescription|Artist|LicenseShortName',
            'iiextmetadatalanguage' => 'en',
        ]);

        $json = $this->getJson($url);
        foreach ($json['query']['pages'] ?? [] as $page) {
            $info = $page['imageinfo'][0] ?? null;
            if (!is_array($info) || !is_string($info['thumburl'] ?? null)) {
                continue;
            }
            $meta = $info['extmetadata'] ?? [];
            $attribution = (string)($info['descriptionurl'] ?? '');
            if (parse_url($attribution, PHP_URL_SCHEME) !== 'https') {
                $attribution = 'https://commons.wikimedia.org/wiki/Commons:Picture_of_the_day';
            }
            return new ProviderMetadata(
                $info['thumburl'],
                $attribution,
                self::plainText($meta['ImageDescription']['value'] ?? ''),
                self::plainText($meta['Artist']['value'] ?? '', 120),
                $this->getName(),
                $date,
                self::plainText($meta['LicenseShortName']['value'] ?? '', 60),
            );
        }

        $this->logger->warning('DailySplash: Wikimedia Commons has no picture of the day for ' . $date, ['app' => 'dailysplash']);
        return null;
    }
}
