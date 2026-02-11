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

## Database Connection

Configuration is split between two files:
- `/var/www/html/wrightetmathon.ini` — defines `database`, `shopcode`, `branchtype`, `description`
- `application/config/database.php` — reads INI file, sets credentials (`admin` / `Son@Risa&11`)

```bash
# Test database connection
mysql -h localhost -u admin -p'Son@Risa&11' <database_name> -e "SELECT 1;"

# Check active sessions
mysql -h localhost -u admin -p'Son@Risa&11' <database_name> -e "SELECT session_id, FROM_UNIXTIME(last_activity) FROM ospos_sessions ORDER BY last_activity DESC LIMIT 5;"
```

## Session Configuration

Sessions are stored in the database (`ospos_sessions` table). Key settings in `application/config/config.php`:

| Setting | Value | Impact |
|---------|-------|--------|
| `sess_match_useragent` | TRUE | User-Agent must match between requests |
| `sess_use_database` | TRUE | Sessions stored in `ospos_sessions` |
| `sess_expire_on_close` | TRUE | Session expires when browser closes |
| `sess_cookie_name` | `ci_session` | Cookie name |

## Troubleshooting

### 500 Internal Server Error on AJAX calls (customer_search, etc.)

**Symptom:** Console shows `POST .../customer_search 500 (Internal Server Error)`

**Cause:** Session cookie validation failure. Log shows:
```
The session cookie data did not match what was expected. This could be a possible hacking attempt.
```

**Solution:**
```bash
# 1. Clean old/corrupted sessions from database
mysql -h localhost -u admin -p'Son@Risa&11' <database_name> -e "DELETE FROM ospos_sessions WHERE last_activity < UNIX_TIMESTAMP(NOW() - INTERVAL 2 HOUR);"

# 2. User must clear browser cookies and re-login
```

### Invalid Timezone Warning

**Symptom:** Log shows `date_default_timezone_set(): Timezone ID '1' is invalid`

**Cause:** Database config `timezone` contains invalid value ('1' instead of timezone string)

**Location:** `application/hooks/load_config.php:39`

**Fix:** Update timezone value in `ospos_app_config` table:
```sql
UPDATE ospos_app_config SET value = 'Europe/Paris' WHERE key = 'timezone';
```

### Missing notification files

**Symptom:** Warnings about missing files in `/var/www/html/wrightetmathon/notifications/`

**Files expected:** `01_test_notif.php`, `02_test_alerte.php`, `03_test_nouveau.php`, `04_test_astuce.php`

**Impact:** Non-blocking, cosmetic only. Create empty files or update `application/views/partial/header_banner.php` to handle missing files.

### Pole Display Permission Denied

**Symptom:** `fopen(/dev/ttyUSB0): Failed to open stream: Permission denied`

**Fix:**
```bash
sudo usermod -a -G dialout apache
sudo chmod 666 /dev/ttyUSB0
sudo systemctl restart httpd
```

## Application Logs

Logs are stored in `application/logs/` with daily rotation:
```bash
# View today's errors
tail -100 application/logs/log-$(date +%Y-%m-%d).php

# Search for specific errors
grep -i "customer_search\|session" application/logs/log-$(date +%Y-%m-%d).php
```

## Backup

```bash
# Create compressed backup (excludes .git)
cd /var/www/html && tar -czvf /tmp/wrightetmathon_backup_$(date +%Y%m%d_%H%M%S).tar.gz --exclude='.git' wrightetmathon

# Restore
tar -xzvf wrightetmathon_backup_YYYYMMDD_HHMMSS.tar.gz -C /var/www/html/
```

## Modern Theme (January 2026) - Light/Dark Mode

A modern CSS theme inspired by YesAppro with support for light and dark modes.

### Files Added/Modified
- `css/modern-theme.css` — Main theme with CSS variables for light/dark modes
- `css/login.css` — Login page with light/dark support
- `js/theme-toggle.js` — JavaScript for theme switching
- `application/views/partial/head.php` — Includes theme CSS and JS

