# KorafCloud Starter

A Laravel starter kit with everything a new app needs on day one, styled entirely with Tailwind CSS.

- ✅ **User management** - search, filter, create, edit, verify and delete users
- ✅ **Role management** - bundle permissions into roles
- ✅ **Permissions management** - create your own permissions in the browser
- ✅ **Two-factor authentication (2FA)** - TOTP with QR setup and recovery codes
- ✅ **Social login** - Google, Facebook and X (Twitter)
- ✅ **Localization** - English, Arabic and Central Kurdish, with full right-to-left support
- ✅ Separate **dashboard for Super Admins**
- ✅ Laravel 12 and Livewire 3

## Stack

| | |
|---|---|
| Framework | Laravel 12, Livewire 3 (class components, Alpine.js included) |
| Styling | Tailwind CSS 4, custom Blade components in `resources/views/components/ui` |
| Icons | [Blade Heroicons](https://github.com/blade-ui-kit/blade-heroicons) |
| Code style | [Laravel Pint](https://github.com/laravel/pint) |
| Testing | [Pest](https://pestphp.com) and [missing-livewire-assertions](https://github.com/christophrumpel/missing-livewire-assertions) by Christoph Rumpel |
| Alerts | [Livewire Alert](https://github.com/jantinnerezo/livewire-alert) (SweetAlert2) |
| Roles & permissions | [Spatie Laravel Permission](https://spatie.be/docs/laravel-permission) |
| Two-factor authentication | [Google2FA](https://github.com/antonioribeiro/google2fa) and [bacon-qr-code](https://github.com/Bacon/BaconQrCode) |
| Social login | [Laravel Socialite](https://laravel.com/docs/socialite) |
| Safety | [Strict Eloquent models](https://planetscale.com/blog/laravels-safety-mechanisms) outside production |
| Debugging | [Laravel Debugbar](https://github.com/barryvdh/laravel-debugbar) |

## Requirements

- PHP 8.3+ with the `intl`, `pdo_mysql` (or `pdo_sqlite`) and `gd` extensions
- Composer 2
- Node.js 20.19+ or 22.12+
- MySQL 8 (or SQLite, MariaDB, PostgreSQL)

## Installation

```bash
composer run setup
```

This installs the PHP and npm dependencies, creates `.env`, generates the app key, runs the migrations and seeders and builds the frontend. Before running it, create the database named in `.env` (`korafcloud` by default):

```sql
CREATE DATABASE korafcloud CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Then start everything (web server, queue worker, log viewer and Vite) with:

```bash
composer run dev
```

With Laragon, the site is also served at `http://korafcloud.test`. If that URL lists the project files instead of the app, click **Reload** in Laragon so it points the virtual host at `public/`.

### Accounts

In the `local` environment the seeder creates a Super Admin, an Admin and a regular user. See `database/seeders/DemoUserSeeder.php` for their logins.

In production, create your first Super Admin with:

```bash
php artisan app:create-super-admin
```

The command prompts for a name, email and password. Pass the email of an existing user to promote them instead.

## Features

### Users, roles & permissions

The admin panel lives at `/admin` and requires the `admin.access` permission. It has its own dashboard and layout.

| Role | Access |
|---|---|
| **Super Admin** | Everything. It passes every permission check through `Gate::after`, so a policy that explicitly says no still applies - for example, you can't delete your own account from the admin panel. |
| **Admin** | The admin panel and user management. It can view roles and permissions but not change them. You can change this in the admin panel. |
| **User** | Given to everyone who registers. |

Built-in roles and permissions are defined in `app/Enums/SystemRole.php` and `app/Enums/SystemPermission.php`. They can't be renamed or deleted in the admin panel, because the code refers to them by name. You can add your own permissions in the admin panel: use `group.action` names such as `posts.publish`.

Check a permission the usual Laravel way:

```php
$user->can('posts.publish');              // PHP
Route::middleware('can:posts.publish');   // routes
```

In Blade, use `@can('posts.publish') ... @endcan`.

Some rules protect you from locking yourself out:

- Only Super Admins can grant the Super Admin role, or edit and delete Super Admins.
- The last Super Admin can't lose the role or delete their account.

Re-running `php artisan db:seed --class=RolesAndPermissionsSeeder` is safe: it only adds what is missing.

### Two-factor authentication

Users turn on 2FA under **Settings → Two-factor auth**, which asks for their password again first.

- The secret and recovery codes are stored encrypted.
- A code can't be used twice.
- Each of the 8 recovery codes works once.
- Admins can reset a user's 2FA from the user's edit page.

Social logins go through the same 2FA challenge.

### Social login

Add the credentials of any provider to `.env`. Its button then appears on the login and register pages, and under **Settings → Connected accounts**.

```dotenv
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=
```

`FACEBOOK_*` and `X_*` work the same way. Register these callback URLs with each provider:

| Provider | Callback URL |
|---|---|
| Google | `https://your-app.com/auth/google/callback` |
| Facebook | `https://your-app.com/auth/facebook/callback` |
| X | `https://your-app.com/auth/x/callback` (OAuth 2.0; turn on "Request email from users") |

How accounts are matched:

- A new social login creates an account with a verified email and no password. The user can set a password later under **Settings → Password**.
- A provider is linked to an existing account only if that account's email is verified. This stops someone from registering your address before you do and sharing your account.
- A user can't disconnect their last way of logging in.

### Localization

The language is chosen from, in order:

1. the user's saved preference
2. the language picked this session
3. the browser's `Accept-Language` header

Emails are sent in the user's language too. Arabic and Kurdish switch the whole layout to right-to-left.

To add a language:

1. Add it to `config/localization.php`.
2. Copy `lang/ar.json` and the `lang/ar` directory to the new locale code, and translate them.
3. Run the tests. `tests/Feature/LocalizationTest.php` fails if any interface string or `:placeholder` is missing from a language.

### Appearance

Users can choose a light, dark or system theme. The choice is saved in the browser and applied before the page paints, including after `wire:navigate` visits.

To change the brand color, edit the `--color-primary-*` variables in `resources/css/app.css`.

### Strict Eloquent models

Outside production, `Model::shouldBeStrict()` turns these mistakes into exceptions:

- N+1 queries caused by lazy loading
- assigning attributes that aren't fillable
- reading attributes that weren't selected

In production, `DB::prohibitDestructiveCommands()` blocks `migrate:fresh` and `db:wipe`.

## Development

```bash
composer run test      # Pest test suite
composer run lint      # fix code style with Pint
composer run review    # check code style, then run the tests
```

The tests run against an in-memory SQLite database, so they never touch your development data.

Laravel Debugbar is shown whenever `APP_DEBUG=true`. Set `DEBUGBAR_ENABLED=false` in `.env` to hide it.

To catch the verification and password reset emails locally, use Laragon's Mailpit: set `MAIL_MAILER=smtp` and `MAIL_PORT=1025`, then open http://localhost:8025.

To send real email through Gmail, turn on 2-Step Verification for the Google account and create an [App Password](https://myaccount.google.com/apppasswords). Then set:

```dotenv
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=you@gmail.com
MAIL_PASSWORD=your-16-character-app-password
MAIL_FROM_ADDRESS="you@gmail.com"
```

Gmail always sends from the signed-in account, so `MAIL_FROM_ADDRESS` must match `MAIL_USERNAME`.

## Project layout

```
app/
  Enums/                 SystemRole, SystemPermission, SocialProvider
  Http/Controllers/      social login, email verification, language switch
  Http/Middleware/       SetLocale
  Livewire/Auth/         login, register, password reset, 2FA challenge...
  Livewire/Settings/     profile, password, 2FA, connected accounts, appearance, language
  Livewire/Admin/        dashboard, users, roles, permissions
  Policies/              who may edit or delete users, roles and permissions
  Services/              TwoFactorAuthenticator (Google2FA + QR codes)
resources/views/
  components/ui/         Tailwind components: button, input, select, modal, dropdown...
  components/layouts/    app, admin and auth layouts
lang/                    ar.json, ckb.json and translated validation messages
```
