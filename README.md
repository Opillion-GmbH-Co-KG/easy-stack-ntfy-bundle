# Easy Stack Ntfy Bundle

Symfony-Bundle fuer ntfy-Integration in Easy-Stack-Anwendungen. Es liefert Chat-UI, Commands, Fixtures, Twig-Views und wiederverwendbare Assets fuer Benachrichtigungen und Kontaktformulare.

## Was hat das?

- EasyAdmin-Menuepunkt und Route fuer eine ntfy-Chat-Oberflaeche.
- Commands fuer Senden, Synchronisieren und ZIP-Upload an ntfy.
- Optionale CronJob-Fixtures fuer `ntfy:sync` und `ntfy:zip-and-send`.
- Twig-Templates und Uebersetzungen fuer die Admin-Ansicht.
- Wiederverwendbares Contact-Popup-JavaScript fuer statische Webseiten.
- Docker-Beispiele fuer einen lokalen ntfy-Service.
- Route- und Menue-Provider mit Guards fuer fehlende ntfy-Konfiguration.

## Was brauche ich?

- PHP >= 8.4.
- Symfony 8 Anwendung mit Easy-Stack-Konventionen.
- EasyAdmin 5.
- Twig fuer die Chat-Ansicht.
- Guzzle fuer HTTP-Requests an ntfy.
- Einen erreichbaren ntfy-Server, z. B. selbst gehostet oder `https://ntfy.sh`.
- Host-Parameter `app_locales`, z. B. `de|en`, fuer lokalisierte Admin-Routen.
- Optional: DoctrineFixturesBundle, wenn CronJob-Fixtures geladen werden sollen.

## Installation

```bash
composer require opillion/easy-stack-ntfy-bundle
```

Falls Assets veroeffentlicht werden sollen:

```bash
php bin/console assets:install public
```

Falls Symfony Flex das Bundle nicht automatisch registriert:

```php
// config/bundles.php
return [
    Opillion\EasyStack\NtfyBundle\EasyStackNtfyBundle::class => ['all' => true],
];
```

## Komponenten

| Komponente | Aufgabe |
| --- | --- |
| `Controller` | Chat-Route und Controller fuer EasyAdmin. |
| `Command` | `ntfy:send`, `ntfy:sync`, `ntfy:zip-and-send`. |
| `DataHolder/DataFixtures` | Optionale CronJob-Fixtures fuer periodische ntfy-Jobs. |
| `Resources/templates` | Twig-View fuer die Chat-Oberflaeche. |
| `Resources/public/js/contact-popup.js` | Wiederverwendbares Contact-Popup fuer Webseiten. |
| `System/EasyAdmin` | EasyAdmin-Menueintegration. |
| `System/Routing` | Routenbereitstellung mit Konfigurations-Guards. |
| `resources/docker` | Docker-Beispiele fuer ntfy. |

## Environment-Variablen

```dotenv
NTFY_HOST=https://ntfy.sh
NTFY_TOKEN=
NTFY_CHAT=
NTFY_ZIP_PROTECTION=
NTFY_TOPIC=
NTFY_TOPIC_WEB=
```

| Variable | Pflicht | Zweck |
| --- | --- | --- |
| `NTFY_HOST` | ja | Basis-URL des ntfy-Servers. |
| `NTFY_TOKEN` | fuer Chat/UI ja | Bearer Token fuer authentifizierte Requests. Die aktuelle Chat-/Menue-Guard erwartet einen Wert. |
| `NTFY_CHAT` | fuer Chat/UI ja | Topic der EasyAdmin-Chat-Oberflaeche und eines der Sync-Topics. |
| `NTFY_ZIP_PROTECTION` | fuer Chat/UI und ZIP-Upload ja | Passwort fuer ZIP-Dateien; die aktuelle Chat-/Menue-Guard erwartet einen Wert. |
| `NTFY_TOPIC` | fuer `ntfy:send` und `ntfy:sync` | Default-Topic fuer CLI-Sends und Sync. |
| `NTFY_TOPIC_WEB` | optional | Zusaetzliches Topic fuer Website-/Kontakt-Events. |

Fehlen die geschuetzten Chat-Variablen, werden Chat-Route und Menueeintrag entfernt.

## Nutzung

Nachricht senden:

```bash
bin/console ntfy:send "Hello from Easy Stack"
bin/console ntfy:send "Hello from Easy Stack" --topic=ops
```

Konfigurierte Topics synchronisieren:

```bash
bin/console ntfy:sync
```

Datei zippen und an ntfy hochladen:

```bash
bin/console ntfy:zip-and-send var/backups/dump.sql dumps --title="Database dump"
```

Contact-Popup nach `assets:install` in statische Seiten einbinden:

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
