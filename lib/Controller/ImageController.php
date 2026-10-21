<?php

declare(strict_types=1);

/**
 * This file is part of the Unsplash App
 * and licensed under the AGPL.
 *
 * Modified for DailySplash (see NOTICE).
 */

namespace OCA\DailySplash\Controller;

use OCA\DailySplash\Services\ImageCacheService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\NoSameSiteCookieRequired;
use OCP\AppFramework\Http\Attribute\NoTwoFactorRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\FileDisplayResponse;
use OCP\AppFramework\Http\JSONResponse;
use OCP\AppFramework\Http\NotFoundResponse;
use OCP\AppFramework\Http\Response;
use OCP\IRequest;

/**
 * Serves the cached image: visitors only ever talk to this Nextcloud.
 *
 * @package OCA\DailySplash\Controller
 */
class ImageController extends Controller
{
    public function __construct(
        string $appName,
        IRequest $request,
        private ImageCacheService $imageCache,
    )
    {
        parent::__construct($appName, $request);
    }

    #[PublicPage]
    #[NoCSRFRequired]
    #[NoSameSiteCookieRequired]
    #[NoTwoFactorRequired]
    public function get(string $v = ''): Response
    {
        $current = $this->imageCache->getCurrent();
        if ($current === null) {
            return new NotFoundResponse();
        }

        $response = new FileDisplayResponse($current['file'], Http::STATUS_OK, ['Content-Type' => $current['mime']]);
        if ($v === $current['version']) {
            // versioned URL: content behind it never changes
            $response->cacheFor(365 * 86400, true, true);
        } else {
            $response->cacheFor(3600, true);
        }
        return $response;
    }

    #[PublicPage]
    #[NoCSRFRequired]
    #[NoSameSiteCookieRequired]
    #[NoTwoFactorRequired]
    public function metadata(): JSONResponse
    {
        $current = $this->imageCache->getCurrent();
        if ($current === null) {
            return new JSONResponse([], Http::STATUS_NOT_FOUND);
        }
        $metadata = $current['metadata'];
        $response = new JSONResponse([
            'source' => $metadata->getSource(),
            'description' => $metadata->getImageDescription(),
            'author' => $metadata->getImageAuthor(),
            'license' => $metadata->getLicense(),
            'attribution' => $metadata->getAttributionUrl(),
            'date' => $metadata->getImageDate(),
        ]);
        $response->cacheFor(3600, true);
        return $response;
    }
}
