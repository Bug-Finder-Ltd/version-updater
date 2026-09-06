<?php

use Illuminate\Support\Facades\Route;
use BugFinder\Updater\Http\Controllers\UpdaterController;

$prefix = config('updater.route_prefix', 'admin/updater');
$middleware = config('updater.middleware', ['web', 'auth']);

Route::group([
    'prefix' => $prefix,
    'middleware' => $middleware,
    'as' => 'bugfinder.updater.',
], function () {
    Route::get('/', [UpdaterController::class, 'index'])->name('index');
    Route::get('/check-requirements', [UpdaterController::class, 'checkRequirements'])->name('check-requirements');
    Route::post('/check-update', [UpdaterController::class, 'checkUpdate'])->name('check-update');
    Route::post('/create-backup', [UpdaterController::class, 'createBackup'])->name('create-backup');
    Route::get('/download-backup', [UpdaterController::class, 'downloadBackup'])->name('download-backup');
    Route::post('/download-package', [UpdaterController::class, 'downloadPackage'])->name('download-package');
    Route::post('/run-update', [UpdaterController::class, 'extractAndMigrate'])->name('run-update');
});
