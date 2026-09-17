# Setup

## Requirements

- PHP 8.2+ with PDO MySQL, JSON, mbstring, fileinfo and GD
- MySQL 8+
- Composer 2
- Apache `mod_rewrite` or an equivalent Nginx front-controller rule

## Install

1. Copy `.env.example` to `.env` and set the database, URL, timezone and payment values.
2. Run `composer install --no-dev --optimize-autoloader` for production, or `composer install` for development.
3. Create the database with `utf8mb4`, then import `database/schema.sql` and `database/seed.sql`.
4. Create accounts:

```bash
php scripts/create-user.php admin "Prosenjit Roy" admin@example.com "Use-A-Strong-Password"
php scripts/create-user.php student "Anirban Hazra" student@example.com "Use-A-Strong-Password" 9876543210
```

5. Make `storage/logs`, `storage/uploads` and `storage/exports` writable by PHP but not publicly executable.
6. Set the document root to the absolute `public/` directory.

Local test server (routing works, but Apache security rules should still be checked before production):

```bash
php -S 127.0.0.1:8000 -t public public/index.php
```

## Timezone

The application default is `Asia/Kolkata`. MySQL session/system time must agree. Store/compare all test windows using the configured zone consistently; do not derive deadlines from browser time.

## Cron

Run this once every minute in production:

```cron
* * * * * /opt/alt/php85/usr/bin/php /home/ACCOUNT/lcc_cbt/current/scripts/finalize-expired.php >> /home/ACCOUNT/lcc_cbt/shared/storage/logs/finalize.log 2>&1
```

Replace `ACCOUNT` and the PHP path with the hosting values. It finalizes an `in_progress` attempt when its server deadline passes or it has not been seen for 30 minutes. Request-time guards remain the primary protection; cron covers a closed or disconnected browser.

## Backup/restore check

Before every release, take a consistent MySQL dump and a copy of `storage/uploads`. Restore both into staging and confirm that one historical result screen and PDF still agree with the database totals.
