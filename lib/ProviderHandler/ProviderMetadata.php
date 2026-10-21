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

/**
 * Description of one daily image, as reported by a provider.
 */
class ProviderMetadata
{
    /**
     * @param string $imageUrl remote URL the image is downloaded from (server side only)
     * @param string $attributionUrl page describing the image and its licence
     * @param string $imageDescription
     * @param string $imageAuthor
     * @param string $source provider name
     * @param string $imageDate date the provider published the image for (Y-m-d)
     * @param string $license short licence name, if known
     */
    public function __construct(
        private string $imageUrl,
        private string $attributionUrl,
        private string $imageDescription,
        private string $imageAuthor,
        private string $source,
        private string $imageDate,
        private string $license = '',
    )
    {
    }

    public function getImageUrl(): string
    {
        return $this->imageUrl;
    }

    public function getAttributionUrl(): string
    {
        return $this->attributionUrl;
    }

    public function getImageDescription(): string
    {
        return $this->imageDescription;
    }

    public function getImageAuthor(): string
    {
        return $this->imageAuthor;
    }

    public function getSource(): string
    {
        return $this->source;
    }

    public function getImageDate(): string
    {
        return $this->imageDate;
    }

    public function getLicense(): string
    {
        return $this->license;
    }

    public function toArray(): array
    {
        return [
            'imageUrl' => $this->imageUrl,
            'attributionUrl' => $this->attributionUrl,
            'description' => $this->imageDescription,
            'author' => $this->imageAuthor,
            'source' => $this->source,
            'imageDate' => $this->imageDate,
            'license' => $this->license,
        ];
    }

    public static function fromArray(array $data): self
    {
        $get = static fn (string $key): string => is_string($data[$key] ?? null) ? $data[$key] : '';
        return new self(
            $get('imageUrl'),
            $get('attributionUrl'),
            $get('description'),
            $get('author'),
            $get('source'),
            $get('imageDate'),
            $get('license'),
        );
    }
}
