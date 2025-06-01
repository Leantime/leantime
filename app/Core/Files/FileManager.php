<?php

namespace Leantime\Core\Files;

use Illuminate\Contracts\Filesystem\Filesystem;
use Illuminate\Filesystem\FilesystemManager;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Files\Contracts\FileManagerInterface;
use Leantime\Core\Files\Exceptions\FileValidationException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * FileManager - Service for handling file operations using Laravel's filesystem
 */
class FileManager implements FileManagerInterface
{
    private FilesystemManager $filesystemManager;

    private Environment $config;

    /**
     * Constructor
     */
    public function __construct(
        FilesystemManager $filesystemManager,
        Environment $config,
    ) {
        $this->filesystemManager = $filesystemManager;
        $this->config = $config;
    }

    /**
     * Sanitize filename to prevent path traversal and other security issues
     *
     * @param  string  $filename  The filename to sanitize
     * @return string Sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove any directory paths
        $filename = basename($filename);

        // Remove special characters that could be problematic
        return preg_replace('/[^a-zA-Z0-9_.-]/', '_', $filename);
    }

    /**
     * Validates a file before upload
     *
     * @param  UploadedFile  $file  The file to validate
     * @param  string  $module  The module name
     *
     * @throws FileValidationException If the file is invalid
     */
    private function validateFile(UploadedFile $file, string $module = 'default'): void
    {
        // Check if file is valid
        if (! $file->isValid() || $file->getError()) {
            Log::error('Invalid file upload attempt: '.$file->getErrorMessage());
            throw new FileValidationException('Invalid file upload attempt: '.$file->getErrorMessage(), FileValidationException::INVALID_FILE);
        }

        // Check file size (10MB max by default, can be configured)
        $maxSize = self::getMaximumFileUploadSize();
        if ($file->getSize() > $maxSize) {
            throw new FileValidationException('File size exceeds the maximum allowed size of '.format($maxSize)->formatBytes(), FileValidationException::FILE_TOO_LARGE);
        }

        //        // Sanitize the real name for security
        //        $originalName = $file->getClientOriginalName();
        //        if ($this->sanitizeFilename($originalName) !== $originalName) {
        //            throw new FileValidationException('Filename contains invalid characters', FileValidationException::INVALID_FILE);
        //        }

    }

    /**
     * Upload a file
     *
     * @param  UploadedFile  $file  The file to upload
     * @param  string  $disk  The disk to use for storage
     * @return array|false Array with file info or false on failure
     */
    public function upload(UploadedFile $file, $disk = 'default'): array|false
    {
        try {
            // Validate file before proceeding
            $this->validateFile($file, $disk);

            $extension = $file->getClientOriginalExtension();
            $realName = $this->sanitizeFilename($file->getClientOriginalName());
            $fileName = $realName;

            if ($disk === 'default') {
                $disk = $this->filesystemManager->getDefaultDriver();
            }

            $visibility = null;
            if ($disk === 'public' && $this->config->useS3) {
                $disk = 's3';
                $visibility = 'public';
            }

            $storage = $this->filesystemManager->disk($disk);

            if (config('filesystems.disks.'.$disk.'.renameFiles')) {
                $newName = md5(session('userdata.id').time());
                $fileName = $newName.'.'.$extension;
            }

            // Store the file
            $stream = fopen($file->getRealPath(), 'r');
            $result = $storage->put($fileName, $stream, $visibility);
            if (is_resource($stream)) {
                fclose($stream);
            }

            return [
                'encName' => $newName,
                'realName' => $realName,
                'extension' => $extension,
                'fileName' => $fileName,
                'newPath' => $newName.'.'.$extension,
                'path' => $fileName,
                'moduleId' => '',
                'module' => '',
                'userId' => session('userdata.id'),
                'fileId' => '',
                'uploadTime' => time(),
            ];

        } catch (\Exception $e) {
            // Enhanced error logging
            Log::error('File upload failed: '.$e->getMessage(), [
                'exception' => $e,
                'file' => $file->getClientOriginalName(),
            ]);

            return false;
        }
    }

