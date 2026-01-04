<?php

namespace Leantime\Domain\Files\Repositories;

use Illuminate\Contracts\Container\BindingResolutionException;
use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Core\Files\Contracts\FileManagerInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class Files
{
    public array $adminModules = ['project' => 'Projects', 'ticket' => 'Tickets', 'client' => 'Clients', 'lead' => 'Lead', 'private' => 'General']; // 'user'=>'Users',

    public array $userModules = ['project' => 'Projects', 'ticket' => 'Tickets', 'private' => 'General'];

    private ConnectionInterface $db;

    private FileManagerInterface $fileManager;

    public function __construct(DbCore $db, FileManagerInterface $fileManager)
    {
        $this->db = $db->getConnection();
        $this->fileManager = $fileManager;
    }

    public function addFile(array $values, string $module): false|string
    {
        $id = $this->db->table('zp_file')->insertGetId([
            'encName' => $values['encName'],
            'realName' => $values['realName'],
            'extension' => $values['extension'],
            'module' => $module,
            'moduleId' => $values['moduleId'],
            'userId' => $values['userId'],
            'date' => now(),
        ]);

        return (string) $id;
    }

    public function getFile(int $id): array|false
    {
        $result = $this->db->table('zp_file as file')
            ->select(
                'file.id',
                'file.extension',
                'file.realName',
                'file.encName',
                'file.date',
                'file.module',
                'file.moduleId',
                'user.firstname',
                'user.lastname'
            )
            ->join('zp_user as user', 'file.userId', '=', 'user.id')
            ->where('file.id', $id)
            ->first();

        return $result ? (array) $result : false;
    }

    public function getFiles(int $userId = 0): false|array
    {
        $query = $this->db->table('zp_file as file')
            ->select(
                'file.id',
                'file.moduleId',
                'file.extension',
                'file.realName',
                'file.encName',
                'file.date',
                'file.module',
                'user.firstname',
                'user.lastname'
            )
            ->join('zp_user as user', 'file.userId', '=', 'user.id');

        if ($userId > 0) {
            $query->where('file.userId', $userId);
        }

        $results = $query->orderBy('file.module')
            ->orderBy('file.moduleId')
            ->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function getFolders(string $module): array
    {
        $folders = [];
        $files = $this->getFiles(session('userdata.id'));

        $table = match ($module) {
            'ticket' => 'zp_tickets',
            'client' => 'zp_clients',
            'project' => 'zp_projects',
            'lead' => 'zp_lead',
            default => 'zp_tickets',
        };

        $titleColumn = match ($module) {
            'ticket' => 'headline',
            'client' => 'name',
            'project' => 'name',
            'lead' => 'name',
            default => 'headline',
        };

        $ids = [];
        foreach ($files as $file) {
            if (! isset($ids[$file['moduleId']])) {
                $result = $this->db->table($table)
                    ->select("{$titleColumn} as title", 'id')
                    ->where('id', $file['moduleId'])
                    ->limit(1)
                    ->first();

                if ($result) {
                    $folders[] = (array) $result;
                }
                $ids[$file['moduleId']] = true;
            }
        }

        return $folders;
    }

    public function getFilesByModule(string $module = '', ?int $moduleId = null, ?int $userId = 0): false|array
    {
        $query = $this->db->table('zp_file as file')
            ->select(
                'file.id',
                'file.extension',
                'file.realName',
                'file.encName',
                'file.date',
                'file.module',
                'file.moduleId',
                'user.firstname',
                'user.lastname',
                'user.id AS userId'
            )
            ->addSelect('file.date AS rawDate')
            ->join('zp_user as user', 'file.userId', '=', 'user.id');

        if ($module !== '') {
            $query->where('file.module', $module);
        } else {
            $query->where('file.module', '<>', '');
        }

        if ($moduleId !== null) {
            $query->where('file.moduleId', $moduleId);
        }

        if ($userId > 0) {
            $query->where('file.userId', $userId);
        }

        $results = $query->get();

        return array_map(fn ($item) => (array) $item, $results->toArray());
    }

    public function deleteFile(int $id): bool
    {
        $result = $this->db->table('zp_file')
            ->select('encName', 'extension')
            ->where('id', $id)
            ->first();

        if ($result && isset($result->encName) && isset($result->extension)) {
            // Use FileManager to delete the file
            $fileName = $result->encName.'.'.$result->extension;

            // Delete file from default storage
            $this->fileManager->deleteFile($fileName, 'default');
        }

        return $this->db->table('zp_file')
            ->where('id', $id)
            ->delete() > 0;
    }

    /**
     * @return array|false
     *
     * @throws BindingResolutionException
     */
    public function upload(array $file, string $module, int $moduleId): false|string|array
    {
        // Clean module mess
        if ($module === 'projects') {
            $module = 'project';
        }
        if ($module === 'tickets') {
            $module = 'ticket';
        }

        try {
            $uploadedFile = $file['file'];
            $path = $uploadedFile['name'];
            $ext = pathinfo($path, PATHINFO_EXTENSION);
            $realName = str_replace('.'.$ext, '', $uploadedFile['name']);

            // Just something unique to avoid collision in s3 (each customer has their own folder)
            $newname = md5(session('userdata.id').time());

            // Create a UploadedFile instance
            $symfonyFile = new UploadedFile(
                $uploadedFile['tmp_name'],
                $uploadedFile['name'],
                $uploadedFile['type'],
                $uploadedFile['error'],
                true
            );

            // Use FileManager to upload the file
            $result = $this->fileManager->upload($symfonyFile, $newname, false);

            if ($result !== false) {
                $values = [
                    'encName' => $newname,
                    'realName' => $realName,
                    'extension' => $ext,
                    'moduleId' => $moduleId,
                    'userId' => session('userdata.id'),
                    'module' => $module,
                    'fileId' => '',
                ];

                $fileAddResults = $this->addFile($values, $module);

                if ($fileAddResults) {
                    $values['fileId'] = $fileAddResults;

                    return $values;
                }
            }

            return false;
        } catch (\Exception $e) {
            report($e);

            return $e->getMessage();
        }
    }

    public function uploadCloud(string $name, string $url, string $module, int $moduleId): void
    {

        // Add cloud stuff here.
    }
}
