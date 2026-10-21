<?php

declare(strict_types=1);

/**
 * @copyright Copyright (c) 2018, Felix Nüsse
 * Modified for DailySplash in 2026 (see NOTICE).
 *
 * @author Felix Nüsse <felix.nuesse@t-online.de>
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

use OCA\DailySplash\Provider\BingWallpaperDaily;
use OCA\DailySplash\Provider\NextcloudImage;
use OCA\DailySplash\Provider\WikimediaCommonsDaily;

class ProviderDefinitions
{
    public const DEFAULT_PROVIDER = BingWallpaperDaily::ID;

    /** @var array<string, Provider> */
    private array $definitions = [];

    public function __construct(
        BingWallpaperDaily $bing,
        WikimediaCommonsDaily $wikimedia,
        NextcloudImage $nextcloud,
    )
    {
        //add all provider to this array. The logic takes care of the rest.
        foreach ([$bing, $wikimedia, $nextcloud] as $provider) {
            $this->definitions[$provider->getId()] = $provider;
        }
    }

    public function exists(string $id): bool
    {
        return isset($this->definitions[$id]);
    }

    /**
     * This returns the requested provider, or the default one for unknown ids.
     */
    public function getProviderById(string $id): Provider
    {
        return $this->definitions[$id] ?? $this->definitions[self::DEFAULT_PROVIDER];
    }

    /**
     * This returns all defined providers, keyed by id.
     *
     * @return array<string, Provider>
     */
    public function getAll(): array
    {
        return $this->definitions;
    }
}