    /**
     * Get a file
     *
     * @param  string  $fileName  The file name (with extension)
     * @param  string  $realName  The original file name (for Content-Disposition)
     * @param  string  $disk  The disk to use for storage
     * @return Response|false Response object or false on failure
     */
    public function getFile(string $fileName, string $realName, string $disk = 'default'): Response|false
    {
        try {
            // Determine the disk to use
            if ($disk === 'default') {
                $disk = $this->filesystemManager->getDefaultDriver();
            }

            // Public disk but using s3 means we are getting files from S3 but they have public visibility
            if ($disk === 'public' && $this->config->useS3) {
                $disk = 's3';
            }

            $storage = $this->filesystemManager->disk($disk);

            // Check if file exists
            if (! $storage->exists($fileName)) {
                return false;
            }

            // Get file mime type
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $mimeType = $this->filesystemManager->mimeType($fileName);

            return $storage->download($fileName, $realName);

        } catch (\Exception $e) {
            Log::error('Error getting file: '.$e->getMessage(), [
                'fileName' => $fileName,
                'disk' => $disk,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Get a file URL
     *
     * @param  string  $fileName  The file name (with extension)
     * @param  string  $disk  The disk to use for storage
     * @param  int  $expires  Number of minutes before URL expires (for S3)
     * @return string|false File URL or false on failure
     */
    public function getFileUrl(string $fileName, string $disk = 'default', $expires = 0): string|false
    {
        try {
            // Determine the disk to use
            if ($disk === 'default') {
                $disk = $this->filesystemManager->getDefaultDriver();
            }

            if ($disk === 'public' && $this->config->useS3) {
                $disk = 's3';
            }

            $storage = $this->filesystemManager->disk($disk);

            // Check if file exists
            if (! $storage->exists($fileName)) {
                return false;
            }

            // Get file mime type
            $extension = pathinfo($fileName, PATHINFO_EXTENSION);
            $mimeType = $this->filesystemManager->mimeType($fileName);

            if ($disk === 's3') {
                try {
                    // Generate a signed URL with proper expiration
                    // Use configured expiration time if not specified
                    if ($expires <= 0) {
                        $expires = (int) $this->config->get('filesystems.url_expiration', 60);
                    }

                    // Add some randomness to prevent cache stampede
                    $jitter = random_int(0, min(5, $expires / 10));
                    $expiration = dtHelper()->now()->addMinutes($expires + $jitter);

                    return $storage->temporaryUrl($fileName, $expiration);
                } catch (\Exception $e) {
                    Log::error('Failed to generate S3 temporary URL: '.$e->getMessage());

                    return false;
                }
            } else {
                // Use caching for non-S3 files
                $cacheEnabled = $this->config->get('filesystems.cache.enabled', true);
                $cacheDuration = $this->config->get('filesystems.cache.duration', 60);
                $cacheKey = "file_url_{$disk}_{$fileName}";

                if ($cacheEnabled && $disk !== 's3') {
                    return Cache::remember($cacheKey, $cacheDuration * 60, static function () use ($storage, $fileName) {
                        return $storage->url($fileName);
                    });
                }

                return $storage->url($fileName);
            }

        } catch (\Exception $e) {
            Log::error('Error getting file URL: '.$e->getMessage(), [
                'fileName' => $fileName,
                'disk' => $disk,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * Delete a file
     *
     * @param  string  $fileName  The file name (with extension)
     * @param  string  $disk  The disk to use for storage
     * @return bool True on success, false on failure
     */
    public function deleteFile(string $fileName, string $disk = 'default'): bool
    {
        if (empty($fileName)) {
            Log::warning('Attempted to delete a file with empty filename');

            return false;
        }

        try {
            // Determine the disk to use
            if ($disk === 'default') {
                $disk = $this->filesystemManager->getDefaultDriver();
            }

            $storage = $this->filesystemManager->disk($disk);

            // Check if file exists before attempting deletion
            if (! $storage->exists($fileName)) {
                Log::info("File not found for deletion: {$fileName} on disk {$disk}");

                return false;
            }

            // Delete the file
            return $storage->delete($fileName);

        } catch (\Exception $e) {
            Log::error('Error deleting file: '.$e->getMessage(), [
                'fileName' => $fileName,
                'disk' => $disk,
                'exception' => $e,
            ]);

            return false;
        }
    }

    /**
     * This function returns the maximum files size that can be uploaded in PHP
     *
     * @return int The filesize allowed by php.ini config in bytes
     */
    public static function getMaximumFileUploadSize(): int
    {
        return min(self::convertPHPSizeToBytes(ini_get('post_max_size')), self::convertPHPSizeToBytes(ini_get('upload_max_filesize')));
    }

    /**
     * This function transforms the php.ini notation for numbers (like '2M') to an integer (2*1024*1024 in this case)
     *
     * @return int The value in bytes
     */
    private static function convertPHPSizeToBytes(string $sSize): int
    {
        $sSuffix = strtoupper(substr($sSize, -1));
        if (! in_array($sSuffix, ['P', 'T', 'G', 'M', 'K'])) {
            return (int) $sSize;
        }
        $iValue = substr($sSize, 0, -1);
        switch ($sSuffix) {
            case 'P':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'T':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'G':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'M':
                $iValue *= 1024;
                // Fallthrough intended
                // no break
            case 'K':
                $iValue *= 1024;
                break;
        }

        return (int) $iValue;
    }
}
