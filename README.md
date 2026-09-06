# BugFinder Version Updater (`bugfinder/version-updater`)

A robust, secure 1-click version updater package for Laravel products (developed by BugFinder). It provides a Web UI dashboard and CLI command to update Laravel CodeCanyon products effortlessly.

---

## Features

- ⚙️ **Pre-flight Environment Audit**: Verifies PHP version, required extensions, folder write permissions, and disk space.
- 🔒 **Envato License & Purchase Code Check**: Integrates with BugFinder's central update server.
- 💾 **Automated 1-Click Backups**: Creates zip backup of application files and exports MySQL database tables (`.sql`) before applying updates.
- 🛡️ **Zip Extraction with File Preservation**: Safely extracts updated files while skipping protected configuration & upload folders (`.env`, `storage/`, `public/uploads/`, etc.).
- 🚀 **Database & Cache Automation**: Runs database migrations (`php artisan migrate --force`), updates `.env` version, and clears Laravel caches automatically.
- 💻 **Interactive Web UI & CLI Support**: Features an AJAX-driven 1-click update dashboard (`/admin/updater`) as well as an Artisan terminal command (`php artisan bugfinder:update`).

---

## Installation Guide

### Option 1: Via Composer (Local Repository or Private Package)
Add the package to your main product's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../bugfinder-version-updater"
    }
],
"require": {
    "bugfinder/version-updater": "*"
}
```

Then run:
```bash
composer update bugfinder/version-updater
```

### Option 2: Publish Config & Views
```bash
php artisan vendor:publish --tag=bugfinder-updater-config
php artisan vendor:publish --tag=bugfinder-updater-views
```

---

## Configuration (`config/updater.php`)

Set your product details in `.env`:

```env
BUGFINDER_PRODUCT_ID=BF-SCR-01
BUGFINDER_PRODUCT_NAME="BugFinder SaaS Application"
APP_VERSION=1.0.0
BUGFINDER_UPDATE_SERVER_URL=https://update.bugfinder.net/api/v1
BUGFINDER_PURCHASE_CODE=xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
```

---

## Usage

### 1. Web Interface
Navigate to your application admin dashboard at:
`https://yourdomain.com/admin/updater`

Features:
- Check PHP & Extension Requirements
- Enter Envato Purchase Code to query available updates
- Trigger 1-Click Update with real-time AJAX progress status

### 2. Command Line (CLI)
Run the updater from the terminal:
```bash
php artisan bugfinder:update
```

Options:
- `--code=YOUR_PURCHASE_CODE`: Pass purchase code explicitly
- `--skip-backup`: Skip file and database backup phase

---

## Code Architecture

```
src/
├── BugFinderUpdaterServiceProvider.php  # Main Service Provider
├── UpdaterManager.php                   # Central Manager class
├── Facades/
│   └── Updater.php                      # Facade accessor
├── Services/
│   ├── EnvironmentCheckService.php      # Server requirements auditor
│   ├── ServerCheckService.php           # Remote update server communicator
│   ├── BackupService.php                # Zip file & SQL database backup engine
│   ├── ExtractorService.php             # Zip extractor preserving user files
│   └── MigrationRunnerService.php       # Artisan migrations & cache operations
├── Http/
│   └── Controllers/
│       └── UpdaterController.php        # Web UI & AJAX controller
└── Console/
    └── UpdateCommand.php                # CLI command implementation
```

---

## Security Guidelines

1. **Zip Slip Prevention**: The extractor rejects paths with `../` or `..\`.
2. **Protected File Blacklist**: Configurable in `config/updater.php` under `protected_files`.
3. **Maintenance Mode**: Automatically enables maintenance mode (`php artisan down`) during extraction and migration, restoring it afterwards (`php artisan up`).

---

## License

Created by **BugFinder**. Free to use in BugFinder CodeCanyon products.
