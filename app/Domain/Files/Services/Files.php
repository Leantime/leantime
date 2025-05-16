<?php

namespace Leantime\Domain\Files\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Files\Exceptions\FileValidationException;
use Leantime\Core\Files\FileManager;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * @api
 */
class Files
{
    use DispatchesEvents;

    public function __construct(
        protected FileRepository $fileRepository,
        protected FileManager $fileManager,
        protected LanguageCore $language,
    ) {}

    /**
     * @api
     */
    public function getFilesByModule(string $module = '', $entityId = null, $userId = null): false|array
    {
        return $this->fileRepository->getFilesByModule($module, $entityId, $userId);
    }

    /**
     * @throws BindingResolutionException
     *
     * @api
     */
    public function upload($file, $module, $moduleId, $entity = null, $disk = 'default'): array|string
    {
        try {
            // Validate input parameters
            if (empty($module) || empty($moduleId)) {
                Log::warning('Upload attempted with missing module or moduleId', [
                    'module' => $module,
                    'moduleId' => $moduleId,
                ]);
                throw new FileValidationException('Missing module or moduleId', FileValidationException::VALIDATION_ERROR);
            }

            if (! isset($file['file']) || ! is_array($file['file'])) {
                throw new FileNotFoundException('File not included in request or has invalid format');
            }
        } catch (FileValidationException $e) {
            Log::warning('File validation failed: '.$e->getMessage());

            return $e->getUserMessage();
        }

        // Normalize module names for consistency
        if ($module === 'projects') {
            $module = 'project';
        }
        if ($module === 'tickets') {
            $module = 'ticket';
        }

        try {
            // Validate file type with the enhanced validator
            $symfonyFile = new UploadedFile(
                $file['file']['tmp_name'],
                $file['file']['name'],
                $file['file']['type'],
                $file['file']['error'],
                true
            );

            // Validate file size before processing
            if ($file['file']['size'] > FileManager::getMaximumFileUploadSize()) {
                throw new FileValidationException('File exceeds maximum allowed size', FileValidationException::FILE_TOO_LARGE);
            }
        } catch (FileValidationException $e) {
            Log::warning('File validation failed: '.$e->getMessage());

            return $e->getUserMessage();
        }

        try {
            // Create a UploadedFile instance
            $symfonyFile = new UploadedFile(
                $file['file']['tmp_name'],
                $file['file']['name'],
                $file['file']['type'],
                $file['file']['error'],
                (bool) config('app.debug')
            );

            $leantimeFile = $this->fileManager->upload($symfonyFile, $disk);
        } catch (\Exception $e) {
            return 'Error uploading file: '.$e->getMessage();
        }

        if ($leantimeFile) {
            $leantimeFile['module'] = $module;
            $leantimeFile['moduleId'] = $moduleId;

            $fileAddResults = $this->fileRepository->addFile($leantimeFile, $module);

            if ($fileAddResults) {
                $leantimeFile['fileId'] = $fileAddResults;

                return $leantimeFile;
            }
        }

        return false;
    }

    public function getModules($id): array
    {
        $modules = $this->fileRepository->userModules;
        if (Auth::userIsAtLeast(Roles::$admin)) {
            $modules = $this->fileRepository->adminModules;
        }

        return $modules;
    }

    /**
     * @api
     */
    public function deleteFile($fileId): bool
    {
        return $this->fileRepository->deleteFile($fileId);
    }

    public function getFilePathById($fileId): false|string
    {
        $dbReference = $this->fileRepository->getFile($fileId);
        if ($dbReference) {
            return $this->fileManager->getFileUrl($dbReference['encName'].'.'.$dbReference['extension']);
        }

        return false;
    }

    public function getFileById($fileId): false|Response
    {
        $dbReference = $this->fileRepository->getFile($fileId);
        if ($dbReference) {
            return $this->fileManager->getFile($dbReference['encName'].'.'.$dbReference['extension'], $dbReference['realName']);
        }

        return false;
    }
}