### Color Palette (YesAppro)

**Light Mode:**
| Variable | Value | Usage |
|----------|-------|-------|
| `--primary` | `#2563eb` | Buttons, links, accents |
| `--secondary` | `#8b5cf6` | Gradients, secondary actions |
| `--success` | `#22c55e` | Success states |
| `--warning` | `#f59e0b` | Warning states |
| `--danger` | `#ef4444` | Error/delete actions |
| `--bg-body` | `#667eea → #764ba2` | Gradient background |
| `--bg-container` | `#ffffff` | Container background |
| `--bg-card` | `#f8fafc` | Card backgrounds |
| `--text-primary` | `#1e293b` | Main text |
| `--border-color` | `#e2e8f0` | Borders |

**Dark Mode:**
| Variable | Value | Usage |
|----------|-------|-------|
| `--primary` | `#3b82f6` | Brighter blue for dark |
| `--bg-body` | `#1e1b4b → #312e81` | Dark gradient |
| `--bg-container` | `#1e293b` | Dark container |
| `--bg-card` | `#334155` | Dark cards |
| `--text-primary` | `#f1f5f9` | Light text |
| `--border-color` | `#475569` | Dark borders |

### Theme Toggle

A floating button (top-right corner) allows users to switch between light and dark modes:
- Preference is saved in `localStorage` (`wm-theme` key)
- Respects system preference (`prefers-color-scheme`) if no manual selection
- Smooth transitions between themes

**JavaScript API:**
```javascript
// Toggle theme
ThemeToggle.toggle();

// Set specific theme
ThemeToggle.setTheme('dark');
ThemeToggle.setTheme('light');

// Get current theme
ThemeToggle.getTheme(); // 'light' or 'dark'
```

### Disabling the Modern Theme
To revert to the original design, comment out in `application/views/partial/head.php`:
```php
<!-- <link rel="stylesheet" type="text/css" href="<?php echo base_url();?>css/modern-theme.css"> -->
<!-- <script src="<?php echo base_url();?>js/theme-toggle.js" type="text/javascript"></script> -->
```

## UI / Graphical Guidelines

These are the established design patterns used across the application. **All new or modified screens must follow these conventions** to maintain visual consistency.

### Color Palette

| Role | Hex | Usage |
|------|-----|-------|
| **Primary Blue** | `#0A6184` | Fieldset borders, button backgrounds, main accent |
| **Primary Blue Alt** | `#106587` | Input borders (register) |
| **Dark Blue** | `#005B7F` | Page titles, footer borders |
| **Table Header** | `#4386a1` | Table header backgrounds (with `cc` transparency suffix) |
| **Register Header** | `#3F839E` | Register table headers |
| **Sorted Header** | `#8dbdd8` | Sorted column indicators in tables |
| **Inner Table Header** | `#679cb2` | Inner/nested table headers (with `a1` transparency) |
| **Body Background** | `#EBF4F8` | Page wrapper background |
| **Footer Background** | `#CDECFA` | Footer strip |
| **Form Field BG** | `#f2f2f2` | Form field container background |
| **Register Area** | `#C2D9D2` | Register panel background |
| **Link Color** | `#276777` | Hyperlinks |

### Status Colors

| Status | Background | Border | Text |
|--------|-----------|--------|------|
| **Success** | `lightgreen` | `3px solid #2ca71c` | black (bold) |
| **Error** | `red` | `3px solid #da3232` | black (bold) |
| **Warning** | `orange` | `3px solid #da3232` | black (bold) |
| **Positive Amount** | — | — | `#27DA16` |
| **Negative Amount** | — | — | `#DA162B` |

### Fieldset Color Coding

Fieldsets use colored borders with matching glow to communicate context:

