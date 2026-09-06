<?php

namespace BugFinder\Updater;

use BugFinder\Updater\Services\EnvironmentCheckService;
use BugFinder\Updater\Services\ServerCheckService;
use BugFinder\Updater\Services\BackupService;
use BugFinder\Updater\Services\ExtractorService;
use BugFinder\Updater\Services\MigrationRunnerService;

class UpdaterManager
{
    public function __construct(
        protected EnvironmentCheckService $environmentCheckService,
        protected ServerCheckService $serverCheckService,
        protected BackupService $backupService,
        protected ExtractorService $extractorService,
        protected MigrationRunnerService $migrationRunnerService
    ) {}

    public function checkRequirements(): array
    {
        return $this->environmentCheckService->check();
    }

    public function checkForUpdates(?string $purchaseCode = null): array
    {
        return $this->serverCheckService->checkForUpdates($purchaseCode);
    }

    public function backupFiles(?string $version = null): string
    {
        return $this->backupService->createFileBackup($version);
    }

    public function backupDatabase(?string $version = null): string
    {
        return $this->backupService->createDatabaseBackup($version);
    }

    public function getBackupFilePath(string $filename): string
    {
        return $this->backupService->getBackupFilePath($filename);
    }

    public function downloadPackage(string $downloadUrl, string $targetFilePath): bool
    {
        return $this->serverCheckService->downloadPackage($downloadUrl, $targetFilePath);
    }

    public function extractPackage(string $zipFilePath): array
    {
        return $this->extractorService->extractPackage($zipFilePath);
    }

    public function runMigrations(): string
    {
        return $this->migrationRunnerService->runMigrations();
    }

    public function clearCaches(): array
    {
        return $this->migrationRunnerService->clearCaches();
    }

    public function enableMaintenance(): void
    {
        $this->migrationRunnerService->enableMaintenanceMode();
    }

    public function disableMaintenance(): void
    {
        $this->migrationRunnerService->disableMaintenanceMode();
    }

    public function getCurrentVersion(): string
    {
        return $this->serverCheckService->getCurrentVersion();
    }

    public function getPurchaseCode(?string $customCode = null): string
    {
        return $this->serverCheckService->getPurchaseCode($customCode);
    }

    public function updateEnvVersion(string $newVersion): bool
    {
        return $this->migrationRunnerService->updateEnvVersion($newVersion);
    }
}
