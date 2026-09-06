<?php

namespace BugFinder\Updater\Services;

class EnvironmentCheckService
{
    /**
     * Perform all pre-flight environment checks.
     *
     * @return array
     */
    public function check(): array
    {
        $php = $this->checkPhpVersion();
        $extensions = $this->checkExtensions();
        $permissions = $this->checkPermissions();
        $disk = $this->checkDiskSpace();

        $isPassed = $php['passed'] && $extensions['passed'] && $permissions['passed'] && $disk['passed'];

        return [
            'passed' => $isPassed,
            'php' => $php,
            'extensions' => $extensions,
            'permissions' => $permissions,
            'disk' => $disk,
        ];
    }

    /**
     * Check if PHP version meets minimum requirement.
     *
     * @return array
     */
    public function checkPhpVersion(): array
    {
        $minVersion = config('updater.requirements.php_version', '8.1.0');
        $currentVersion = PHP_VERSION;
        $passed = version_compare($currentVersion, $minVersion, '>=');

        return [
            'current' => $currentVersion,
            'required' => $minVersion,
            'passed' => $passed,
        ];
    }

    /**
     * Check required PHP extensions.
     *
     * @return array
     */
    public function checkExtensions(): array
    {
        $requiredExtensions = config('updater.requirements.extensions', [
            'zip', 'curl', 'pdo', 'mbstring', 'fileinfo', 'openssl'
        ]);

        $results = [];
        $allPassed = true;

        foreach ($requiredExtensions as $ext) {
            $isLoaded = extension_loaded($ext);
            $results[$ext] = $isLoaded;
            if (!$isLoaded) {
                $allPassed = false;
            }
        }

        return [
            'passed' => $allPassed,
            'details' => $results,
        ];
    }

    /**
     * Check folder write permissions.
     *
     * @return array
     */
    public function checkPermissions(): array
    {
        $writablePaths = config('updater.requirements.writable_paths', [
            'storage', 'bootstrap/cache', 'public'
        ]);

        $results = [];
        $allPassed = true;

        foreach ($writablePaths as $path) {
            $fullPath = base_path($path);
            $isWritable = $this->isPathWritable($fullPath);
            $results[$path] = [
                'full_path' => $fullPath,
                'is_writable' => $isWritable,
            ];
            if (!$isWritable) {
                $allPassed = false;
            }
        }

        return [
            'passed' => $allPassed,
            'details' => $results,
        ];
    }

    /**
     * Helper to test actual write permissions on Windows / Linux systems.
     *
     * @param string $fullPath
     * @return bool
     */
    private function isPathWritable(string $fullPath): bool
    {
        if (!is_dir($fullPath)) {
            return false;
        }

        if (@is_writable($fullPath)) {
            return true;
        }

        // Real file write test (works reliably on Windows OS where folder read-only flag is set)
        $testFile = rtrim($fullPath, '/\\') . DIRECTORY_SEPARATOR . '.write_test_' . uniqid();
        $writable = @file_put_contents($testFile, 'test') !== false;
        if ($writable) {
            @unlink($testFile);
        }

        return $writable;
    }

    /**
     * Check available disk space (minimum 100MB required for update process).
     *
     * @return array
     */
    public function checkDiskSpace(): array
    {
        $path = base_path();
        $freeBytes = @disk_free_space($path);
        $minRequiredBytes = 100 * 1024 * 1024; // 100 MB

        if ($freeBytes === false) {
            return [
                'passed' => true, // Warning only if function disabled
                'free_human' => 'Unknown',
                'required_human' => '100 MB',
            ];
        }

        return [
            'passed' => $freeBytes >= $minRequiredBytes,
            'free_bytes' => $freeBytes,
            'free_human' => round($freeBytes / (1024 * 1024), 2) . ' MB',
            'required_human' => '100 MB',
        ];
    }
}
