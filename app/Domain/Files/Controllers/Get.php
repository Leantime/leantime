<?php

namespace Leantime\Domain\Files\Controllers;

use Aws\S3\S3Client;
use Illuminate\Support\Facades\Log;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Controller\Controller;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Files\FileManager;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class Get extends Controller
{
    private FileService $filesService;

    private FileRepository $filesRepo;

    private Environment $config;

    private FileManager $fileManager;

    private ProjectService $projectService;

    /**
     * Initializes the controller with required dependencies.
     *
     * @param  FileRepository  $filesRepo  The file repository for database lookups.
     * @param  FileService  $filesService  The file service for business logic.
     * @param  Environment  $config  The environment configuration.
     * @param  FileManager  $fileManager  The file manager for filesystem operations.
     * @param  ProjectService  $projectService  The project service for access checks.
     */
    public function init(
        FileRepository $filesRepo,
        FileService $filesService,
        Environment $config,
        FileManager $fileManager,
        ProjectService $projectService
    ): void {
        $this->filesRepo = $filesRepo;
        $this->filesService = $filesService;
        $this->config = $config;
        $this->fileManager = $fileManager;
        $this->projectService = $projectService;
    }

    /**
     * Handles GET requests to download/view a file.
     *
     * Validates that the current user has access to the project the file belongs to
     * before serving the file content.
     *
     * @return Response The file content response, 403 if unauthorized, or 404 if not found.
     *
     * @throws \Exception
     */
    public function get(): Response
    {
        $rawEncName = $_GET['encName'] ?? '';
        $encName = preg_replace('/[^a-zA-Z0-9]+/', '', $rawEncName);

        if (empty($encName)) {
            return new Response('Bad request', 400);
        }

        // Look up the file record to check authorization and get trusted metadata
        $fileRecord = $this->filesRepo->getFileByEncName($encName);

        if ($fileRecord === false) {
            return new Response('File not found', 404);
        }

        // Use DB values instead of user-supplied params to prevent parameter tampering
        $realName = $fileRecord['realName'];
        $ext = $fileRecord['extension'];

        // Check project-level access unless user is admin or owner
        if (! Auth::userIsAtLeast(Roles::$admin)) {
            $projectId = $this->resolveProjectId($fileRecord);

            if ($projectId !== null) {
                $userId = (int) session('userdata.id');
                if (! $this->projectService->isUserAssignedToProject($userId, $projectId)) {
                    Log::warning('Unauthorized file access attempt', [
                        'userId' => $userId,
                        'fileId' => $fileRecord['id'],
                        'projectId' => $projectId,
                    ]);

                    return new Response('', 403);
                }
            } elseif ($this->isOwnerRestrictedModule($fileRecord)) {
                // For private/user files, only the file owner can access
                $userId = (int) session('userdata.id');
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

        // Use the FileManager to get the file
        $response = $this->fileManager->getFile($fileName, $realName);

        if ($response === false) {
            return new Response('File not found', 404);
        }

        return $response;
    }

    /**
     * Resolves the project ID for a given file record based on its module type.
     *
     * For 'project' module files, the moduleId is the project ID directly.
     * For 'ticket' module files, looks up the ticket to find its project.
     * For 'client' module files, returns null (handled by owner check).
     * For other module types, returns null (handled by owner check via isOwnerRestrictedModule).
     *
     * @param  array  $fileRecord  The file record from the database.
     * @return int|null The project ID, or null if project context cannot be determined.
     */
    private function resolveProjectId(array $fileRecord): ?int
    {
        $module = $fileRecord['module'] ?? '';
        $moduleId = (int) ($fileRecord['moduleId'] ?? 0);

        if ($moduleId <= 0) {
            return null;
        }

        if ($module === 'project') {
            return $moduleId;
        }

        if ($module === 'ticket') {
            $db = app()->make(DbCore::class)->getConnection();
            $ticket = $db->table('zp_tickets')
                ->select('projectId')
                ->where('id', $moduleId)
                ->first();

            if ($ticket) {
                return (int) $ticket->projectId;
            }
        }

        return null;
    }

    /**
     * Determines whether a file's module type requires owner-level access.
     *
     * Files in 'private', 'user', 'lead', and 'export' modules are restricted
     * to the user who uploaded them unless the caller is admin/owner.
     *
     * @param  array  $fileRecord  The file record from the database.
     * @return bool True if the module restricts access to the file owner.
     */
    private function isOwnerRestrictedModule(array $fileRecord): bool
    {
        $ownerModules = ['private', 'user', 'lead', 'export'];

        return in_array($fileRecord['module'] ?? '', $ownerModules, true);
    }

    /**
     * Retrieves a file locally and returns it as a streamed response.
     *
     * @param  string  $encName  The encoded name of the file.
     * @param  string  $ext  The extension of the file.
     * @param  string  $module  The module of the file.
     * @param  string  $realName  The real name of the file.
     * @return Response The streamed response containing the file or a 404 response if the file was not found.
     */
    private function getFileLocally($encName, $ext, $module, $realName): Response
    {

        $mimes = [
            'jpg' => 'image/jpg',
            'jpeg' => 'image/jpg',
            'gif' => 'image/gif',
            'png' => 'image/png',
        ];

        // TODO: Replace with ROOT
        $path = realpath(APP_ROOT.'/'.$this->config->userFilePath.'/');

        $fullPath = $path.'/'.$encName.'.'.$ext;

        if (file_exists(realpath($fullPath))) {
            if ($fd = fopen(realpath($fullPath), 'rb')) {
                $path_parts = pathinfo($fullPath);

                if ($ext == 'pdf') {
                    $mime_type = 'application/pdf';
                    header('Content-type: application/pdf');
                    header('Content-Disposition: inline; filename="'.$realName.'.'.$ext.'"');
                } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png') {
                    $mime_type = $mimes[$ext];
                    header('Content-type: '.$mimes[$ext]);
                    header('Content-disposition: inline; filename="'.$realName.'.'.$ext.'";');
                } elseif ($ext == 'svg') {
                    $mime_type = 'image/svg+xml';
                    header('Content-type: image/svg+xml');
                    header('Content-disposition: attachment; filename="'.$realName.'.'.$ext.'";');
                } else {
                    $mime_type = 'application/octet-stream';
                    header('Content-type: application/octet-stream');
                    header('Content-Disposition: attachment; filename="'.$realName.'.'.$ext.'"');
                }

                $sLastModified = filemtime($fullPath);
                $sEtag = md5_file($fullPath);

                $sFileSize = filesize($fullPath);

                $oStreamResponse = new StreamedResponse;
                $oStreamResponse->headers->set('Content-Type', $mime_type);
                $oStreamResponse->headers->set('Content-Length', $sFileSize);
                $oStreamResponse->headers->set('ETag', $sEtag);

                if (app()->make(Environment::class)->debug == false) {
                    $oStreamResponse->headers->set('Pragma', 'public');
                    $oStreamResponse->headers->set('Cache-Control', 'max-age=86400');
                    $oStreamResponse->headers->set('Last-Modified', gmdate('D, d M Y H:i:s', $sLastModified).' GMT');
                } else {
                    Log::warning('Not caching');
                }

                $oStreamResponse->setCallback(function () use ($fullPath) {
                    readfile($fullPath);
                });

                return $oStreamResponse;
            }
        }

        return new Response('File not found', 404);

    }

    /**
     * @return void
     *
     * @throws \Illuminate\Contracts\Container\BindingResolutionException
     */
    private function getFileFromS3($encName, $ext, $module, $realName): Response
    {

        $mimes = [
            'jpg' => 'image/jpg',
            'jpeg' => 'image/jpg',
            'gif' => 'image/gif',
            'png' => 'image/png',
        ];

        // Instantiate the client.

        $s3Config = [
            'version' => 'latest',
            'region' => $this->config->s3Region,

        ];

        // AWS SDK allows you to connect to aws resource using the role attached to an instance
        if (! empty($this->config->s3Key) && ! empty($this->config->s3Secret)) {
            $s3Config['credentials'] = [
                'key' => $this->config->s3Key,
                'secret' => $this->config->s3Secret,
            ];
        }

        if (
            ! empty($this->config->s3EndPoint)
            && $this->config->s3EndPoint != 'null'
            && $this->config->s3EndPoint != 'false'
        ) {
            $s3Config['endpoint'] = $this->config->s3EndPoint;
        }

        if (($this->config->s3UsePathStyleEndpoint === true
                || $this->config->s3UsePathStyleEndpoint === 'true')
            && ($this->config->s3UsePathStyleEndpoint !== 'false')
        ) {
            $s3Config['use_path_style_endpoint'] = true;
        }

        // Instantiate the S3 client with your AWS credentials
        $s3Client = new S3Client($s3Config);

        try {
            // implode all non-empty elements to allow s3FolderName to be empty.
            // otherwise you will get an error as the key starts with a slash
            $fileName = implode('/', array_filter([$this->config->s3FolderName, $encName.'.'.$ext]));
            $result = $s3Client->getObject([
                'Bucket' => $this->config->s3Bucket,
                'Key' => $fileName,
                'Body' => 'this is the body!',
            ]);

            $response = new Response($result->get('Body')->getContents());

            if ($ext == 'pdf') {
                $response->headers->set('Content-type', 'application/pdf');
            } elseif ($ext == 'jpg' || $ext == 'jpeg' || $ext == 'gif' || $ext == 'png') {
                $response->headers->set('Content-type', $result['ContentType']);
            } elseif ($ext == 'svg') {
                $response->headers->set('Content-type', 'image/svg+xml');
            } else {
                header('Content-disposition: attachment; filename="'.$realName.'.'.$ext.'";');
            }

            $response->headers->set('Content-Disposition', 'inline; filename="'.$realName.'.'.$ext.'"');

            $response->headers->set('Pragma', 'public');
            $response->headers->set('Cache-Control', 'max-age=86400');

            return $response;

        } catch (\Exception $e) {

            Log::error($e);

            return new Response('File cannot be found', 400);
        }
    }
}
