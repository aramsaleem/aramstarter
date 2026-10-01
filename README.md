# KorafCloud Starter

A Laravel starter kit with everything a new app needs on day one, styled entirely with Tailwind CSS. Clone it, run one command, and start building your own project on top of it.

- ✅ **User management** - search, filter, create, edit, verify and delete users
- ✅ **Role & permission management** - bundle permissions into roles, create your own permissions in the browser
- ✅ **Two-factor authentication (2FA)** - TOTP with QR setup and recovery codes
- ✅ **Social login** - Google, Facebook and X (Twitter)
- ✅ **Impersonation** - admins can sign in as another user and return with one click
- ✅ **Activity log** - sign-ins, failed attempts, 2FA events and every admin change
- ✅ **Editable website** - texts, features, pricing and FAQ in every language, from the admin panel
- ✅ **Admin dashboard** - live charts, people overview and a security checklist
- ✅ **Localization** - English, Arabic and Central Kurdish, with full right-to-left support
- ✅ Laravel 12, Livewire 3, Tailwind CSS 4, Pest

## Requirements

- PHP 8.3+ with the `intl`, `pdo_mysql` (or `pdo_sqlite`) and `gd` extensions
- Composer 2
- Node.js 20.19+ or 22.12+
- MySQL 8 (or SQLite, MariaDB, PostgreSQL)
- Git

[Laragon](https://laragon.org) on Windows includes all of these.

## Quick start

### 1. Get the code

**Option A - start a new project from the template (recommended).** On GitHub, click **Use this template → Create a new repository**, give it your project's name, then clone your new repository:

```bash
git clone https://github.com/YOUR-USERNAME/YOUR-PROJECT.git my-project
cd my-project
```

**Option B - clone this repository directly** and start a fresh history for your project:

```bash
git clone https://github.com/aramsaleem/korafcloud-starter.git my-project
cd my-project
rm -rf .git          # PowerShell: Remove-Item -Recurse -Force .git
git init -b main
```

With Laragon, clone into `C:\laragon\www` (or wherever your Laragon `www` folder is) so the project is served at `http://my-project.test`.

### 2. Create the database

Open Laragon's database tool (HeidiSQL), MySQL Workbench or the `mysql` command line and run:

```sql
CREATE DATABASE my_project CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 3. Configure the project

Copy the example environment file:

```bash
cp .env.example .env          # PowerShell: Copy-Item .env.example .env
```

Then open `.env` and set at least:

```dotenv
APP_NAME="My Project"
APP_URL=http://my-project.test

DB_DATABASE=my_project
DB_USERNAME=root
DB_PASSWORD=
```

### 4. Install everything

```bash
composer run setup
```

This installs the PHP and npm packages, generates the app key, creates the tables, seeds roles, permissions, demo users and website content, and builds the frontend.

### 5. Run it

```bash
composer run dev
```

This starts the web server, queue worker, log viewer and Vite together. Open http://localhost:8000 (or `http://my-project.test` with Laragon - click **Reload** in Laragon after cloning so the site points at `public/`).

### 6. Sign in

In the `local` environment the seeder creates these accounts. Every one uses the password `password`:

| Email | Role |
|---|---|
| `superadmin@example.com` | Super Admin |
| `admin@example.com` | Admin |
| `user@example.com` | User |

The admin panel is at `/admin`. Change or delete the demo accounts before going live.

### 7. Push to your own repository

If you used Option B, create an empty repository on GitHub (no README), then:

```bash
git add -A
git commit -m "Start my project from KorafCloud Starter"
git remote add origin https://github.com/YOUR-USERNAME/YOUR-PROJECT.git
git push -u origin main
```

## Everyday commands

```bash
composer run dev       # start the app while developing
composer run test      # run the test suite
composer run lint      # fix code style with Pint
composer run review    # check code style, then run the tests
npm run build          # build the frontend for production
php artisan migrate    # run new migrations
```

## Going to production

```bash
composer install --no-dev --optimize-autoloader
npm ci && npm run build
php artisan migrate --force
php artisan db:seed --class=RolesAndPermissionsSeeder --force
php artisan db:seed --class=ContentSeeder --force
php artisan app:create-super-admin
php artisan optimize
```

In the production `.env`, set `APP_ENV=production`, `APP_DEBUG=false` and an `https://` `APP_URL`. Add a cron entry for the scheduler, which removes activity log entries older than 180 days:

```bash
* * * * * cd /path/to/project && php artisan schedule:run >> /dev/null 2>&1
```

`app:create-super-admin` prompts for a name, email and password. Pass the email of an existing user to promote them instead.

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

### Impersonation

Users with the `users.impersonate` permission (Super Admins and Admins by default) can click **Sign in as user** on the users list, a user's edit page or the dashboard. They confirm their own password first, then see the app exactly as that user does. An amber bar offers **Return to my account**.

- You can only impersonate users you outrank, never yourself, and never from inside another impersonation.
- The user's password, 2FA, connected accounts and account deletion are locked while impersonating.
- Sessions end on their own after 60 minutes (`App\Support\Impersonation::MAX_MINUTES`).
- Starting, ending and everything done in between is recorded in the activity log with the admin's email.

### Activity log

**Admin → Activity** (`activity.view` permission) lists sign-ins, failed attempts, lockouts, 2FA events and every change to users, roles, permissions and the website, with IP address and browser. Failed sign-ins keep the attempted email but never the password. Record your own events with:

```php
Audit::log(ActivityEvent::UserUpdated, $user, ['changed' => ['name']]);
```

### Editable website

**Admin → Website** (`content.manage` permission) edits the public home page without touching code: hero texts, buttons, closing text, footer, contact email, section visibility, feature cards, pricing plans and FAQ, in every language. Empty translations fall back to the default language. `php artisan db:seed --class=ContentSeeder` fills the starting content and never overwrites your edits.

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

Light is the default; users can switch to dark or system under **Settings → Appearance**. The choice is saved in the browser and applied before the page paints, including after `wire:navigate` visits.

To change the brand color, edit the `--color-primary-*` variables in `resources/css/app.css`.

### Strict Eloquent models

Outside production, `Model::shouldBeStrict()` turns these mistakes into exceptions:

- N+1 queries caused by lazy loading
- assigning attributes that aren't fillable
- reading attributes that weren't selected

In production, `DB::prohibitDestructiveCommands()` blocks `migrate:fresh` and `db:wipe`.

## Development

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
  Enums/                 SystemRole, SystemPermission, SocialProvider, ActivityEvent
  Http/Controllers/      home page, social login, email verification, language, impersonation
  Http/Middleware/       locale, security headers, impersonation guards
  Livewire/Auth/         login, register, password reset, 2FA challenge...
  Livewire/Settings/     profile, password, 2FA, connected accounts, appearance, language
  Livewire/Admin/        dashboard, users, roles, permissions, activity log, website editor
  Models/                User, ActivityLog, Feature, Plan, Faq, SiteSetting
  Policies/              who may edit, delete or impersonate users, roles and permissions
  Services/              TwoFactorAuthenticator (Google2FA + QR codes)
  Support/               Audit, Impersonation, SiteSettings, Localization
resources/views/
  welcome.blade.php      the public website (content from Admin > Website)
  components/ui/         Tailwind components: button, input, select, modal, dropdown...
  components/layouts/    app, admin and auth layouts
lang/                    ar.json, ckb.json and translated validation messages
```
