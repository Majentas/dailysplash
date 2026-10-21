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
use OCA\DailySplash\Services\SettingsService;
use OCP\AppFramework\Controller;
use OCP\AppFramework\Http;
use OCP\AppFramework\Http\Attribute\NoAdminRequired;
use OCP\AppFramework\Http\Attribute\NoCSRFRequired;
use OCP\AppFramework\Http\Attribute\NoSameSiteCookieRequired;
use OCP\AppFramework\Http\Attribute\NoTwoFactorRequired;
use OCP\AppFramework\Http\Attribute\PublicPage;
use OCP\AppFramework\Http\DataDisplayResponse;
use OCP\IRequest;
use OCP\IURLGenerator;

/**
 * Class CssController
 *
 * @package OCA\DailySplash\Controller
 */
class CssController extends Controller
{
    public function __construct(
        string $appName,
        IRequest $request,
        private SettingsService $settings,
        private ImageCacheService $imageCache,
        private IURLGenerator $urlGenerator,
    )
    {
        parent::__construct($appName, $request);
    }

    #[NoAdminRequired]
    #[NoCSRFRequired]
    public function dashboard(): DataDisplayResponse
    {
        return $this->prepareResponse($this->backgroundCss('body'));
    }

    #[PublicPage]
    #[NoCSRFRequired]
    #[NoSameSiteCookieRequired]
    #[NoTwoFactorRequired]
    public function login(): DataDisplayResponse
    {
        $css = $this->backgroundCss('body#body-login');
        if ($css !== '' && $this->settings->isHighVisibilityLogin()) {
            $css .= $this->getHighVisibilityStyleLogin();
        }
        return $this->prepareResponse($css);
    }

    /**
     * The stylesheet URL carries a version (?v=), so it can be cached for a day.
     */
    private function prepareResponse(string $css): DataDisplayResponse
    {
        $response = new DataDisplayResponse($css, Http::STATUS_OK, ['Content-Type' => 'text/css']);
        $response->cacheFor(86400, true);
        return $response;
    }

    private function backgroundCss(string $selector): string
    {
        $version = $this->imageCache->getCurrentVersion();
        if ($version === '') {
            // nothing cached yet: leave the theming background alone
            return '';
        }
        $imagePath = $this->urlGenerator->linkToRoute('dailysplash.image.get', ['v' => $version]);

        $css = $selector . ' {';
        if ($this->settings->isTintEnabled()) {
            $css .= $this->getTintStyle($imagePath);
        } else {
            $css .= "background-image: url('$imagePath') !important;";
        }
        if ($this->settings->getBlurStrength() > 0) {
            $css .= 'backdrop-filter: blur(' . $this->settings->getBlurStrength() . 'px);';
        }
        $css .= '}';
        return $css;
    }

    private function getTintStyle(string $imagePath): string
    {
        // getInstanceColor() always returns #rrggbb
        $color = $this->settings->getInstanceColor();
        $r = hexdec(substr($color, 1, 2));
        $g = hexdec(substr($color, 3, 2));
        $b = hexdec(substr($color, 5, 2));
        $alpha = $this->settings->getColorStrength() / 100;
        return 'background-color: #fff !important;'
            . "background-image: linear-gradient(rgba($r, $g, $b, $alpha), rgba($r, $g, $b, $alpha)), url('$imagePath') !important;"
            . 'background-blend-mode: normal, multiply;';
    }

    private function getHighVisibilityStyleLogin(): string
    {
        $css = 'footer { min-height: 120px !important;}';
        $css .= '.legal {';
        $css .= 'background-color: var(--color-primary-element);';
        $css .= 'color: var(--color-primary-element-text) !important;';
        $css .= 'cursor: pointer;';
        $css .= 'border-radius: 22px;';
        $css .= 'padding: 15px;';
        $css .= '}';
        $css .= 'p.info {padding: 15px;height: 80px;line-height: 3;}';
        return $css;
    }
}
