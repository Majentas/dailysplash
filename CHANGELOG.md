# Changelog

All notable changes to DailySplash. The history of the original Splash app
(`unsplash`) is kept below for reference.

## 1.0.1 - 2026-10-21

Metadata and documentation only, no code changes.

### Changed
* `info.xml`: links to the DailySplash repository, documentation link and screenshot for the Nextcloud App Store.
* README: installation and update from GitHub.

## 1.0.0 - 2026-10-21

First release of the DailySplash fork (based on upstream Splash 3.2.0-dev, commit 5aa215d).

### Changed
* Renamed to DailySplash: app id `dailysplash`, namespace `OCA\DailySplash`, new routes, settings keys, app data folder, CSS classes and l10n domain.
* Requires Nextcloud 35 and PHP 8.2 or newer (tested with PHP 8.5).
* Settings use the typed `IAppConfig` / `IUserConfig` APIs; controller annotations replaced by PHP attributes; no private `OC\` classes.
* Bing and Wikimedia Commons picture of the day are now downloaded server side by an hourly background job (only when the cached image is not today's), with timeouts, a 15 MB limit, host allow-list and image type check, and served by Nextcloud. Visitors' browsers never contact the providers, so no CSP exception is needed anymore.
* If a provider fails, the previous image stays in place and the job retries an hour later.
* Default provider is Bing; login page enabled, dashboard disabled by default.
* Admin settings show the image date, last check and last error, and can download today's image on demand.

### Added
* `occ dailysplash:fetch [--force]`.

### Removed
* Unsplash API, Wallhaven and random Wikimedia providers, keyword and token settings, personal settings notice, CSP listener.

---

# Upstream Splash changelog

## 3.1.0 - 2025-05-20
### What's Changed
* Add NC 31 support by @cociweb in https://github.com/nextcloud/unsplash/pull/165
* Add Bing Daily Wallpaper by @brucetruth in https://github.com/nextcloud/unsplash/pull/155

### Fixed
* fix(settings): swap deprecated `style()` for `addStyle()` + fix for missing `user.js` resource by @joshtrichards in https://github.com/nextcloud/unsplash/pull/158
* fix(provider): Hide verbose curl output behind `debug` by @joshtrichards in https://github.com/nextcloud/unsplash/pull/162

### Other
* chore(routes): eliminate usage of deprecated `registerRoutes()` by @joshtrichards in https://github.com/nextcloud/unsplash/pull/157
* refactor: Use constructor property promotion by @joshtrichards in https://github.com/nextcloud/unsplash/pull/159
* chore: Move to IBootstrap and refactor Application.php by @joshtrichards in https://github.com/nextcloud/unsplash/pull/160

### New Contributors
* @joshtrichards made their first contribution in https://github.com/nextcloud/unsplash/pull/157
* @cociweb made their first contribution in https://github.com/nextcloud/unsplash/pull/165
* @brucetruth made their first contribution in https://github.com/nextcloud/unsplash/pull/155

**Full Changelog**: https://github.com/nextcloud/unsplash/compare/v3.0.3...v3.1.0

## 3.0.3 - 2024-11-09
### What did we do this time?
- Introduced compatibility with nextcloud 30
- Changed default searchterms to nature and colorful
- Improve handling of removed providers or missconfigurations

### Known Issues:
- Preview does not always work

## 3.0.2 - 2024-10-06
### What did we do this time?
- Introduced compatibility with nextcloud 30
- Changed default searchterms to nature and colorful
- Improve handling of removed providers or missconfigurations

### Known Issues:
- Preview does not always work

## 3.0.1 - 2024-09-07
### What did we do this time?
- Introduced compatibility with nextcloud 29
- Fix Login-Route (@Bl4DEx)
- Use Wikimedia as a default provider if the existing one is missing or none choosen

### Known Issues:
- Preview does not always work

## 3.0.0 - 2024-08-31
### What did we do this time?
- Introduced compatibility with nextcloud 28
- Introduced image caching and new image sources
- Added Wikimedia
- Added Wallhaven.cc
- Added support for the new unsplash api (api token required)

### Known Issues:
- Preview does not always work

## 2.2.1 - 2023-06-21
### What did we do this time?
- Fixed Broken Background-Key for Nextcloud 27 Compatibility.

### Known Issues:
- Blur is not beeing applied on dashbard

## 2.2.0 - 2023-02-12
### Added
- Allow an admin to highlight privacy and data protection links on login-screen for better visibility

## 2.1.1 - 2022-11-07
### Fixed
- Fixed Image beeing to dark when tinting was applied

## 2.1.0 - 2022-11-07
### Added
- Added Transifex-Translations - Thanks @p-bo, @rakekniven, @nickvergessen and everyone who added translations!
### Removed
- Removed specific Settings for users. They are now automatically integrated into the new Backgrounds!
- Removed Support for PHP 7.4


## 2.0.1 - 2022-10-04
### Fixed
- Fixed incompatibility with Php 7.3 and below
- Fixed missing theming for totp - Thanks @skjnldsv !

## 2.0.0 - 2022-10-03
### Added
- Support for Wallhaven and Wikimedia
- Blurring
- Tinting
- Added Czech Translation (Thanks @p-bo!)


## 1.2.5 - 2022-05-08
### Changed
- Update French Translation (Thanks @ijeantet!)
- Server Compatibility (Nextcloud 24)

## 1.2.4 - 2021-11-30
### Changed
- Server Compatibility (Nextcloud 23)

## 1.2.3 - 2021-07-11
### Changed
- Update Polish Translation (Thanks @Valdnet!)
- Server Compatibility (Nextcloud 22)

## 1.2.2 - 2021-01-19
### Changed
- Update French Translation (Thanks @ijeantet!)
- Server Compatibility (Nextcloud 21)

## 1.2.1 - 2021-01-19
### Changed
- Use proper unsplash-links for dashboard
- Server Compatibility (Nextcloud 20)

## 1.2.0 - 2021-01-16
### Added
- Added the ability to override dashboard backgrounds (#78)
### Changed
- Updated the Settingspage to not use jQuery (#79)
- Rework internal mechanism to push stylesheets (#77)

Thanks @marius-wieschollek!

## 1.1.7 - 2020-10-20
### Changed
- Polish Translation (Thanks @Valdnet!)
- Server Compatibility

## 1.1.6 - 2020-06-05
### Fixed
- external API Calls (Thanks @robinmetral!)(#60)

## 1.1.5 - 2019-10-09
### Changed
- Update Polish Translation (Thanks @Valdnet!)


## 1.1.4 - 2019-10-09
### Changed
- Rename App to Splash
- Server Compatibility


## 1.1.2 - 2018-12-04
### Fixed
- Server Compatibility
- Many improvements to css styles


## 1.1.0 - 2018-04-12
### Added
- Header Background Image
### Changed
- Change the description to fit the app better


## 1.0.6 - 2018-04-10
### Fixed
- Only use Nature Images
