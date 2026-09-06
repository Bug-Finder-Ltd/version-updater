<?php

namespace BugFinder\Updater\Services;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use ZipArchive;
use Exception;
use RecursiveIteratorIterator;
use RecursiveDirectoryIterator;

class BackupService
{
    /**
     * Create a compressed zip backup of application files.
     *
     * @param string|null $version
     * @return string Path to the created zip file
     * @throws Exception
     */
    public function createFileBackup(?string $version = null): string
    {
        @set_time_limit(600);
        @ini_set('max_execution_time', '600');
        @ini_set('memory_limit', '512M');

        if (!extension_loaded('zip')) {
            throw new Exception("PHP Zip extension is required for file backup.");
        }

        $version = $version ?? config('updater.current_version', '1.0.0');
        $backupDir = config('updater.backup_path');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $filename = "file-backup-v{$version}-" . date('Y-m-d-His') . ".zip";
        $zipPath = $backupDir . '/' . $filename;

        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            throw new Exception("Unable to create backup zip file at: {$zipPath}");
        }

        $basePath = rtrim(str_replace('\\', '/', base_path()), '/');

        $excludeTopDirs = [
            'vendor',
            'node_modules',
            '.git',
            'storage',
            'public/uploads',
            'public/storage',
            'public/assets/uploads',
        ];

        $directory = new \RecursiveDirectoryIterator($basePath, \RecursiveDirectoryIterator::SKIP_DOTS);

        $filter = new \RecursiveCallbackFilterIterator($directory, function ($current, $key, $iterator) use ($basePath, $excludeTopDirs, $zipPath) {
            $path = str_replace('\\', '/', $current->getRealPath() ?: $current->getPathname());
            $relativePath = ltrim(substr($path, strlen($basePath)), '/');
            $lowerRelative = strtolower($relativePath);

            // Do not enter excluded directories
            foreach ($excludeTopDirs as $exclude) {
                $lowerExclude = strtolower($exclude);
                if ($lowerRelative === $lowerExclude || str_starts_with($lowerRelative, $lowerExclude . '/')) {
                    return false;
                }
            }

            return true;
        });

        $iterator = new \RecursiveIteratorIterator($filter, \RecursiveIteratorIterator::LEAVES_ONLY);

        foreach ($iterator as $file) {
            if ($file->isFile() && $file->isReadable()) {
                $filePath = $file->getRealPath();
                if (!$filePath) {
                    continue;
                }

                $relativePath = ltrim(substr(str_replace('\\', '/', $filePath), strlen($basePath)), '/');
                $zip->addFile($filePath, $relativePath);
            }
        }

        $closeResult = $zip->close();

        if ($closeResult === false || !File::exists($zipPath) || File::size($zipPath) === 0) {
            if (File::exists($zipPath)) {
                @File::delete($zipPath);
            }
            throw new Exception("Failed to finalize file backup zip. Ensure storage directory is writable.");
        }

        return $zipPath;
    }

    /**
     * Export database tables to an SQL dump file.
     *
     * @param string|null $version
     * @return string Path to the created SQL dump file
     * @throws Exception
     */
    public function createDatabaseBackup(?string $version = null): string
    {
        @set_time_limit(600);
        @ini_set('max_execution_time', '600');
        @ini_set('memory_limit', '512M');

        $version = $version ?? config('updater.current_version', '1.0.0');
        $backupDir = config('updater.backup_path');

        if (!File::exists($backupDir)) {
            File::makeDirectory($backupDir, 0755, true, true);
        }

        $filename = "db-backup-v{$version}-" . date('Y-m-d-His') . ".sql";
        $sqlPath = $backupDir . '/' . $filename;

        try {
            $tables = DB::select('SHOW TABLES');
            $dbNameKey = 'Tables_in_' . DB::getDatabaseName();

            $sqlContent = "-- BugFinder Auto-Updater Database Backup\n";
            $sqlContent .= "-- Version: {$version}\n";
            $sqlContent .= "-- Generated: " . date('Y-m-d H:i:s') . "\n\n";
            $sqlContent .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            foreach ($tables as $table) {
                $tableName = $table->$dbNameKey ?? current((array)$table);

                // Get CREATE TABLE statement
                $createTableStmt = DB::select("SHOW CREATE TABLE `{$tableName}`");
                $createSql = $createTableStmt[0]->{'Create Table'} ?? '';
                $sqlContent .= "DROP TABLE IF EXISTS `{$tableName}`;\n";
                $sqlContent .= $createSql . ";\n\n";

                // Get table rows
                $rows = DB::table($tableName)->get();
                foreach ($rows as $row) {
                    $rowArray = (array)$row;
                    $values = array_map(function ($value) {
                        if (is_null($value)) {
                            return 'NULL';
                        }
                        return DB::getPdo()->quote($value);
                    }, array_values($rowArray));

                    $columns = array_map(function ($col) {
                        return "`{$col}`";
                    }, array_keys($rowArray));

                    $sqlContent .= "INSERT INTO `{$tableName}` (" . implode(', ', $columns) . ") VALUES (" . implode(', ', $values) . ");\n";
                }
                $sqlContent .= "\n";
            }

            $sqlContent .= "SET FOREIGN_KEY_CHECKS=1;\n";

            File::put($sqlPath, $sqlContent);

            return $sqlPath;
        } catch (Exception $e) {
            throw new Exception("Database backup failed: " . $e->getMessage());
        }
    }

    /**
     * Resolve and validate absolute backup file path safely.
     *
     * @param string $filename
     * @return string
     * @throws Exception
     */
    public function getBackupFilePath(string $filename): string
    {
        // Sanitize filename to prevent directory traversal
        $sanitized = basename($filename);
        $backupDir = config('updater.backup_path');
        $fullPath = $backupDir . '/' . $sanitized;

        if (!File::exists($fullPath)) {
            throw new Exception("Backup file not found: {$sanitized}");
        }

        return $fullPath;
    }
}
