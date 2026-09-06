<?php

namespace BugFinder\Updater;

use Illuminate\Support\ServiceProvider;
use BugFinder\Updater\Console\UpdateCommand;
use BugFinder\Updater\Services\EnvironmentCheckService;
use BugFinder\Updater\Services\ServerCheckService;
use BugFinder\Updater\Services\BackupService;
use BugFinder\Updater\Services\ExtractorService;
use BugFinder\Updater\Services\MigrationRunnerService;

class BugFinderUpdaterServiceProvider extends ServiceProvider
{
    /**
     * Register package services in the container.
     */
    public function register(): void
    {
        // Merge default configuration
        $this->mergeConfigFrom(
            __DIR__ . '/../config/updater.php', 'updater'
        );

        // Bind Services
        $this->app->singleton(EnvironmentCheckService::class);
        $this->app->singleton(ServerCheckService::class);
        $this->app->singleton(BackupService::class);
        $this->app->singleton(ExtractorService::class);
        $this->app->singleton(MigrationRunnerService::class);

        // Bind UpdaterManager
        $this->app->singleton('bugfinder.updater', function ($app) {
            return new UpdaterManager(
                $app->make(EnvironmentCheckService::class),
                $app->make(ServerCheckService::class),
                $app->make(BackupService::class),
                $app->make(ExtractorService::class),
                $app->make(MigrationRunnerService::class)
            );
        });
    }

    /**
     * Bootstrap package services, routes, views, and commands.
     */
    public function boot(): void
    {
        // Load Routes
        $this->loadRoutesFrom(__DIR__ . '/../routes/web.php');

        // Load Views
        $this->loadViewsFrom(__DIR__ . '/../resources/views', 'updater');

        // Register Console Commands
        if ($this->app->runningInConsole()) {
            $this->commands([
                UpdateCommand::class,
            ]);

            // Publish Configuration
            $this->publishes([
                __DIR__ . '/../config/updater.php' => config_path('updater.php'),
            ], 'bugfinder-updater-config');

            // Publish Views
            $this->publishes([
                __DIR__ . '/../resources/views' => resource_path('views/vendor/updater'),
            ], 'bugfinder-updater-views');
        }
    }
}
