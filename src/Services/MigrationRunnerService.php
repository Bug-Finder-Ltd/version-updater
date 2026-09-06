<?php

namespace BugFinder\Updater\Services;

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Exception;

class MigrationRunnerService
{
    /**
     * Enable Maintenance Mode.
     *
     * @return void
     */
    public function enableMaintenanceMode(): void
    {
        try {
            Artisan::call('down', [
                '--secret' => 'bugfinder-update-in-progress',
            ]);
        } catch (Exception $e) {
            // Fallback for older Laravel versions without --secret flag
            Artisan::call('down');
        }
    }

    /**
     * Disable Maintenance Mode.
     *
     * @return void
     */
    public function disableMaintenanceMode(): void
    {
        Artisan::call('up');
    }

    /**
     * Run database migrations.
     *
     * @return string Output log of migration execution
     * @throws Exception
     */
    public function runMigrations(): string
    {
        try {
            Artisan::call('migrate', [
                '--force' => true,
            ]);

            return Artisan::output();
        } catch (Exception $e) {
            throw new Exception("Migration execution failed: " . $e->getMessage());
        }
    }

    /**
     * Clear all application caches.
     *
     * @return array Log of executed cache clear operations
     */
    public function clearCaches(): array
    {
        $logs = [];

        $commands = [
            'config:clear' => 'Config cache cleared',
            'cache:clear'  => 'Application cache cleared',
            'route:clear'  => 'Route cache cleared',
            'view:clear'   => 'View cache cleared',
        ];

        foreach ($commands as $cmd => $label) {
            try {
                Artisan::call($cmd);
                $logs[$cmd] = [
                    'status' => 'success',
                    'message' => $label,
                    'output' => trim(Artisan::output()),
                ];
            } catch (Exception $e) {
                $logs[$cmd] = [
                    'status' => 'error',
                    'message' => $e->getMessage(),
                ];
            }
        }

        return $logs;
    }

    /**
     * Update APP_VERSION in .env file and basic_controls DB table.
     *
     * @param string $newVersion
     * @return bool
     */
    public function updateEnvVersion(string $newVersion): bool
    {
        // 1. Update Database (basic_controls or configured table)
        try {
            $table = config('updater.database.table', 'basic_controls');
            if (Schema::hasTable($table)) {
                $column = config('updater.database.version_column', 'app_version');
                if (!Schema::hasColumn($table, $column)) {
                    $column = 'version';
                }

                if (Schema::hasColumn($table, $column)) {
                    $firstRow = DB::table($table)->first();
                    if ($firstRow && isset($firstRow->id)) {
                        DB::table($table)->where('id', $firstRow->id)->update([$column => $newVersion]);
                    } else {
                        DB::table($table)->update([$column => $newVersion]);
                    }
                }
            }
        } catch (Exception $e) {
            // Log or ignore DB update error, continue to .env
        }

        // 2. Update .env file
        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            return false;
        }

        $content = File::get($envPath);

        if (preg_match('/^APP_VERSION=.*/m', $content)) {
            $content = preg_replace('/^APP_VERSION=.*/m', 'APP_VERSION=' . $newVersion, $content);
        } else {
            $content .= "\nAPP_VERSION=" . $newVersion;
        }

        File::put($envPath, $content);
        return true;
    }
}
