# DailySplash

**A new picture of the day on your Nextcloud login page, served by your own server.**

DailySplash shows the [Bing](https://www.bing.com) or
[Wikimedia Commons](https://commons.wikimedia.org/wiki/Commons:Picture_of_the_day)
picture of the day as the background of the Nextcloud login page, and optionally
of the dashboard.

[![License: AGPL v3](https://img.shields.io/badge/License-AGPL%20v3-blue.svg)](LICENSE.md)
![Nextcloud 35](https://img.shields.io/badge/Nextcloud-35-0082c9)
![PHP 8.2+](https://img.shields.io/badge/PHP-8.2%2B-777bb4)

## Features

* **Privacy friendly:** images are downloaded by your Nextcloud server and served
  from `/apps/dailysplash/image`. Visitors' browsers never contact Bing or
  Wikimedia, and no Content-Security-Policy exception is needed.
* **Reliable:** a background job checks hourly and only downloads when today's
  image is not cached yet. If a provider is down, yesterday's image stays; if
  nothing was ever downloaded, the normal login page is shown.
* **Safe downloads:** timeouts, a 15 MB limit, a host allow-list, no redirects,
  and only real JPEG, PNG or WebP images are accepted.
* **Attribution:** a small info box credits the source, author and licence of
  each image, with a link to its page.
* **Admin settings** (*Administration settings → Theming*): provider, login page
  and dashboard toggles, preview with image date and last error, "download now",
  tint, blur and a high-visibility mode for the legal links.

## Requirements

* Nextcloud 35
* PHP 8.2 or newer (tested with 8.5)
* Background jobs enabled (Cron recommended)
* Outbound HTTPS from the server to `www.bing.com`, `commons.wikimedia.org`
  and `thumb.wikimedia.org`

## Installation

DailySplash is not in the Nextcloud App Store; install it from GitHub.

1. Download `dailysplash-<version>.tar.gz` from the
   [latest release](https://github.com/Majentas/dailysplash/releases/latest).
2. Extract it into your Nextcloud `apps` directory and give it to the web server
   user:
   ```sh
   tar -xzf dailysplash-1.0.0.tar.gz -C /var/www/nextcloud/apps/
   chown -R www-data:www-data /var/www/nextcloud/apps/dailysplash
   ```
3. Enable it and fetch today's image:
   ```sh
   sudo -u www-data php /var/www/nextcloud/occ app:enable dailysplash
   sudo -u www-data php /var/www/nextcloud/occ dailysplash:fetch
   ```

Adjust paths and the user (`www-data`) to your installation.

Alternatively, clone the repository into `apps/dailysplash`:
`git clone https://github.com/Majentas/dailysplash.git dailysplash`.

### Updating

Replace the `apps/dailysplash` folder with the new release, then run
`occ upgrade`. Check the [changelog](CHANGELOG.md) first.

### Uninstalling

`occ app:disable dailysplash` immediately restores the default login page.
`occ app:remove dailysplash` removes the app.

## Usage

The default provider is **Bing**. The login page background is on and the
dashboard background is off. Change this in *Administration settings → Theming →
DailySplash*. The dashboard background only applies to users who kept the
default theming background.

```sh
occ dailysplash:fetch           # download today's image if not cached yet
occ dailysplash:fetch --force   # download again now
```

## Compatibility

| DailySplash | Nextcloud | PHP |
|---|---|---|
| 1.0.x | 35 | 8.2 – 8.5 |

Each release only declares the Nextcloud versions it has been tested with. A new
Nextcloud major version is supported once a release says so; do not force-enable
the app on untested versions.

## Credits

DailySplash is a modified fork of **[Splash](https://github.com/nextcloud/unsplash)**
(app id `unsplash`), which no longer installs on recent Nextcloud and PHP versions.
See [NOTICE](NOTICE) for what changed.

Splash was conceived by [Jan-Christoph Borchardt](https://github.com/jancborchardt/)
and [Lukas Reschke](https://github.com/LukasReschke), and developed and maintained
by [Felix Nüsse](https://github.com/newhinton) and
[Marius Wieschollek](https://github.com/marius-wieschollek), with the Bing
provider by Bruce Mubangwa and many other contributors (see the git history).
Translations come from the Nextcloud community on Transifex.

The pictures belong to their authors and are shown under their own licences.
DailySplash is not affiliated with Nextcloud GmbH, Microsoft or the Wikimedia
Foundation.

## Development

There is no build step: `js/` and `css/` are plain files. Code layout:

* `lib/Provider/`: which image is today's (Bing, Wikimedia, none)
* `lib/Services/ImageCacheService.php`: download, validation and storage in app data
* `lib/Cron/ImageProviderBackgroundFetch.php`: hourly background job
* `lib/Controller/`: image, CSS and attribution endpoints, admin settings
* `lib/EventListener/BeforeTemplateRenderedEventListener.php`: adds the background
  to the login and dashboard pages

Issues and pull requests are welcome at
<https://github.com/Majentas/dailysplash/issues>.

## License

[AGPL-3.0-or-later](LICENSE.md), like the original Splash app.
