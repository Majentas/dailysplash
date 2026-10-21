<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2024 Bruce Truth | Bruce Mubangwa <bruce@broosaction.com>
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

class BingWallpaperDaily extends Provider
{
    public const ID = 'bing';
    private const HOST = 'www.bing.com';
    private const API_URL = 'https://www.bing.com/HPImageArchive.aspx?format=js&idx=0&n=1&mkt=en-US';

    public function getId(): string
    {
        return self::ID;
    }

    public function getName(): string
    {
        return 'Bing Wallpaper - Picture of the Day';
    }

    public function getAllowedImageHosts(): array
    {
        return [self::HOST];
    }

    public function resolveDailyImage(\DateTimeImmutable $today): ?ProviderMetadata
    {
        $json = $this->getJson(self::API_URL);
        $image = $json['images'][0] ?? null;
        if (!is_array($image)) {
            $image = [];
        }
        $urlBase = $image['urlbase'] ?? null;
        if (!is_string($urlBase) || !str_starts_with($urlBase, '/th?id=')) {
            $this->logger->warning('DailySplash: Bing returned no usable image', ['app' => 'dailysplash']);
            return null;
        }

        // Bing publishes per market day; startdate is YYYYMMDD
        $date = \DateTimeImmutable::createFromFormat('!Ymd', (string)($image['startdate'] ?? ''));
        $imageDate = $date !== false ? $date->format('Y-m-d') : $today->format('Y-m-d');

        // "Description, Place (© Author/Agency)"
        $copyright = (string)($image['copyright'] ?? '');
        $description = $copyright;
        $author = '';
        if (preg_match('/^(.*?)\s*\((©[^)]*)\)\s*$/u', $copyright, $m)) {
            $description = $m[1];
            $author = $m[2];
        }

        $attribution = (string)($image['copyrightlink'] ?? '');
        if (parse_url($attribution, PHP_URL_SCHEME) !== 'https' || parse_url($attribution, PHP_URL_HOST) !== self::HOST) {
            $attribution = 'https://' . self::HOST . '/';
        }

        return new ProviderMetadata(
            'https://' . self::HOST . $urlBase . '_1920x1080.jpg',
            $attribution,
            self::plainText($description),
            self::plainText($author, 120),
            $this->getName(),
            $imageDate,
        );
    }
}
