<?php

namespace Leantime\Domain\Files\Services;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Files\Exceptions\FileValidationException;
use Leantime\Core\Files\FileManager;
use Leantime\Core\Language as LanguageCore;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Clients\Permissions\ClientsPermissions;
use Leantime\Domain\Files\Permissions\FilesPermissions;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Symfony\Component\Filesystem\Exception\FileNotFoundException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Response;

/**
 * @api
 */
class Files extends BaseService
{
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

    /**
     * Module types whose files belong to a project (resolved by getProjectIdForFile). For these,
     * an unresolvable project id — an invalid or deleted entity — must FAIL CLOSED rather than
     * fall through to the non-project "allow upload" / "serve file" path.
     *
     * @var array<int, string>
     */
    private const PROJECT_SCOPED_MODULES = ['project', 'ticket'];

    /**
     * Module whose files belong to a client (company account). Clients are a company-wide
     * resource, so access is gated by the GLOBAL clients.* permissions: clients.view to list or
     * download, clients.edit to upload or delete.
     */
    private const CLIENT_MODULE = 'client';

    public function __construct(
        protected FileRepository $fileRepository,
        protected FileManager $fileManager,
        protected LanguageCore $language,
    ) {}

    /**
     * Lists files for a module/entity, fail-closed against the entity's owning project.
     *
     * We resolve the target's real project and require files.view in it (readonly+);
     * owner-restricted listings (private/user/lead/export) are limited to the owner; client
     * listings need clients.view and a specific client id; an empty/unknown module returns []
     * rather than dumping the whole table. The module name is normalized (lowercased) first so a
     * differently-cased name can't side-step the checks.
     *
     * @api
     */
    public function getFilesByModule(string $module = '', $entityId = null, $userId = null): false|array
    {
        $module = self::normalizeModule($module);

        if (! $this->canListModule($module, $entityId)) {
            return [];
        }

        return $this->fileRepository->getFilesByModule($module, $entityId, $userId);
    }

    /**
     * Normalizes a module name: trimmed, lowercased and with the legacy plural aliases folded
     * into their singular form. Every entry point (upload, list, download, delete) runs module
     * names through this so the authorization checks always see the canonical spelling.
     *
     * @param  string|null  $module  The raw module name.
     * @return string The canonical module name ('' when empty).
     */
    public static function normalizeModule(?string $module): string
    {
        $module = strtolower(trim((string) $module));

        return match ($module) {
            'projects' => 'project',
            'tickets' => 'ticket',
            'clients' => 'client',
            default => $module,
        };
    }

    /**
     * Whether the current user may list the files of a module/entity.
     *
     * @param  string  $module  The normalized module name.
     * @param  mixed  $entityId  The entity id within the module.
     */
    private function canListModule(string $module, mixed $entityId): bool
    {
        if (in_array($module, self::PROJECT_SCOPED_MODULES, true)) {
            $projectId = $this->resolveProjectId(['module' => $module, 'moduleId' => $entityId]);

            return $projectId !== null && $this->can(FilesPermissions::VIEW, $projectId);
        }

        if (in_array($module, self::OWNER_RESTRICTED_MODULES, true)) {
            // Owner-restricted listing: only the owner may enumerate their own files.
            return (int) $entityId === $this->currentUserId();
        }

        if ($module === self::CLIENT_MODULE) {
            // A specific client id is required so the call can't dump every client's files.
            return (int) $entityId > 0 && $this->can(ClientsPermissions::VIEW);
        }

        // Empty or unknown module: refuse rather than dump rows.
        return false;
    }

    /**
     * Stores an uploaded file against a module/moduleId after authorizing the target via
     * userCanUploadToModule() (empty or unknown modules are denied).
     *
     * @throws BindingResolutionException
     * @throws AuthorizationException When the current user may not upload to the target.
     *
     * @api
     */
    public function upload($file, $module, $moduleId, $entity = null, $disk = 'default'): array|string|false
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

        // Normalize module names (case + legacy plurals) so the checks below see the canonical
        // spelling and the stored row matches it.
        $module = self::normalizeModule((string) $module);

