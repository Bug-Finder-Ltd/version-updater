<?php

namespace BugFinder\Updater\Services;

use Illuminate\Support\Facades\File;
use ZipArchive;
use Exception;

class ExtractorService
{
    /**
     * Extract update package zip safely into application base path.
     *
     * @param string $zipFilePath
     * @return array Summary of extracted and skipped files
     * @throws Exception
     */
    public function extractPackage(string $zipFilePath): array
    {
        if (!File::exists($zipFilePath)) {
            throw new Exception("Update package zip file not found at: {$zipFilePath}");
        }

        $zip = new ZipArchive();
        if ($zip->open($zipFilePath) !== true) {
            throw new Exception("Failed to open update zip archive.");
        }

        $basePath = realpath(base_path());
        $protectedFiles = config('updater.protected_files', [
            '.env',
            '.htaccess',
            'storage',
            'public/uploads',
            'public/storage',
        ]);

        $extractedFiles = [];
        $skippedFiles = [];

        for ($i = 0; $i < $zip->numFiles; $i++) {
            $filename = $zip->getNameIndex($i);

            // Standardize slashes
            $normalizedFilename = str_replace('\\', '/', $filename);

            // Prevent Zip Slip / Path Traversal attacks
            if (str_contains($normalizedFilename, '../') || str_contains($normalizedFilename, '..\\')) {
                throw new Exception("Security Alert: Malicious file path detected in zip archive: {$filename}");
            }

            // Check if file matches any protected rules
            $isProtected = false;
            foreach ($protectedFiles as $protected) {
                $normalizedProtected = str_replace('\\', '/', $protected);
                if ($normalizedFilename === $normalizedProtected || str_starts_with($normalizedFilename, rtrim($normalizedProtected, '/') . '/')) {
                    $isProtected = true;
                    break;
                }
            }

            if ($isProtected) {
                $skippedFiles[] = $normalizedFilename;
                continue;
            }

            $targetPath = $basePath . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $normalizedFilename);

            // If entry is a directory
            if (str_ends_with($normalizedFilename, '/')) {
                if (!File::exists($targetPath)) {
                    File::makeDirectory($targetPath, 0755, true, true);
                }
                continue;
            }

            // Ensure destination directory exists
            $targetDir = dirname($targetPath);
            if (!File::exists($targetDir)) {
                File::makeDirectory($targetDir, 0755, true, true);
            }

            // Extract file content
            $content = $zip->getFromIndex($i);
            if ($content !== false) {
                File::put($targetPath, $content);
                $extractedFiles[] = $normalizedFilename;
            }
        }

        $zip->close();

        return [
            'extracted_count' => count($extractedFiles),
            'skipped_count' => count($skippedFiles),
            'extracted_files' => $extractedFiles,
            'skipped_files' => $skippedFiles,
        ];
    }
}
