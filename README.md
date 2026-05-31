# Easy Stack Ntfy Bundle






## Stack
- Symfony 8
- PHP 8.4+
## Stack
- Symfony 8
- PHP 8.4+
## Stack
- Symfony 8
- PHP 8.4+
## Stack
- Symfony 8
- PHP 8.4+
## Stack
- Symfony 8
- PHP 8.4+
Symfony bundle with NTFY-related UI, routes, and maintenance commands for Easy Stack.

Features:

- EasyAdmin menu entry for NTFY chat.
- Route and controller to serve the chat interface.
- Twig template for the chat view.
- Reusable contact popup asset for websites.
- Reusable Docker examples for running a local ntfy service.
- NTFY commands:
  - `ntfy:send`
  - `ntfy:sync`
  - `ntfy:zip-and-send`
- Optional cron job fixtures for the two periodic jobs:
  - `ntfy:sync` (every 30 seconds)
  - `ntfy:zip-and-send` (every hour)

The chat menu item is only shown when required environment variables are present.

## Installation

```bash
composer require opillion/easy-stack-ntfy-bundle
```

If you need bundle assets in a Symfony app:

```bash
php bin/console assets:install public
```

If not using Symfony Flex, enable the bundle in `config/bundles.php`:

```php
return [
    Opillion\EasyStack\NtfyBundle\EasyStackNtfyBundle::class => ['all' => true],
];
```

## Environment

The bundle binds the ntfy service values from environment variables. The chat route and EasyAdmin menu are removed when one of the guarded chat variables is missing or empty.

| Variable | Required for | Notes |
| --- | --- | --- |
| `NTFY_HOST` | All ntfy requests, chat route, chat menu | Base URL of the ntfy server, for example `https://ntfy.sh`. |
| `NTFY_TOKEN` | Authenticated requests, chat route, chat menu | Sent as `Authorization: Bearer ...` when present. The current route/menu guard expects a non-empty value. |
| `NTFY_CHAT` | Chat route, chat menu, `ntfy:sync` | Chat topic shown in the bundled EasyAdmin chat UI. |
| `NTFY_ZIP_PROTECTION` | Chat route, chat menu, `ntfy:zip-and-send` | Used as ZIP password when non-empty. The current route/menu guard expects a value. |
| `NTFY_TOPIC` | `ntfy:send`, `ntfy:sync` | Default topic for CLI sends and one of the topics polled by sync. |
| `NTFY_TOPIC_WEB` | `ntfy:sync`, static contact popup | Optional extra topic for website/contact events. |

The localized chat route also expects the host application's `%app_locales%` parameter:

```yaml
parameters:
    app_locales: 'de|en'
```

## Commands

Send a plain text message:

```bash
bin/console ntfy:send "Hello from Easy Stack"
bin/console ntfy:send "Hello from Easy Stack" --topic=ops
```

Synchronize all configured topics (`NTFY_TOPIC`, `NTFY_TOPIC_WEB`, `NTFY_CHAT`):

```bash
bin/console ntfy:sync
```

Zip one file and upload it to ntfy. The optional topic argument defaults to `dumps`; `--title` controls the ntfy upload title.

```bash
bin/console ntfy:zip-and-send var/backups/dump.sql dumps --title="Database dump"
```

## Symfony Integration

The bundle extension prepends:

- Twig namespace `@EasyStackNtfyBundle`.
- Translation path `src/Resources/translations`.

The service file binds:

- `?string $ntfyHost` from `NTFY_HOST`.
- `?string $ntfyToken` from `NTFY_TOKEN`.
- `?string $ntfyChatTopic` from `NTFY_CHAT`.
- `?string $ntfyZipProtection` from `NTFY_ZIP_PROTECTION`.

If DoctrineFixturesBundle is installed, the bundle registers fixtures for the periodic `ntfy:sync` and `ntfy:zip-and-send` CronJobs.

## Reusable Resources

The bundle also ships reusable assets and examples:

- Contact popup script: `src/Resources/public/js/contact-popup.js`
- Docker examples: `resources/docker/`

For static pages you can wire the popup like this after publishing assets:

```html
<script
  src="/bundles/easystackntfy/js/contact-popup.js"
  data-ntfy-topic="easy-stack-web-contact"
  data-ntfy-host="https://ntfy.sh"
  data-ntfy-title="Neue Kontaktanfrage"
  data-ntfy-tags="contact,website"
></script>
```
