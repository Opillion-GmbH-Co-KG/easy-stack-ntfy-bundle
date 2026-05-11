# Easy Stack Ntfy Bundle

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

To expose the chat menu and enable ntfy sync features, set:

- `NTFY_HOST`
- `NTFY_CHAT`
- `NTFY_TOKEN`
- `NTFY_ZIP_PROTECTION`
- `NTFY_TOPIC`

Optional topic helpers:

- `NTFY_TOPIC_WEB`

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
