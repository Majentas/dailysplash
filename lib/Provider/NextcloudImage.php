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

/**
 * Keeps the background configured in the theming app (no override at all).
 */
class NextcloudImage extends Provider
{
    public const ID = 'nextcloud';

    public function getId(): string
    {
        return self::ID;
    }

    public function getName(): string
    {
        return 'Nextcloud Image';
    }

    public function isCached(): bool
    {
        return false;
    }

    public function getAllowedImageHosts(): array
    {
        return [];
    }

    public function resolveDailyImage(\DateTimeImmutable $today): ?ProviderMetadata
    {
        return null;
    }
}
