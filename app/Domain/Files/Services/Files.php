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
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * @api
 */
class Files
{
    use DispatchesEvents;

    /**
     * Image file extensions treated as previewable images across the file UI.
     *
     * @var array<int, string>
     */
    private const IMAGE_EXTENSIONS = ['jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv', 'webp'];

    /**
     * Module types whose files are restricted to the uploading user.
     *
     * @var array<int, string>
     */
    private const OWNER_RESTRICTED_MODULES = ['private', 'user', 'lead', 'export'];

    public function __construct(
        protected FileRepository $fileRepository,
        protected FileManager $fileManager,
        protected LanguageCore $language,
        protected ProjectRepository $projectRepository,
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
     * Delete a file. The caller must be the file owner or have at least manager role.
     *
     * @api
     */
    public function deleteFile($fileId): bool
    {
        $file = $this->fileRepository->getFile((int) $fileId);

        if (! $file) {
            return false;
        }

        $currentUserId = session('userdata.id');

        // File owner can delete their own file
        if ((int) $file['userId'] === (int) $currentUserId) {
            return $this->fileRepository->deleteFile((int) $fileId);
        }

        // Managers and above can delete any file
        if (Auth::userIsAtLeast(Roles::$manager)) {
            return $this->fileRepository->deleteFile((int) $fileId);
        }

        return false;
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

    /**
     * Returns the list of image file extensions treated as previewable images.
     *
     * @return array<int, string> The whitelisted image extensions.
     *
     * @api
     */
    public function getImageExtensions(): array
    {
        return self::IMAGE_EXTENSIONS;
    }

    /**
     * Determines whether a file's module type restricts access to the file owner.
     *
     * Files in 'private', 'user', 'lead' and 'export' modules are only accessible
     * to the user who uploaded them (unless the caller is admin/owner).
     *
     * @param  array  $fileRecord  The file record from the database.
     * @return bool True if the module restricts access to the file owner.
     */
    public function isOwnerRestrictedModule(array $fileRecord): bool
    {
        return in_array($fileRecord['module'] ?? '', self::OWNER_RESTRICTED_MODULES, true);
    }

    /**
     * Resolves the owning project id for a file record based on its module type.
     *
     * For 'project' module files the moduleId is the project id directly.
     * For 'ticket' module files the owning ticket is looked up to find its project.
     * All other module types return null (handled by the owner-restriction check).
     *
     * @param  array  $fileRecord  The file record from the database.
     * @return int|null The owning project id, or null when no project context applies.
     */
    public function resolveProjectId(array $fileRecord): ?int
    {
        return $this->fileRepository->getProjectIdForFile($fileRecord);
    }

    /**
     * Authorizes the current user to upload a file against a target module/moduleId.
     *
     * The /api/files (now /files/upload) endpoint takes module + moduleId straight from
     * the request, and Files::upload() does no access control — so without this gate a
     * logged-in user could attach files to another project/ticket by tampering with the
     * query string. Mirrors the read-path model in getFileForUser(): admins/owners bypass,
     * project-scoped targets (project/ticket) require access to the owning project, and
     * targets with no project mapping fall back to that path's (unrestricted) behaviour.
     *
     * Not @api: internal authorization helper for the upload controller, not a JSON-RPC method.
     *
     * @param  string  $module  The target module (e.g. project, ticket, wiki)
     * @param  int  $moduleId  The target entity id within that module
     * @return bool True if the current user may upload to the target
     */
    public function userCanUploadToModule(string $module, int $moduleId): bool
    {
        if (Auth::userIsAtLeast(Roles::$admin)) {
            return true;
        }

        $projectId = $this->resolveProjectId(['module' => $module, 'moduleId' => $moduleId]);

        if ($projectId !== null) {
            return $this->projectRepository->isUserAssignedToProject((int) session('userdata.id'), $projectId);
        }

        return true;
    }

    /**
     * Resolves a file by its encoded name, authorizes the given user, and returns
     * the file response.
     *
     * Authorization mirrors the previous controller behavior: admins/owners bypass
     * all checks; project-scoped files require the user to be assigned to the owning
     * project; owner-restricted modules require the user to be the uploader.
     *
     * @param  string  $encName  The encoded (hashed) filename without extension.
     * @param  int  $userId  The id of the requesting user.
     * @return Response The file content response, 403 if unauthorized, or 404 if not found.
     *
     * @throws \Exception
     *
     * @api
     */
    public function getFileForUser(string $encName, int $userId): Response
    {
        $fileRecord = $this->fileRepository->getFileByEncName($encName);

        if ($fileRecord === false) {
            return new Response('File not found', 404);
        }

        // Use DB values instead of user-supplied params to prevent parameter tampering
        $realName = $fileRecord['realName'];
        $ext = $fileRecord['extension'];

        // Check project-level access unless the caller is admin or owner
        if (! Auth::userIsAtLeast(Roles::$admin)) {
            $projectId = $this->resolveProjectId($fileRecord);

            if ($projectId !== null) {
                if (! $this->projectRepository->isUserAssignedToProject($userId, $projectId)) {
                    Log::warning('Unauthorized file access attempt', [
                        'userId' => $userId,
                        'fileId' => $fileRecord['id'],
                        'projectId' => $projectId,
                    ]);

                    return new Response('', 403);
                }
            } elseif ($this->isOwnerRestrictedModule($fileRecord)) {
                // For private/user files, only the file owner can access
                if ((int) ($fileRecord['userId'] ?? 0) !== $userId) {
                    Log::warning('Unauthorized file access attempt on private file', [
                        'userId' => $userId,
                        'fileId' => $fileRecord['id'],
                        'module' => $fileRecord['module'] ?? '',
                    ]);

                    return new Response('', 403);
                }
            }
        }

        // Construct the file name from trusted DB values
        $fileName = $encName.'.'.$ext;

        $response = $this->fileManager->getFile($fileName, $realName);

        if ($response === false) {
            return new Response('File not found', 404);
        }

        return $response;
    }

    /**
     * Handles the upload/delete POST action for the file browser controllers.
     *
     * Dispatches based on the submitted POST/FILES payload and returns a structured
     * result describing the action taken and its outcome so the controller can render
     * the appropriate notification (and redirect after a successful delete).
     *
     * @param  array  $post  The POST payload (expects 'delFile' and/or 'upload').
     * @param  array  $files  The FILES payload (expects 'file').
     * @param  string  $module  The module the upload belongs to (e.g. 'project').
     * @param  int|string|null  $moduleId  The module entity id the upload belongs to.
     * @return array{action: string|null, success: bool} The action ('delete'|'upload'|null) and whether it succeeded.
     *
     * @throws BindingResolutionException
     *
     * @api
     */
    public function handleFileAction(array $post, array $files, string $module, int|string|null $moduleId): array
    {
        if (isset($post['delFile'])) {
            return [
                'action' => 'delete',
                'success' => $this->deleteFile($post['delFile']),
            ];
        }

        if (isset($post['upload']) || isset($files['file'])) {
            if (isset($files['file'])) {
                $this->upload($files, $module, $moduleId);

                return ['action' => 'upload', 'success' => true];
            }

            return ['action' => 'upload', 'success' => false];
        }

        return ['action' => null, 'success' => false];
    }
}
