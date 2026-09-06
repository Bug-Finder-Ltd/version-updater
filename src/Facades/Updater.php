<?php

namespace BugFinder\Updater\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static array checkRequirements()
 * @method static array checkForUpdates(?string $purchaseCode = null)
 * @method static string backupFiles(?string $version = null)
 * @method static string backupDatabase(?string $version = null)
 * @method static bool downloadPackage(string $downloadUrl, string $targetFilePath)
 * @method static array extractPackage(string $zipFilePath)
 * @method static string runMigrations()
 * @method static array clearCaches()
 * 
 * @see \BugFinder\Updater\UpdaterManager
 */
class Updater extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'bugfinder.updater';
    }
}
