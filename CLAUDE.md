# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

Wright et Mathon POS — an enterprise retail Point of Sale system (current version V14.2.3). Built with **PHP 8.3** on the **CodeIgniter 2.x** framework, using **MySQL/MySQLi** with table prefix `ospos_`. The frontend uses **jQuery 3.3.1** with jQuery UI 1.12.1. The primary language context is **French** (France-based retail, Europe/Paris timezone fallback).

## Running the Application

The app runs on Apache (`httpd` service) and is accessed at `http://localhost/wrightetmathon`. The startup script `wm.sh` clears caches, installs fonts, and launches Chromium in app mode. There is no formal build step, test suite, or linter configured — the application is deployed directly as PHP source files served by Apache.

**Database configuration** is read from `/var/www/html/wrightetmathon.ini` (external to the repo), which provides hostname and database name. The connection is configured in `application/config/database.php`.

## Architecture

### MVC Structure (CodeIgniter 2.x conventions)

- **Entry point:** `index.php` — front controller that bootstraps `system/core/CodeIgniter.php`
- **Default route:** `login` controller (`application/config/routes.php`)
- **Controllers:** `application/controllers/` (56 files)
- **Models:** `application/models/` (41 files)
- **Views:** `application/views/` (PHP templates organized by module)
- **Libraries:** `application/libraries/` (custom business logic)
- **Helpers:** `application/helpers/` (utility functions)
- **Config:** `application/config/` (framework and app configuration)

### Controller Hierarchy

All controllers extend `CI_Controller` directly. Two base controllers provide shared behavior:
- `Common_controller` — base for data-oriented controllers; provides `common_exit()` which cleans up session state and redirects based on `$_SESSION['origin']` codes (`AS`, `II`, `DR`, `SA`, `CA`, etc.)
- `Person_controller` — base for person-related entities (customers, employees, suppliers); provides `mailto()`, `suggest()`, and `get_row()` AJAX endpoints

### Key Controllers by Domain

| Domain | Controller | Notes |
|--------|-----------|-------|
| Authentication | `login.php` | Session-based auth, pole display integration |
| Sales/POS | `sales.php` (~5100 lines) | Core POS operations, cart, checkout |
| Reports | `reports.php` (~5900 lines) | All reporting and analytics |
| Products | `items.php` (~4600 lines) | Product/item CRUD and search |
| Customers | `customers.php` (~2200 lines) | Customer management |
| Purchasing | `receivings.php` (~1700 lines) | Purchase orders and receiving |
| Cash Registers | `cashtills.php` (~1200 lines) | Till management |
| APIs | `apis.php` | External API endpoints |

### Auto-loaded Resources

Defined in `application/config/autoload.php` — everything listed here is available in every controller without explicit loading:

- **Libraries:** database, form_validation, session, user_agent, pagination, Receiving_lib, Sale_lib, Pole_display, Upload
- **Models:** 33 models including Appconfig, Customer, Employee, Item, Sale, Transaction, Common_routines, Stock_queries, and all entity models
- **Helpers:** form, url, table, text, currency, html, download, report
- **Language files:** 28 localization files (English and French in `application/language/`)

### Hook: Configuration Loading

A `post_controller_constructor` hook (`application/hooks/load_config.php`) runs on every request:
1. Initializes session objects (`$_SESSION['G']`, `$_SESSION['C']`, `$_SESSION['M']`) as stdClass for PHP 8 compatibility
2. Loads all rows from the `ospos_app_config` table into CI's config system via the `Appconfig` model
3. Reloads language files if language changed
4. Sets timezone (defaults to `Europe/Paris`)

### Session State Conventions

The app uses `$_SESSION` extensively with specific object keys:
- `$_SESSION['G']` — Global/general session data (e.g., `login_employee_id`, `login_employee_username`)
- `$_SESSION['C']` — Customer-related session data
- `$_SESSION['M']` — Module-related session data
- `$_SESSION['origin']` — Two-letter code tracking navigation origin for redirect logic
- `$_SESSION['module_id']` — Current module identifier for access control

### Key Libraries

- `Sale_lib.php` — Cart management, discounts, payment processing logic
- `Receiving_lib.php` — Purchase order business logic
- `Pole_display.php` — Customer-facing POS display hardware integration
- `Escpos.php` — ESC/POS thermal receipt printer control

### Key Helpers

- `table_helper.php` (~69KB) — Generates HTML tables for all data views; the largest helper
- `currency_helper.php` — Currency formatting and conversion
- `report_helper.php` — Report generation utilities

### Database

- **Driver:** MySQLi with Active Record pattern
- **Prefix:** All tables use `ospos_` prefix
- **Key tables** (inferred from models): items, customers, employees, suppliers, sales, receivings, transactions, inventory, categories, branches, warehouses, gift_cards, cashtills, currencies, payment_methods

### Third-Party Libraries (bundled in repo)

- **PHPMailer** — SMTP email
- **PHPExcel** — Excel import/export
- **mPDF** — PDF generation
- **escpos-php** — Receipt printer protocol
- **Symfony components** — Various utilities (mail, HTTP, routing, security, translation, validation)

### Localization

Two languages supported: English and French. Language files are in `application/language/{english,french}/`. All 28 language file sets are auto-loaded. The active language is stored in the database (`ospos_app_config` table) and applied on every request via the config hook.

### Hardware Integrations

The system integrates with POS hardware:
- **Pole display** (customer-facing screen) via serial port — see `Pole_display.php`
- **ESC/POS receipt printers** — see `Escpos.php` library and `escpos-php` third-party
- **Barcode scanners** — input handled via standard keyboard wedge
- **VapeSelf vending machine** — dedicated controller and model (`vapeself.php`, `vapeself_model.php`)

## Development Notes

- The codebase has no package manager (no `composer.json` or `package.json`), no test framework, and no CI/CD pipeline. Changes are deployed by editing PHP files directly.
- Version history is tracked in `version.ini` with a detailed French-language changelog.
- Backup/old files exist throughout (e.g., `items_30.php`, `*_old.php`, `*.bak`) — these are legacy copies, not active code.
- The `application/config/database.php` reads from an external INI file at `/var/www/html/wrightetmathon.ini` for database name and hostname.
- Environment is set to `production` in `index.php` (errors suppressed).