| Class | Border Color | Box-Shadow | Meaning |
|-------|-------------|------------|---------|
| `.fieldset` | `1px solid #0A6184` | `0 0 15px #0A6184` | Normal / neutral (blue) |
| `.fieldset1` | `3px solid #F30707` | `0 0 15px #F30707` | Error / alert (red) |
| `.fieldset2` | `1px solid #0AEF1D` | `0 0 15px #0AEF1D` | Success / valid (green) |
| `.fieldset3` | `1px solid #DFB60B` | `0 0 15px #DFB60B` | Warning / caution (yellow) |
| `.fieldset4` | `1px solid #0A6184` | none | Neutral, no shadow (blue) |

All fieldsets use `border-radius: 8px` and `padding: 5px`.

### Typography

| Context | Font Family | Size | Weight |
|---------|-------------|------|--------|
| **General UI** | `Arial, sans-serif` | `13px` | normal |
| **Page title** | `Arial, sans-serif` | `50px` | bold |
| **Page subtitle** | `Arial, sans-serif` | `25px` | bold |
| **Form fields (CSS2)** | `Trebuchet MS, Verdana, sans-serif` | `1em` | normal |
| **Register inputs** | `Arial, sans-serif` | `20px` | normal |
| **Footer** | inherits | `11px` | normal |
| **Modern theme** | `-apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif` | `0.875em` | normal |

### Buttons

#### Standard Action Buttons

| Class | Background | Border | Border-Radius | Font |
|-------|-----------|--------|---------------|------|
| `.submit_button` | `#0a6184` (white text) | `2px solid #ddd` | — | `padding: 5px` |
| `.delete_button` | `#ea4729` (white text) | `4px solid #000` | `8px` | bold |
| `.undelete_button` | `#13F511` (black text) | `4px solid #000` | `8px` | `120%`, bold |
| `.barcode_button` | `#13F511` | `4px solid #000` | `8px` | — |

#### Large Action Buttons (Sales Register)

| Class | Background | Width | Height | Border |
|-------|-----------|-------|--------|--------|
| `.big_button_finish` | `greenyellow` | `150px` | `40px` | `1px solid #0A6184`, radius `8px` |
| `.big_button_cancel` | `red` | `150px` | `40px` | `1px solid #0A6184`, radius `8px` |
| `.big_button_invoice` | `greenyellow` | `100%` | `40px` | `1px solid #0A6184`, radius `8px` |
| `.big_button_suspend` | `orange` | `100%` | `40px` | `1px solid #0A6184`, radius `8px` |
| `.big_exit_button` | `yellow` | `150px` | `40px` | — |

All big buttons use `font-size: 16px; font-weight: bold`.

#### Confirm / Customer Buttons

| Class | Background | Font-Size | Hover |
|-------|-----------|----------|-------|
| `.customer_submit_button` | `#0a6184` | `150%` (bold) | `background: #00d9ff`, `text-shadow: 5px 5px 9px black` |
| `.confirm_submit_button` | `#0a6184` | `300%` (bold) | same as above |

Both have `border: 4px solid #A42A2A` and `border-radius: 8px`.

#### CSS2 Modern Buttons

| Class | Background | Color | Border |
|-------|-----------|-------|--------|
| `.btsubmit` | `#43899d` | `#fff` | `1px solid #2C9AD2`, radius `3px` |
| `.btretour` | `#fff` | `#43899d` | `1px solid #43899d`, radius `0` |

Both use `text-transform: uppercase; letter-spacing: 0.1em; min-width: 30%; padding: 6px 12px`.

### Tables (`.tablesorter`)

```
border-collapse: collapse;

Header (thead tr th):
  color: #FFFFFF
  background: #4386a1 (or #4386a1cc with transparency)
  text-align: center
  border-radius: 8px (or 1px)
  padding: 0px 5px

Body (tbody td):
  background: #FFF
  color: #3D3D3D
  border-bottom: 1px solid #DDDDDD
  padding: 0px 5px

Hover (.over):
  background: yellow (or #679cb2)

Selected (.selected):
  background: #BBBBBB

Highlighted row (#line_colour):
  background: lightgreen
```

