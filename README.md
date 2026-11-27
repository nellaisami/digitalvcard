# Northstar Admin Dashboard

A responsive Laravel admin dashboard demo with password + email OTP sign-in, password recovery, account settings, superadmin-only admin, user, organization, plan, and tax management, profile photos, configurable plan features, and organization plan assignment.

## Requirements

- PHP 8.3+ with `openssl`, `pdo_mysql`, `mbstring`, `tokenizer`, `xml`, `ctype`, `fileinfo`, and `curl` enabled
- Composer 2+
- MySQL 8.0+ (or a compatible MariaDB release)
- Node.js 20+ and npm (to compile the included Tailwind CSS bundle)
- Set `APP_CURRENCY` in `.env` to the ISO currency code used for plan display (defaults to `USD`).

This project was scaffolded with Laravel 13 and PHP 8.5. Laravel 13 requires PHP 8.3 or later.

## Install on Windows / XAMPP

1. Start MySQL from the XAMPP Control Panel. Create an empty database named `digitalvc` (or choose another name).
2. In this project directory, install PHP dependencies:

   ```powershell
   composer install
   ```

3. Install front-end dependencies and compile Tailwind:

   ```powershell
   npm install
   npm run build
   ```

4. Create your local environment file and generate the app key:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   ```

5. Open `.env` and set `DB_DATABASE`, `DB_USERNAME`, and `DB_PASSWORD` to the values for your local MySQL instance. The example database name is `digitalvc`; username and password are blank placeholders and must match the credentials configured on your machine.
6. Configure outgoing email in `.env` for OTP and password-reset delivery. Set `MAIL_MAILER`, `MAIL_HOST`, `MAIL_PORT`, `MAIL_USERNAME`, `MAIL_PASSWORD`, `MAIL_ENCRYPTION` (or `MAIL_SCHEME`), and `MAIL_FROM_ADDRESS` for your mail provider. Do not commit `.env` or real credentials. For local development without SMTP, the default `MAIL_MAILER=log` writes email content to `storage/logs/laravel.log`.
7. Create the tables and demo data:

   ```powershell
   php artisan migrate --seed
   php artisan storage:link
   ```

8. Start the development server and open the URL it prints (typically `http://127.0.0.1:8000`):

   ```powershell
   php artisan serve
   ```

The root URL redirects to the dashboard, and guests are sent to the login page. For local Apache, configure the site document root to this project's `public` directory. When editing Blade templates or Tailwind classes, rerun `npm run build` (or use `npm run dev` during development).

## Demo accounts (local testing only)

All seeded role accounts use the demo password `password123`. Sign in with any of these addresses:

| Role | Email |
| --- | --- |
| Super Admin | `superadmin@example.com` |
| Admin | `admin@example.com` |
| Manager | `manager@example.com` |
| Editor | `editor@example.com` |
| Staff | `staff@example.com` |

After entering a password, provide the email verification code delivered to the account address. Registration also requires this code before the new account can access the dashboard. When using the local `log` mail driver, find the code in `storage/logs/laravel.log`. Change or remove the demo credentials before using a deployed environment.

## Authentication and access

- Super Admin accounts are stored separately in `super_admins`; regular user management cannot list, delete, or assign the Super Admin role.
- Registration creates an active `staff` account, then requires email OTP verification before sign-in; public registration cannot choose an elevated role.
- Password sign-in and public registration require a second, six-digit email code before protected pages are accessible. Codes are stored as hashes, expire after 10 minutes, are limited to five verification attempts, and can be resent with throttling.
- The standard Laravel password broker handles password reset tokens. Configure mail to deliver reset links.
- Session regeneration, logout invalidation, CSRF protection, login throttling, and Laravel password hashing are enabled.
- Only the Super Admin guard can access Admin and Users management. Admin accounts have a dedicated create, view, edit, search, activate/deactivate, and delete workflow.
- Only Super Admins can manage plans and tax settings. Plans support monthly, yearly, and unlimited frequency, configurable features and numeric limits, transactional single-default selection, and assignment-safe deletion.
- Super Admins manage organizations through the `organizations.*` routes. Creating an organization transactionally creates a platform `admin` owner account, links it with the `owner` pivot role, assigns an optional active plan, and sends a signed email-verification link. Super Admins can also assign the Admin role to existing organization owners. Owners use the existing email OTP login flow and can view only their own active organization.
- Organization owners and active managers can manage memberships using the organization-only `member` and `manager` roles. Super Admins can assign both standard users and platform Admin accounts to organizations with either membership role; the platform Admin role remains global and is not an organization role. New accounts receive a signed email-verification link. Removing a membership retains the user account and vCards; the organization owner cannot be edited or removed through member management.
- Organization owners and platform Admins assigned to an organization can upload, replace, or remove that organization's JPG, JPEG, PNG, or WebP logo (up to 2 MB). Logos are stored on Laravel's public disk and require the `storage:link` step above.
- Organization records use `organizations.email`, `owner_user_id`, nullable `plan_id`, and nullable `logo_path`; organization memberships use the existing `organization_users` pivot with `role` and `status`. vCards keep their user owner and have a nullable organization relationship; the organization migration backfills existing cards from membership records and uses `nullOnDelete` so records are retained. Assigned plans cannot be deleted until organizations are reassigned.
- Deactivating an organization retains its users and vCards and removes those vCards from organization-member access. Deleting an organization detaches memberships and retains its user accounts and vCards.
- Super Admins manage the vCard template catalog, preview images, prices, and HTML code. Users select templates in account settings, while organization owners and assigned Platform Admins can select one in organization settings. Template prices are displayed for individual selection but do not trigger payments. The Used Count includes direct user selections only; organization selections are not included. Preview pages use sandboxed iframes, and templates assigned to plans cannot be deleted until removed from those plans.
- Tax rates are configurable from Tax Settings. Current plan estimates add each active tax against the base price (additive, not compounded); changing rates does not rewrite historical transactions.
- Standard user forms always create `staff` accounts and do not accept role changes. Superadmins manage roles on the separate user role page; role changes are also enforced on the server.
- Super Admins can assign regular users and platform Admin accounts to multiple organizations from their respective management forms. Organization owners and managers can also create or attach standard users from each organization page.
- User management supports search, role/verification/status filters, pagination, status and email-verification switches, and profile-photo upload/removal. Plans are assigned to organizations, not individual users.
- Profile photos accept JPG, JPEG, PNG, and WebP up to 2 MB, are stored on Laravel's public disk, and require the `storage:link` step above. The default avatar is shown when a photo is not set.
- Account settings allow the signed-in user to edit their own profile, photo, and password. Changing the email marks it unverified until the next successful OTP sign-in.

## Useful commands

```powershell
php artisan migrate
php artisan migrate:fresh --seed
php artisan route:list
php artisan test
```

`migrate:fresh` drops all existing tables in the configured database. Use it only with a disposable local database.

## Front-end

Blade templates live in `resources/views`. Tailwind 4 is compiled through Vite. Dashboard component styles and small UI interactions are in `public/css/admin.css` and `public/js/admin.js`. Font Awesome and the dashboard chart use CDN assets; an internet connection is needed for those third-party visual assets.
