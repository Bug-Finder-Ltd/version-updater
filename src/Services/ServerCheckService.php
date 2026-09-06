<?php

namespace BugFinder\Updater\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class ServerCheckService
{
    /**
     * Check remote server for available updates.
     *
     * @param string|null $purchaseCode
     * @return array
     * @throws Exception
     */
    public function checkForUpdates(?string $purchaseCode = null): array
    {
        $serverUrl = config('updater.server_url');
        $currentVersion = $this->getCurrentVersion();
        $domain = config('updater.domain_override') ?? env('BUGFINDER_TEST_DOMAIN') ?? request()->getHost();

        if (empty($serverUrl)) {
            throw new Exception("Updater server URL is not configured.");
        }

        // Ensure endpoint resolves to /api/product/version/update
        $endpoint = rtrim($serverUrl, '/');
        if (!str_contains($endpoint, 'product/version/update')) {
            if (!str_contains($endpoint, '/api')) {
                $endpoint .= '/api';
            }
            $endpoint .= '/product/version/update';
        }

        $queryParams = [
            'domain' => $domain,
            'version' => $currentVersion,
            'purchase_code' => $this->getPurchaseCode($purchaseCode),
        ];

        try {
            $response = Http::timeout(30)->get($endpoint, $queryParams);

            if ($response->failed()) {
                $errorMsg = $response->json('message') ?? 'Failed to connect to the update server (HTTP ' . $response->status() . ').';
                throw new Exception($errorMsg);
            }

            $data = $response->json();

            if (($data['status'] ?? '') === 'error') {
                throw new Exception($data['message'] ?? 'Update check failed.');
            }

            $downloadUrl = $data['download_url'] ?? null;
            $hasUpdate = !empty($downloadUrl);

            return [
                'has_update' => $hasUpdate,
                'current_version' => $currentVersion,
                'latest_version' => $data['latest_version'] ?? $currentVersion,
                'release_date' => $data['release_date'] ?? null,
                'changelog' => $data['update_log'] ?? ($data['message'] ?? ''),
                'download_url' => $downloadUrl,
                'raw' => $data,
            ];
        } catch (Exception $e) {
            throw new Exception($e->getMessage());
        }
    }

    /**
     * Download the update package zip file from the remote server.
     *
     * @param string $downloadUrl
     * @param string $targetFilePath
     * @return bool
     * @throws Exception
     */
    public function downloadPackage(string $downloadUrl, string $targetFilePath): bool
    {
        $directory = dirname($targetFilePath);

        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true, true);
        }

        try {
            $response = Http::timeout(300)->sink($targetFilePath)->get($downloadUrl);

            if ($response->failed()) {
                if (File::exists($targetFilePath)) {
                    File::delete($targetFilePath);
                }
                throw new Exception("Failed to download update package. HTTP status: " . $response->status());
            }

            if (!File::exists($targetFilePath) || File::size($targetFilePath) === 0) {
                throw new Exception("Downloaded file is empty or missing.");
            }

            return true;
        } catch (Exception $e) {
            if (File::exists($targetFilePath)) {
                File::delete($targetFilePath);
            }
            throw new Exception("Download failed: " . $e->getMessage());
        }
    }

    /**
     * Resolve the current installed application version.
     *
     * @return string
     */
    public function getCurrentVersion(): string
    {
        // 0. Test override from config or ENV (used for local testing)
        $testVersion = config('updater.version_override') ?? env('BUGFINDER_TEST_VERSION');
        if (!empty($testVersion)) {
            return $testVersion;
        }

        // 1. Resolver closure from config
        $resolver = config('updater.current_version_resolver');
        if (is_callable($resolver)) {
            return call_user_func($resolver);
        }

        // 2. Query basic_controls (or configured table) in Database
        $table = config('updater.database.table', 'basic_controls');
        if (Schema::hasTable($table)) {
            $column = config('updater.database.version_column', 'app_version');
            if (!Schema::hasColumn($table, $column)) {
                $column = 'version'; // Fallback column name
            }

            if (Schema::hasColumn($table, $column)) {
                $dbVersion = DB::table($table)->value($column);
                if (!empty($dbVersion)) {
                    return $dbVersion;
                }
            }
        }

        // 3. Config / ENV fallback
        return config('updater.current_version') ?? env('APP_VERSION', '1.0.0');
    }

    /**
     * Resolve the buyer's purchase code.
     *
     * @param string|null $customCode
     * @return string
     */
    public function getPurchaseCode(?string $customCode = null): string
    {
        if (!empty($customCode)) {
            return $customCode;
        }

        // 1. Resolver closure from config
        $resolver = config('updater.purchase_code_resolver');
        if (is_callable($resolver)) {
            return call_user_func($resolver);
        }

        // 2. Query basic_controls (or configured table) in Database
        $table = config('updater.database.table', 'basic_controls');
        if (Schema::hasTable($table)) {
            $column = config('updater.database.purchase_code_column', 'purchase_code');
            if (!Schema::hasColumn($table, $column)) {
                $column = 'license_code'; // Fallback column name
            }

            if (Schema::hasColumn($table, $column)) {
                $dbCode = DB::table($table)->value($column);
                if (!empty($dbCode)) {
                    return $dbCode;
                }
            }
        }

        // 3. Config / ENV fallback
        return config('updater.purchase_code') ?? env('BUGFINDER_PURCHASE_CODE', '');
    }
}
