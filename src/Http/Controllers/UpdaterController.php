<?php

namespace BugFinder\Updater\Http\Controllers;

use Illuminate\Routing\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use BugFinder\Updater\UpdaterManager;
use Exception;

class UpdaterController extends Controller
{
    public function __construct(protected UpdaterManager $updater) {}

    /**
     * Render the main Updater Web Dashboard page.
     */
    public function index()
    {
        $currentVersion = $this->updater->getCurrentVersion();
        $purchaseCode = $this->updater->getPurchaseCode();
        $productName = config('updater.product_name', 'BugFinder Application');

        return view('updater::index', compact('currentVersion', 'purchaseCode', 'productName'));
    }

    /**
     * Check system requirements & permissions.
     */
    public function checkRequirements(): JsonResponse
    {
        try {
            $requirements = $this->updater->checkRequirements();
            return response()->json([
                'success' => true,
                'data' => $requirements,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check remote server for update availability.
     */
    public function checkUpdate(Request $request): JsonResponse
    {
        try {
            $purchaseCode = $this->updater->getPurchaseCode($request->input('purchase_code'));
            $updateData = $this->updater->checkForUpdates($purchaseCode);

            return response()->json([
                'success' => true,
                'data' => $updateData,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Trigger file and database backup.
     */
    public function createBackup(): JsonResponse
    {
        try {
            $fileBackup = $this->updater->backupFiles();
            $dbBackup = $this->updater->backupDatabase();

            $fileBackupName = basename($fileBackup);
            $dbBackupName = basename($dbBackup);

            return response()->json([
                'success' => true,
                'message' => 'File and Database backup created successfully.',
                'file_backup' => $fileBackupName,
                'db_backup' => $dbBackupName,
                'file_backup_url' => route('bugfinder.updater.download-backup', ['file' => $fileBackupName]),
                'db_backup_url' => route('bugfinder.updater.download-backup', ['file' => $dbBackupName]),
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Backup failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Download generated backup file (Zip or SQL).
     */
    public function downloadBackup(Request $request)
    {
        $file = $request->input('file');
        if (empty($file)) {
            abort(404, 'Backup filename required.');
        }

        try {
            $filePath = $this->updater->getBackupFilePath($file);
            return response()->download($filePath);
        } catch (Exception $e) {
            abort(404, $e->getMessage());
        }
    }

    /**
     * Download update package from remote URL.
     */
    public function downloadPackage(Request $request): JsonResponse
    {
        $request->validate([
            'download_url' => 'required|url',
            'version' => 'required|string',
        ]);

        try {
            $version = $request->input('version');
            $downloadUrl = $request->input('download_url');

            $tempDir = config('updater.update_temp_path');
            $targetPath = $tempDir . "/update-v{$version}.zip";

            $this->updater->downloadPackage($downloadUrl, $targetPath);

            return response()->json([
                'success' => true,
                'message' => "Update package v{$version} downloaded successfully.",
                'file_path' => $targetPath,
            ]);
        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Download failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Extract update package, run database migrations & clear cache.
     */
    public function extractAndMigrate(Request $request): JsonResponse
    {
        $request->validate([
            'version' => 'required|string',
        ]);

        $version = $request->input('version');
        $tempDir = config('updater.update_temp_path');
        $zipPath = $tempDir . "/update-v{$version}.zip";

        try {
            // Enable maintenance mode during update
            $this->updater->enableMaintenance();

            // Extract zip
            $extractionResult = $this->updater->extractPackage($zipPath);

            // Run database migrations
            $migrationOutput = $this->updater->runMigrations();

            // Update APP_VERSION in .env
            $this->updater->updateEnvVersion($version);

            // Clear cache
            $cacheLogs = $this->updater->clearCaches();

            // Disable maintenance mode
            $this->updater->disableMaintenance();

            return response()->json([
                'success' => true,
                'message' => "Application updated to v{$version} successfully!",
                'extraction' => $extractionResult,
                'migration' => $migrationOutput,
                'cache' => $cacheLogs,
            ]);
        } catch (Exception $e) {
            // Always turn maintenance mode off if an exception occurs
            $this->updater->disableMaintenance();

            return response()->json([
                'success' => false,
                'message' => 'Update failed: ' . $e->getMessage(),
            ], 500);
        }
    }
}
