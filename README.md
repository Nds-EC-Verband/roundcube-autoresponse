# autoresponse — Roundcube Vacation/Out-of-Office Plugin

A clean, self-contained Roundcube plugin that adds a dedicated **"Abwesenheitsnotiz"** (vacation / out-of-office) entry to the Settings navigation.  
It writes and reads a real [Sieve](https://tools.ietf.org/html/rfc5228) `vacation` script via the `managesieve` plugin, so the auto-reply is enforced server-side — independent of whether Roundcube is open.

> **Optimised for [Plesk](https://www.plesk.com/) hosting environments.** The installation paths and default configuration in this README reflect a standard Plesk server setup. The plugin works on any Roundcube installation, but paths may differ outside of Plesk.

---

## Features

- Enable / disable the auto-reply with a single checkbox
- Custom **subject** and **body** for the vacation message
- Optional **date range** — the reply is only sent between two calendar dates
- Sieve script is saved and activated automatically
- Form input is preserved on validation errors (no data loss)
- Falls back to stored user preferences when the Sieve server is temporarily unreachable
- Automatically deactivates the reply if the end date has passed

---

## Requirements

| Requirement | Version |
|---|---|
| Roundcube | 1.4 or newer |
| PHP | 7.4 or newer |
| `managesieve` plugin | must be installed & configured |
| Sieve server | must support the `vacation` extension |

---

## Installation

### 1. Copy the plugin

Place the `autoresponse` folder inside your Roundcube plugins directory.

**Plesk (default path):**
```
/usr/share/psa-roundcube/plugins/autoresponse/
```

**Other environments** — the path depends on your Roundcube installation, e.g.:
```
/var/lib/roundcube/plugins/autoresponse/
```

The directory must contain at least:

```
autoresponse/
├── autoresponse.php
├── config.inc.php.dist        (optional, copy to config.inc.php)
├── localization/
│   └── de_DE.inc              (and any other locales)
└── skins/
    └── elastic/
        └── autoresponse.css
```

### 2. Enable the plugins

Edit your Roundcube main configuration file and add **both** `managesieve` and `autoresponse` to the plugins list.

**Plesk (default path):** `/usr/share/psa-roundcube/config/config.inc.php`

```php
$config['plugins'] = [
    'managesieve',
    'autoresponse',
    // ... your other plugins
];
```

> **Important:** `managesieve` must appear **before** `autoresponse` in the list.

### 3. Configure the Sieve library path (if needed)

The plugin tries to locate `rcube_sieve.php` automatically by looking in the standard `managesieve` plugin paths.

**On a Plesk server** the file is typically found at:

```
/usr/share/psa-roundcube/plugins/managesieve/lib/Roundcube/rcube_sieve.php
```

This path is tried automatically. If auto-detection fails (e.g. on non-Plesk setups), set the path explicitly in `plugins/autoresponse/config.inc.php`:

```php
$config['autoresponse_sieve_lib'] =
    '/usr/share/psa-roundcube/plugins/managesieve/lib/Roundcube/rcube_sieve.php';
```

---

## Configuration

Copy `config.inc.php.dist` to `config.inc.php` inside the plugin folder and adjust as needed.  
All keys are **optional** — the plugin falls back to the corresponding `managesieve_*` values.

```php
<?php

// Override the ManageSieve host (defaults to managesieve_host)
$config['autoresponse_sieve_host'] = 'localhost';

// Override the ManageSieve port (defaults to 4190 or the system sieve service port)
$config['autoresponse_sieve_port'] = 4190;

// Force TLS (defaults to managesieve_usetls)
$config['autoresponse_sieve_tls'] = false;

// Absolute path to rcube_sieve.php — only needed if auto-detection fails
$config['autoresponse_sieve_lib'] = '';

// Minimum interval (days) between vacation replies to the same sender
// Passed directly to the Sieve :days argument (default: 1)
$config['autoresponse_vacation_days'] = 1;
```

---

## How it works

When the user saves an **enabled** vacation notice, the plugin generates and activates a Sieve script like this:

```sieve
require ["vacation", "date", "relational"];

if allof (
    currentdate :value "ge" "date" "2025-07-01",
    currentdate :value "le" "date" "2025-07-31"
) {
    vacation :days 1 :subject "Ich bin im Urlaub" text:
Vielen Dank für Ihre Nachricht. Ich bin derzeit nicht erreichbar.
.
;
}
```

When the vacation notice is **disabled**, the active script is replaced with a harmless comment and deactivated:

```sieve
# autoresponse disabled
```

---

## Contributing

Feedback, bug reports, and improvements are very welcome!

- **Found a bug or something not working?**  
  Please [open an Issue](../../issues) and describe what happened, your Roundcube version, and your server environment (Plesk version, PHP version, mail server). The more detail, the faster it can be fixed.

- **Have an improvement or fix in mind?**  
  Fork the repository, make your changes, and open a [Pull Request](../../pulls). Even small contributions — typo fixes, additional locale files, compatibility improvements — are appreciated.

- **Not sure whether something is a bug or a configuration issue?**  
  Open an Issue anyway. Let's figure it out together.

Errors from the plugin are written to the standard Roundcube error log (usually `logs/errors.log` relative to your Roundcube root) — including that in your issue report is very helpful.

---

## License

MIT — see [LICENSE](LICENSE) for details.