        // Authorize against the target before writing anything. This guards the JSON-RPC path,
        // which reaches the @api upload() directly, without the Upload controller's
        // userCanUploadToModule pre-check.
        if (! $this->userCanUploadToModule($module, (int) $moduleId)) {
            throw new AuthorizationException;
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
     * Delete a file. The uploader may always delete their own file; otherwise the caller needs
     * files.delete (editor+) IN THE FILE'S OWN PROJECT.
     *
     * The old check allowed any manager+ to delete ANY file globally (no project scope) — an IDOR
     * across projects. We now resolve the file's real project and require files.delete there
     * (admin/owner still bypass project membership). Owner-restricted/no-project files (private,
     * user avatar, ...) can only be deleted by their uploader. Denials soft-deny (return false) to
     * preserve the prior contract and keep handleFileAction's success flag meaningful.
     *
     * @api
     */
    public function deleteFile($fileId): bool
    {
        $file = $this->fileRepository->getFile((int) $fileId);

        if (! $file) {
            return false;
        }

        $module = self::normalizeModule($file['module'] ?? '');

        // Client files are a company resource: deleting one needs clients.edit, even for the
        // uploader (who may since have lost access to client management).
        if ($module === self::CLIENT_MODULE) {
            if (! $this->can(ClientsPermissions::EDIT)) {
                return false;
            }

            return $this->fileRepository->deleteFile((int) $fileId);
        }

        // The uploader may always delete their own file, regardless of role.
        if ((int) $file['userId'] === $this->currentUserId()) {
            return $this->fileRepository->deleteFile((int) $fileId);
        }

        $projectId = in_array($module, self::PROJECT_SCOPED_MODULES, true)
            ? $this->resolveProjectId(['module' => $module, 'moduleId' => $file['moduleId'] ?? null])
            : null;

        // Non-owner delete of a project-scoped file requires files.delete in THAT project.
        if ($projectId !== null) {
            if (! $this->can(FilesPermissions::DELETE, $projectId)) {
                return false;
            }

            return $this->fileRepository->deleteFile((int) $fileId);
        }

        // Owner-restricted / no-project / unknown-module file and the caller is not the uploader: deny.
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
        return in_array(self::normalizeModule($fileRecord['module'] ?? ''), self::OWNER_RESTRICTED_MODULES, true);
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
     * The /files/upload (alias /api/files) endpoint takes module + moduleId straight from the
     * request, so every target is checked here and upload() enforces the same verdict in-body:
     *  - project-scoped targets (project/ticket) need files.upload in the owning project
     *    (admin/owner bypass membership); an unresolvable id fails closed
     *  - client targets need the global clients.edit permission
     *  - owner-restricted targets (user/private/lead/export) keep prior behavior; their flows pin
     *    moduleId server-side (e.g. ProfileImage forces the session user's id)
     *  - anything else (empty or unknown module) is denied
     *
     * Not @api: internal authorization helper for the upload controller, not a JSON-RPC method.
     *
     * @param  string  $module  The target module (e.g. project, ticket, client); normalized here
     * @param  int  $moduleId  The target entity id within that module
     * @return bool True if the current user may upload to the target
     */
    public function userCanUploadToModule(string $module, int $moduleId): bool
    {
        $module = self::normalizeModule($module);

        if (in_array($module, self::PROJECT_SCOPED_MODULES, true)) {
            $projectId = $this->resolveProjectId(['module' => $module, 'moduleId' => $moduleId]);

            return $projectId !== null && $this->can(FilesPermissions::UPLOAD, $projectId);
        }

        if ($module === self::CLIENT_MODULE) {
            return $moduleId > 0 && $this->can(ClientsPermissions::EDIT);
        }

        if (in_array($module, self::OWNER_RESTRICTED_MODULES, true)) {
            return true;
        }

        // Empty or unknown module: deny.
        return false;
    }

    /**
     * Resolves a file by its encoded name, authorizes the CURRENT (session) user, and returns
     * the file response.
     *
     * Project-scoped files require files.view in the owning project (readonly+; admin/owner bypass
     * project membership — equivalent to the prior admin-bypass + isUserAssignedToProject check).
     * Owner-restricted modules require the caller to be the uploader. Authorization always uses the
     * session user, never the $userId argument: this @api method previously trusted the passed id,
     * so a JSON-RPC caller could read any owner-restricted file by claiming to be its uploader.
     *
     * @param  string  $encName  The encoded (hashed) filename without extension.
     * @param  int  $userId  Retained for signature/RPC compatibility; not used for authorization.
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
        $ext = $fileRecord['extension'];
        $realName = FileManager::displayName((string) $fileRecord['realName'], (string) $ext);

        $currentUserId = $this->currentUserId();
        $module = self::normalizeModule($fileRecord['module'] ?? '');

        if (! $this->canViewFile($module, $fileRecord, $currentUserId)) {
            Log::warning('Unauthorized file access attempt', [
                'userId' => $currentUserId,
                'fileId' => $fileRecord['id'],
                'module' => $module,
            ]);

            return new Response('', 403);
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
     * Whether the current user may view/download a stored file.
     *
     *  - project-scoped files need files.view in the owning project; an unresolvable project
     *    (e.g. a deleted ticket) is denied
     *  - client files need the global clients.view permission
     *  - owner-restricted files are limited to their uploader
     *  - files with an empty or unknown module are limited to their uploader (fail closed)
     *
     * @param  string  $module  The normalized module of the file.
     * @param  array  $fileRecord  The file record from the database.
     * @param  int|null  $currentUserId  The session user id.
     */
    private function canViewFile(string $module, array $fileRecord, ?int $currentUserId): bool
    {
        if (in_array($module, self::PROJECT_SCOPED_MODULES, true)) {
            $projectId = $this->resolveProjectId(['module' => $module, 'moduleId' => $fileRecord['moduleId'] ?? null]);

            return $projectId !== null && $this->can(FilesPermissions::VIEW, $projectId);
        }

        if ($module === self::CLIENT_MODULE) {
            return $this->can(ClientsPermissions::VIEW);
        }

        // Owner-restricted and unknown modules: only the uploader.
        return $currentUserId !== null && (int) ($fileRecord['userId'] ?? 0) === $currentUserId;
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
     * Not @api: an internal controller helper shaped around $_POST/$_FILES, called in-process by the
     * Browse/ShowAll controllers. It is deliberately NOT JSON-RPC reachable — its delegates
     * (deleteFile/upload) self-authorize, but the request-array signature is not an API surface.
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
                try {
                    $result = $this->upload($files, $module, $moduleId);
                } catch (AuthorizationException) {
                    // A denied upload becomes a clean "upload failed" notification, not a 403 page.
                    return ['action' => 'upload', 'success' => false];
                }

                // upload() returns the file metadata array on success, or a string/false on failure.
                return ['action' => 'upload', 'success' => is_array($result)];
            }

            return ['action' => 'upload', 'success' => false];
        }

        return ['action' => null, 'success' => false];
    }
}
