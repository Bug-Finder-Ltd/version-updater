<?php

namespace BugFinder\Updater\Console;

use Illuminate\Console\Command;
use BugFinder\Updater\UpdaterManager;
use Exception;

class UpdateCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'bugfinder:update {--code= : Optional Envato Purchase Code} {--skip-backup : Skip automatic file and DB backup}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check and apply 1-click version updates for BugFinder application.';

    public function __construct(protected UpdaterManager $updater)
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("==============================================");
        $this->info("      BugFinder Automatic Application Updater  ");
        $this->info("==============================================");

        $currentVersion = config('updater.current_version', '1.0.0');
        $this->line("Current App Version: <comment>v{$currentVersion}</comment>");

        // Step 1: Pre-flight checks
        $this->line("\n[1/5] Checking Server Environment...");
        $env = $this->updater->checkRequirements();
        if (!$env['passed']) {
            $this->error("Environment checks failed! Please check PHP version, extensions, or folder permissions.");
            return Command::FAILURE;
        }
        $this->info("✔ Environment checks passed.");

        // Step 2: Check for updates
        $this->line("\n[2/5] Checking Remote Server for Updates...");
        $purchaseCode = $this->option('code') ?? config('updater.purchase_code');

        try {
            $updateInfo = $this->updater->checkForUpdates($purchaseCode);
        } catch (Exception $e) {
            $this->error("Update check failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        if (empty($updateInfo['has_update'])) {
            $this->info("🎉 Your application is already up to date (v{$currentVersion}).");
            return Command::SUCCESS;
        }

        $newVersion = $updateInfo['latest_version'];
        $downloadUrl = $updateInfo['download_url'];

        $this->alert("New version available: v{$newVersion}");
        if (!empty($updateInfo['changelog'])) {
            $this->line("<comment>Changelog:</comment>\n" . $updateInfo['changelog']);
        }

        if (!$this->confirm("Do you want to proceed with updating to v{$newVersion}?", true)) {
            $this->warn("Update cancelled by user.");
            return Command::SUCCESS;
        }

        // Step 3: Create Backups
        if (!$this->option('skip-backup')) {
            $this->line("\n[3/5] Creating Application File & Database Backup...");
            try {
                $fileZip = $this->updater->backupFiles($currentVersion);
                $dbSql = $this->updater->backupDatabase($currentVersion);
                $this->info("✔ Backup created successfully:");
                $this->line("  - File Zip: " . basename($fileZip));
                $this->line("  - DB SQL: " . basename($dbSql));
            } catch (Exception $e) {
                $this->error("Backup failed: " . $e->getMessage());
                if (!$this->confirm("Do you want to continue update WITHOUT backup?", false)) {
                    return Command::FAILURE;
                }
            }
        } else {
            $this->warn("Skipping backup step as requested.");
        }

        // Step 4: Download package
        $this->line("\n[4/5] Downloading Update Package (v{$newVersion})...");
        $tempPath = config('updater.update_temp_path') . "/update-v{$newVersion}.zip";

        try {
            $this->updater->downloadPackage($downloadUrl, $tempPath);
            $this->info("✔ Package downloaded successfully.");
        } catch (Exception $e) {
            $this->error("Download failed: " . $e->getMessage());
            return Command::FAILURE;
        }

        // Step 5: Extract & Migrate
        $this->line("\n[5/5] Extracting Update Files & Running Migrations...");
        try {
            $this->updater->enableMaintenance();

            $extraction = $this->updater->extractPackage($tempPath);
            $this->info("✔ Extracted {$extraction['extracted_count']} files (Skipped {$extraction['skipped_count']} protected files).");

            $this->line("Running database migrations...");
            $migrationLog = $this->updater->runMigrations();
            $this->line($migrationLog);

            $this->updater->updateEnvVersion($newVersion);
            $this->updater->clearCaches();

            $this->updater->disableMaintenance();

            $this->info("\n==============================================");
            $this->info(" 🎉 Update Completed Successfully! (v{$newVersion})");
            $this->info("==============================================");

            return Command::SUCCESS;
        } catch (Exception $e) {
            $this->updater->disableMaintenance();
            $this->error("\n❌ Update process failed: " . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
