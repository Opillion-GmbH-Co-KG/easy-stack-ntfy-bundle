# Easy Stack Ntfy Bundle

[![Tests](https://github.com/Opillion-GmbH-Co-KG/easy-stack-ntfy-bundle/actions/workflows/tests.yml/badge.svg)](https://github.com/Opillion-GmbH-Co-KG/easy-stack-ntfy-bundle/actions/workflows/tests.yml)
[![License: GPL v3](https://img.shields.io/badge/License-GPLv3-blue.svg)](LICENSE)

Symfony bundle for [ntfy](https://ntfy.sh/) integration in Easy Stack applications. It provides a chat UI, commands, fixtures, Twig views and reusable assets for notifications and contact forms.

## What It Provides

- EasyAdmin menu item and route for an ntfy chat UI.
- Commands for sending, syncing and ZIP uploads to ntfy.
- Optional CronJob fixtures for `ntfy:sync` and `ntfy:zip-and-send`.
- Twig templates and translations for the admin view.
- Reusable contact popup JavaScript for static websites.
- Docker examples for a local ntfy service.
- Route and menu providers with guards for missing ntfy configuration.

## Requirements

- PHP >= 8.4.
- Symfony 8 application following the Easy Stack conventions.
- EasyAdmin 5.
- PHP extensions `fileinfo` and `zip`.
- Twig for the chat view.
- Guzzle for HTTP requests to ntfy.
- A reachable ntfy server, for example self-hosted or `https://ntfy.sh`.
- Host parameter `app_locales`, for example `de|en`, for localized admin routes.
- Optional: DoctrineFixturesBundle when CronJob fixtures should be loaded.

## Installation

```bash
composer config repositories.easy-stack-ntfy-bundle vcs https://github.com/Opillion-GmbH-Co-KG/easy-stack-ntfy-bundle.git
composer require opillion/easy-stack-ntfy-bundle:dev-main
```

The package is currently installed directly from this repository and is not yet published on Packagist.

If assets should be published:

```bash
php bin/console assets:install public
```

If Symfony Flex does not register the bundle automatically:

```php
// config/bundles.php
return [
    Opillion\EasyStack\NtfyBundle\EasyStackNtfyBundle::class => ['all' => true],
];
```

## Components

| Component | Purpose |
| --- | --- |
| `Controller` | Chat route and controller for EasyAdmin. |
| `Command` | `ntfy:send`, `ntfy:sync`, `ntfy:zip-and-send`. |
| `DataHolder/DataFixtures` | Optional CronJob fixtures for periodic ntfy jobs. |
| `Resources/views` | Twig view for the chat UI. |
| `Resources/public/js/contact-popup.js` | Reusable contact popup for websites. |
| `System/EasyAdmin` | EasyAdmin menu integration. |
| `System/Routing` | Route provider with configuration guards. |
| `resources/docker` | Docker examples for ntfy. |

## Environment Variables

```dotenv
NTFY_HOST=https://ntfy.sh
NTFY_TOKEN=
NTFY_CHAT=
NTFY_ZIP_PROTECTION=
NTFY_TOPIC=
NTFY_TOPIC_WEB=
```

These placeholders document the variable names only. This repository does not contain a runtime `.env` file or credentials. Configure values in the consuming application's secret management or deployment environment.

| Variable | Required | Purpose |
| --- | --- | --- |
| `NTFY_HOST` | yes | Base URL of the ntfy server. |
| `NTFY_TOKEN` | yes for chat/UI | Bearer token for authenticated requests. The current chat/menu guard expects a value. |
| `NTFY_CHAT` | yes for chat/UI | Topic used by the EasyAdmin chat UI and one of the sync topics. |
| `NTFY_ZIP_PROTECTION` | yes for chat/UI and ZIP upload | Password for ZIP files; the current chat/menu guard expects a value. |
| `NTFY_TOPIC` | for `ntfy:send` and `ntfy:sync` | Default topic for CLI sends and sync. |
| `NTFY_TOPIC_WEB` | optional | Additional topic for website/contact events. |

When the guarded chat variables are missing, the chat route and menu item are removed.

### Browser-visible credentials

The built-in chat UI performs ntfy requests in the browser. Its `NTFY_TOKEN` and `NTFY_ZIP_PROTECTION` values are therefore visible to authenticated users who can open the chat page. Use a dedicated ntfy user, a dedicated topic and a password that is not reused elsewhere. Likewise, a `data-ntfy-token` value configured for the contact popup is public to website visitors; never place a general-purpose ntfy token there.

## Usage

Send a message:

```bash
bin/console ntfy:send "Hello from Easy Stack"
bin/console ntfy:send "Hello from Easy Stack" --topic=ops
```

Synchronize configured topics:

```bash
bin/console ntfy:sync
```

Zip a file and upload it to ntfy:

```bash
bin/console ntfy:zip-and-send var/backups/dump.sql dumps --title="Database dump"
```

Include the contact popup in static pages after `assets:install`:

```html
<script
  src="/bundles/easystackntfy/js/contact-popup.js"
  data-ntfy-topic="easy-stack-web-contact"
  data-ntfy-host="https://ntfy.sh"
  data-ntfy-title="Neue Kontaktanfrage"
  data-ntfy-tags="contact,website"
></script>
```

The contact popup requires Bootstrap 5's JavaScript and CSS in the host page. Elements with `data-contact-trigger` open the modal.

## Docker Examples

The files under `resources/docker/` are building blocks for Easy Stack's image pipeline rather than a standalone production deployment. The development Compose example references these variables without committing a `.env` file:

| Variable | Purpose |
| --- | --- |
| `DOCKER_REPO_NAME` | Registry namespace containing the intermediate ntfy images. |
| `BASE_IMAGE_TAG` | Tag of the base ntfy image. |
| `DEV_IMAGE_TAG` | Tag of the development image. |
| `PROD_IMAGE_TAG` | Tag of the production image. |
| `NTFY_EXTERNAL_PORT` | Port exposed on the host. |
| `NTFY_INTERNAL_PORT` | ntfy listen port inside the container. |
| `NTFY_DATA_PATH` | Optional host path for cache and authentication data. |
| `USER_ID` / `GROUP_ID` | Runtime user and group passed to the development image. |

Review the sample `server.yml`, use your own public base URL and create ntfy users outside the repository before exposing an instance.

## Tests

```bash
composer install
vendor/bin/phpunit
```

## Security

Do not report vulnerabilities or accidentally exposed credentials in a public issue. Follow [SECURITY.md](SECURITY.md) to report them privately.

## License

This project is licensed under the GNU General Public License v3.0 or later. See [LICENSE](LICENSE).
