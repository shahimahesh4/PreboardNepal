# Preboard Nepal

A working first increment of a Nepal-focused learning platform using Laravel 12, Filament 5, Livewire 4 and Tailwind 4.

## What works

- Responsive student website: landing page, study library, subject hubs, reader, dashboard, saved resources and profile.
- Registration, login, verification emails and password reset.
- Nine original foundation resources and three practice sets.
- Timed practice with persistent answers, server-side scoring, refresh recovery, result explanations and private history.
- Filament administration for academics, editorial content, practice sets, reports and users, with required authenticator MFA.
- Reviewed publication actions, paid-preview protection and entitlement expiry checks.

The full documented product is larger than this increment. Paid checkout, PDF/OCR ingestion, AI, tutoring and production deployment are **not yet implemented**. See [implementation status](docs/06-implementation-status.md) and the [product documentation](docs/README.md).

## Local setup

Requirements: PHP 8.2+ with SQLite, intl, mbstring, DOM, fileinfo and ZIP extensions; Composer; Node 22+ (tested with Node 24). PHP 8.3+ and a Laravel 13 upgrade remain the intended production direction.

```powershell
composer install
npm ci
Copy-Item .env.example .env
php artisan key:generate
New-Item database/database.sqlite -ItemType File -ErrorAction SilentlyContinue
php artisan migrate --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8000
```

Only copy `.env.example` when `.env` does not already exist. Never regenerate an established production application key. Configure your real database and mail provider before deploying anywhere public.

Open [the local website](http://127.0.0.1:8000). To develop with asset refresh and the exam scheduler, use `composer run dev` instead of the final serve command. Pail is intentionally omitted from that command for Windows compatibility.

Run `php artisan schedule:work` in a separate terminal when using the simple serve command. Production requires the Laravel scheduler to run every minute so closed-browser exam attempts finalize promptly.

## Student demo

For local development only, set `PREBOARD_DEMO_ENABLED=true` in `.env`, then run `php artisan db:seed`. The login page and homepage offer an “Explore local student demo” button. The demo account has a random password and the route only works with `APP_ENV=local`. Keep the flag false outside local development.

The current workspace has this local demo enabled. It is a student account with no admin privileges.

## Staff access

Create an administrator from a trusted terminal:

```powershell
php artisan preboard:admin your-email@example.com --name="Your Name"
```

The command prompts privately for a new password and refuses to overwrite an existing account. Open [administration](http://127.0.0.1:8000/stnapanel), sign in, and enroll an authenticator for mandatory MFA. Create a second administrator for independent content review; authors cannot publish their own edits. No default admin credentials are seeded.

## Development email

The default `MAIL_MAILER=log` writes verification and reset messages to `storage/logs/laravel.log`; it does not deliver real email. Configure SMTP or another supported Laravel mail transport for actual delivery. Do not publish log files.

## Checks

```powershell
php artisan test
php vendor/bin/pint --test
npm run build
```

Tests use an isolated in-memory SQLite database, not your local learner data. CI performs these checks on Linux. Composer install publishes Filament assets and the locally hosted font. `.env`, database files, dependencies and generated assets are excluded from Git.

## Before production

Complete the outstanding work in [implementation status](docs/06-implementation-status.md); upgrade the runtime; set `APP_DEBUG=false`; configure HTTPS, production email, database backups, scheduler/worker supervision and monitoring; finalize curriculum/content and privacy requirements; verify the existing domain migration plan. This application has not replaced preboardnepal.com.

## Membership and payments

Checkout stays closed unless `PAYMENTS_ENABLED=true`, a supported gateway is configured, and an offer is activated in Filament. Use `KHALTI_ENVIRONMENT=sandbox` or `ESEWA_ENVIRONMENT=sandbox` during merchant acceptance testing; production requires `live`. eSewa also needs `ESEWA_ENABLED=true`, `ESEWA_PRODUCT_CODE` and `ESEWA_SECRET_KEY`. Never commit gateway secrets. No offer prices or gateway credentials are seeded.

Verified learners can access `/billing` for their orders. Filament provides Products and Orders; staff can check a stored transaction with its provider but cannot manually mark it paid. Run `php artisan preboard:reconcile-payments` to verify pending payments and recent refunds; the Laravel scheduler runs it every five minutes. Full and partial refunds revoke the associated entitlement; partial refunds require manual review. This integration does not initiate refunds.

See [implementation status](docs/06-implementation-status.md) for remaining production work and payment limitations.

See [Nepal payment setup and gateway options](docs/07-nepal-payment-integrations.md) for configuration, acceptance checks, and connectIPS/Fonepay/NEPALPAY planning.
