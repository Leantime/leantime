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
     * Source: https://stackoverflow.com/questions/2021624/string-sanitizer-for-filename
     *
     * @param  string  $filename  The filename to sanitize
     * @return string Sanitized filename
     */
    private function sanitizeFilename(string $filename): string
    {
        // Remove any directory paths
        $filename = basename($filename);

        // sanitize filename
        $filename = preg_replace(
            '~
        [<>:"/\\\|?*]|           # file system reserved https://en.wikipedia.org/wiki/Filename#Reserved_characters_and_words
        [\x00-\x1F]|             # control characters http://msdn.microsoft.com/en-us/library/windows/desktop/aa365247%28v=vs.85%29.aspx
        [\x7F\xA0\xAD]|          # non-printing characters DEL, NO-BREAK SPACE, SOFT HYPHEN
        [#\[\]@!$&\'()+,;=]|     # URI reserved https://www.rfc-editor.org/rfc/rfc3986#section-2.2
        [{}^\~`]                 # URL unsafe characters https://www.ietf.org/rfc/rfc1738.txt
        ~x',
            '-', $filename);
        // avoids ".", ".." or ".hiddenFiles"
        $filename = ltrim($filename, '.-');

        // maximize filename length to 255 bytes http://serverfault.com/a/9548/44086
        $ext = pathinfo($filename, PATHINFO_EXTENSION);

        return mb_strcut(pathinfo($filename, PATHINFO_FILENAME), 0, 255 - ($ext ? strlen($ext) + 1 : 0), mb_detect_encoding($filename)).($ext ? '.'.$ext : '');
    }

    public function filter_filename($filename, $beautify = true) {}

    /**
     * File extensions that are never allowed to be uploaded.
     * These can be executed server-side or used to override server configuration.
     */
    private const DENIED_EXTENSIONS = [
        'php',
        'phtml',
        'php3',
        'php4',
        'php5',
        'phar',
        'htaccess',
        'shtml',
    ];

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

        // Reject dangerous file extensions that could be executed server-side
        $extension = strtolower($file->getClientOriginalExtension());
        if (in_array($extension, self::DENIED_EXTENSIONS, true)) {
            Log::warning('Blocked upload of dangerous file type', [
                'extension' => $extension,
                'originalName' => $file->getClientOriginalName(),
                'userId' => session('userdata.id'),
            ]);
            throw new FileValidationException(
                'File type .'.$extension.' is not allowed',
                FileValidationException::INVALID_MIME_TYPE
            );
        }

        // Also check for double extensions like file.php.jpg that could bypass
        // some server configurations
        $originalName = strtolower($file->getClientOriginalName());
        foreach (self::DENIED_EXTENSIONS as $deniedExt) {
            if (str_contains($originalName, '.'.$deniedExt.'.')) {
                Log::warning('Blocked upload with dangerous double extension', [
                    'originalName' => $file->getClientOriginalName(),
                    'userId' => session('userdata.id'),
                ]);
                throw new FileValidationException(
                    'File contains a disallowed extension in its name',
                    FileValidationException::INVALID_MIME_TYPE
                );
            }
        }

        // SVG files can contain embedded scripts and event handlers.
        // Reject them outright to prevent stored XSS attacks.
        if ($extension === 'svg' || $extension === 'svgz') {
            Log::info('Blocked SVG upload for security', [
                'originalName' => $file->getClientOriginalName(),
                'userId' => session('userdata.id'),
            ]);
            throw new FileValidationException(
                'SVG file uploads are not allowed for security reasons',
                FileValidationException::INVALID_MIME_TYPE
            );
        }
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
            $mimeType = $storage->mimeType($fileName) ?: 'application/octet-stream';

            // Read file contents directly instead of using download() which relies
            // on fpassthru() — a function disabled on many shared hosting environments.
            $content = $storage->get($fileName);

            $response = new Response($content);
            $response->headers->set('Content-Type', $mimeType);
            $response->headers->set('Content-Length', (string) $storage->size($fileName));
            $response->headers->set('Content-Disposition', 'inline; filename="'.$realName.'"');

            // Sandbox all user-uploaded files to prevent script execution
            $response->headers->set('Content-Security-Policy', 'sandbox');

            // Force download for content types that can execute scripts (HTML, SVG, XML)
            // to prevent inline rendering of potentially malicious content
            $dangerousMimeTypes = [
                'text/html',
                'application/xhtml+xml',
                'image/svg+xml',
                'application/xml',
                'text/xml',
            ];

            if (in_array(strtolower($mimeType), $dangerousMimeTypes, true)) {
                $response->headers->set('Content-Disposition', 'attachment; filename="'.$realName.'"');
                $response->headers->set('X-Content-Type-Options', 'nosniff');
            }

            if (! $this->config->debug) {
                $response->headers->set('Pragma', 'public');
                $response->headers->set('Cache-Control', 'max-age=86400');
                $response->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', $storage->lastModified($fileName)).' GMT');
            }

            return $response;

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
