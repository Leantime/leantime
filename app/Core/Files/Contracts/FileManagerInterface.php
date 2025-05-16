<?php

namespace Leantime\Core\Files\Contracts;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * FileManagerInterface - Interface for file management operations
 */
interface FileManagerInterface
{
    /**
     * Upload a file
     *
     * @param  UploadedFile  $file  The file to upload
     * @param  string  $newName  The new name for the file (without extension)
     * @param  string  $disk  Where to push the file to
     * @return array|false Array with file info or false on failure
     */
    public function upload(UploadedFile $file, string $disk = 'default'): array|false;

    /**
     * Get a file
     *
     * @param  string  $fileName  The file name (with extension)
     * @param  string  $realName  The original file name (for Content-Disposition)
     * @param  bool  $public  Whether the file is publicly accessible
     * @return Response|false Response object or false on failure
     */
    public function getFile(string $fileName, string $realName, string $disk = 'default'): Response|false;

    /**
     * Delete a file
     *
     * @param  string  $fileName  The file name (with extension)
     * @param  bool  $public  Whether the file is publicly accessible
     * @return bool True on success, false on failure
     */
    public function deleteFile(string $fileName, string $disk = 'default'): bool;
}
