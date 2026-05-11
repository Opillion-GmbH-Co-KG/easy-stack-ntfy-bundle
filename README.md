# Easy Stack Ntfy Bundle

Symfony bundle with NTFY-related UI, routes, and maintenance commands for Easy Stack.

Features:

- EasyAdmin menu entry for NTFY chat.
- Route and controller to serve the chat interface.
- Twig template for the chat view.
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
