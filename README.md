# Easy Stack Ntfy Bundle

Symfony bundle for ntfy integration in Easy Stack applications. It provides a chat UI, commands, fixtures, Twig views and reusable assets for notifications and contact forms.

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
- Twig for the chat view.
- Guzzle for HTTP requests to ntfy.
- A reachable ntfy server, for example self-hosted or `https://ntfy.sh`.
- Host parameter `app_locales`, for example `de|en`, for localized admin routes.
- Optional: DoctrineFixturesBundle when CronJob fixtures should be loaded.

## Installation

```bash
composer require opillion/easy-stack-ntfy-bundle
```

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
| `Resources/templates` | Twig view for the chat UI. |
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

| Variable | Required | Purpose |
| --- | --- | --- |
| `NTFY_HOST` | yes | Base URL of the ntfy server. |
| `NTFY_TOKEN` | yes for chat/UI | Bearer token for authenticated requests. The current chat/menu guard expects a value. |
| `NTFY_CHAT` | yes for chat/UI | Topic used by the EasyAdmin chat UI and one of the sync topics. |
| `NTFY_ZIP_PROTECTION` | yes for chat/UI and ZIP upload | Password for ZIP files; the current chat/menu guard expects a value. |
| `NTFY_TOPIC` | for `ntfy:send` and `ntfy:sync` | Default topic for CLI sends and sync. |
| `NTFY_TOPIC_WEB` | optional | Additional topic for website/contact events. |

When the guarded chat variables are missing, the chat route and menu item are removed.

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

## Tests

```bash
composer install
vendor/bin/phpunit
```