### Form Inputs

| Context | Border | Border-Radius | Height | Background |
|---------|--------|---------------|--------|-----------|
| **Standard** | `1px solid #ccc` | — | — | `#f2f2f2` (container) |
| **CSS2 modern** | `1px solid #699eb4` | `3px` | `30px` | `#fff` |
| **Required** (`.colorobligatoire`) | `1px solid #cf2b23` | — | — | — |
| **Normal** (`.colornormal`) | `1px solid #699eb4` | — | — | — |
| **Register** | `1px solid #106587` | `8px` | `30px` | — |

Required field labels use `color: red` (class `.required`).

### Layout

| Area | Width | Notes |
|------|-------|-------|
| **Content wrapper** | `95%` centered | Background `#EBF4F8` |
| **Register (left)** | `73%` float left | Cart and items area |
| **Sale panel (right)** | `25%` float right | Totals, payments, actions |
| **Menubar height** | `120px` | Background image `menubar_bg.gif` repeat-x |
| **Menu item width** | `70px` (hover: `90px`) | Image-based icons |

### Notification Messages

```css
.success_message {
  background: lightgreen;
  border: 3px solid #2ca71c;
  border-radius: 8px;
  font-weight: bold;
  text-align: center;
}
.error_message {
  background: red;
  border: 3px solid #da3232;
  border-radius: 8px;
  font-weight: bold;
  text-align: center;
}
.warning_message {
  background: orange;
  border: 3px solid #da3232;
  border-radius: 8px;
  font-weight: bold;
  text-align: center;
}
```

### Icons

The application uses **image-based icons** (PNG), not an icon font:
- Menu icons: `images/menubar/*.png` (sales.png, customers.png, etc.)
- UI icons: `images/icon-calendar.png`, `images/icon-close.png`
- Status: `images/picto_erreur.png`, `images/picto_erreur.gif`
- Modern sections use **inline SVG** (`viewBox="0 0 24 24"`)

### Standard Border-Radius

| Context | Value |
|---------|-------|
| Fieldsets, buttons, messages | `8px` |
| CSS2 inputs, btsubmit | `3px` |
| Exit button (circle) | `12px` |
| Modern theme cards | `6px` |
| Login container (modern) | `16px` |

### Box Shadows

| Context | Value |
|---------|-------|
| Fieldset glow | `0 0 15px [fieldset-color]` |
| Exit button | `1px 1px 3px #000` |
| Modern cards (light) | `0 4px 6px rgba(0, 0, 0, 0.08)` |
| Modern cards (heavy) | `0 20px 60px rgba(0, 0, 0, 0.15)` |
| Input inset (CSS2) | `0 1px 1px rgba(0, 0, 0, 0.075) inset` |

### Sales Register Slider (SLIDES_VENTES)

The register page includes an image carousel with 9 slides:

```css
div#slider { width: 335px; height: 335px; overflow: hidden; }
div#slider figure {
  width: 900%;          /* 9 slides x 100% */
  animation: 45s slidy infinite;   /* 5s per slide */
}
div#slider figure img { width: 11.111%; }  /* 100% / 9 */
```

Images are loaded from `SLIDES_VENTES/slide1.png` through `slide9.png` with cache-busting query string (`?v=<timestamp>`) set at sync time.

### PHP 8.3 Compatibility Rules

When modifying any code, follow these rules to avoid fatal errors:

1. **Never assign properties on potentially null objects** — always initialize with `new stdClass()` before property assignment
2. **Never use `count()` on non-arrays** — use `->num_rows()` for CI database results
3. **Guard all divisions** — use ternary checks before any division (`$divisor > 0 ? $a / $divisor : 0`)
4. **Check `count()` parentheses** — the pattern `count($array > $limit)` is a bug; correct form is `count($array) > $limit`
