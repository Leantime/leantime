<?php

namespace Leantime\Domain\Mcp\Services;

use Illuminate\Database\ConnectionInterface;
use Leantime\Core\Language as LanguageCore;
use Leantime\Core\Mcp\Auth\McpPrincipal;
use Leantime\Core\Mcp\McpException;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Clients\Repositories\Clients;
use Leantime\Core\Db\Db as DbCore;
use Leantime\Domain\Menu\Repositories\Menu;
use Leantime\Domain\Notifications\Models\Notification;
use Leantime\Domain\Projects\Repositories\Projects;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

class ProjectWriter
{
    private ConnectionInterface $connection;

    public function __construct(
        DbCore $db,
        private Projects $projectsRepository,
        private Clients $clientsRepository,
        private ProjectService $projectsService,
        private LanguageCore $language,
        private UserRepository $usersRepository,
    ) {
        $this->connection = $db->getConnection();
    }

    public function createProject(int $actorId, array $values): int|false
    {
        $client = $this->clientsRepository->getClient((int) $values['clientId']);
        if ($client === false) {
            return false;
        }

        $assignedUsers = array_values(array_filter(
            array_map(function (array $user) use ($actorId) {
                if (! isset($user['id'])) {
                    return null;
                }

                return [
                    'id' => (int) $user['id'],
                    'projectRole' => (string) ($user['projectRole'] ?? ''),
                ];
            }, $values['assignedUsers'] ?? []),
            static fn ($user) => $user !== null && $user['id'] !== $actorId,
        ));

        $projectId = $this->projectsRepository->addProject([
            'name' => $values['name'],
            'details' => $values['details'] ?? '',
            'clientId' => (int) $values['clientId'],
            'hourBudget' => $values['hourBudget'] ?? 0,
            'dollarBudget' => $values['dollarBudget'] ?? 0,
            'psettings' => $values['psettings'] ?? 'restricted',
            'menuType' => $values['menuType'] ?? Menu::DEFAULT_MENU,
            'type' => $values['type'] ?? 'project',
            'parent' => $values['parent'] ?? null,
            'start' => $values['start'] ?? null,
            'end' => $values['end'] ?? null,
            'assignedUsers' => $assignedUsers,
        ]);

        if ($projectId === false) {
            return false;
        }

        $this->projectsRepository->addProjectRelation($actorId, $projectId, (string) ($values['creatorProjectRole'] ?? 'owner'));

        return $projectId;
    }

    public function updateProject(McpPrincipal $principal, int $projectId, string $expectedVersion, array $values): array
    {
        $project = $this->projectsRepository->getProject($projectId);
        if ($project === false) {
            throw new McpException('Project not found', -32004, 404);
        }

        if ($project['modified'] !== $expectedVersion) {
            throw new McpException('Project version conflict', -32009, 409, [
                'projectId' => $projectId,
                'currentVersion' => $project['modified'],
            ]);
        }

        $clientId = (int) ($values['clientId'] ?? $project['clientId']);
        if ($this->clientsRepository->getClient($clientId) === false) {
            throw new McpException('Client not found', -32602, 400);
        }

        $state = (string) ($values['state'] ?? $project['state'] ?? '');
        if ($state === '1' || $state === 1) {
            if ($this->projectsRepository->hasTickets($projectId)) {
                throw new McpException('Projects with tickets cannot be closed', -32009, 409);
            }
        }

        $psettings = (string) ($values['psettings'] ?? $project['psettings']);
        if (! in_array($psettings, ['restricted', 'clients', 'all'], true)) {
            throw new McpException('Invalid project access setting', -32602, 400);
        }

        $payload = [
            'name' => trim((string) ($values['name'] ?? $project['name'])),
            'details' => (string) ($values['details'] ?? $project['details']),
            'clientId' => $clientId,
            'state' => $values['state'] ?? $project['state'],
            'hourBudget' => $values['hourBudget'] ?? $project['hourBudget'],
            'dollarBudget' => $values['dollarBudget'] ?? $project['dollarBudget'],
            'psettings' => $psettings,
            'menuType' => (string) ($values['menuType'] ?? $project['menuType'] ?? Menu::DEFAULT_MENU),
            'type' => (string) ($values['type'] ?? $project['type'] ?? 'project'),
            'parent' => $values['parent'] ?? $project['parent'],
            'start' => $values['start'] ?? $project['start'],
            'end' => $values['end'] ?? $project['end'],
        ];

        if ($payload['name'] === '') {
            throw new McpException('Project name is required', -32602, 400);
        }

        $this->projectsRepository->editProject($payload, $projectId);

        $notification = app()->make(Notification::class);
        $notification->url = [
            'url' => BASE_URL.'/projects/showProject/'.$projectId,
            'text' => $this->language->__('email_notifications.project_update_cta'),
        ];
        $notification->entity = $project;
        $notification->module = 'projects';
        $notification->action = 'updated';
        $notification->projectId = $projectId;
        $notification->subject = sprintf($this->language->__('email_notifications.project_update_subject'), $projectId, $payload['name']);
        $notification->authorId = $principal->userId;
        $notification->message = sprintf($this->language->__('email_notifications.project_update_message'), $principal->user['firstname'] ?? $principal->tokenName, strip_tags($payload['name']));
        $this->projectsService->notifyProjectUsers($notification);

        return [
            'projectId' => $projectId,
            'project' => $this->projectsRepository->getProject($projectId),
        ];
    }

    public function updateProjectMembers(int $projectId, string $expectedVersion, array $members): array
    {
        $project = $this->projectsRepository->getProject($projectId);
        if ($project === false) {
            throw new McpException('Project not found', -32004, 404);
        }

        if ($project['modified'] !== $expectedVersion) {
            throw new McpException('Project version conflict', -32009, 409, [
                'projectId' => $projectId,
                'currentVersion' => $project['modified'],
            ]);
        }

        $desiredMembers = [];
        foreach ($members as $member) {
            $userId = (int) ($member['userId'] ?? 0);
            if ($userId <= 0) {
                throw new McpException('Each member must include a valid userId', -32602, 400);
            }

            if ($this->usersRepository->getUser($userId) === false) {
                throw new McpException('Project member user not found', -32602, 400, ['userId' => $userId]);
            }

            $projectRoleId = $member['projectRoleId'] ?? null;
            if ($projectRoleId !== null) {
                $projectRoleId = (int) $projectRoleId;
                if (! in_array($projectRoleId, [5, 10, 20, 30], true)) {
                    throw new McpException('projectRoleId must be one of 5, 10, 20, 30 or null', -32602, 400);
                }
            }

            $desiredMembers[$userId] = $projectRoleId === null ? '' : Roles::getRoleString($projectRoleId);
        }

        $currentMembers = $this->projectsService->getUsersAssignedToProject($projectId, true);
        $currentMap = [];
        foreach ($currentMembers as $member) {
            $currentMap[(int) $member['id']] = (string) ($member['projectRole'] ?? '');
        }

        $this->connection->transaction(function () use ($projectId, $desiredMembers, $currentMap) {
            foreach ($currentMap as $userId => $currentRole) {
                if (! array_key_exists($userId, $desiredMembers)) {
                    $this->projectsRepository->deleteProjectRelation($userId, $projectId);
                }
            }

            foreach ($desiredMembers as $userId => $desiredRole) {
                if (! array_key_exists($userId, $currentMap)) {
                    $this->projectsRepository->addProjectRelation($userId, $projectId, $desiredRole);
                    continue;
                }

                if ((string) $currentMap[$userId] !== (string) $desiredRole) {
                    $this->projectsRepository->deleteProjectRelation($userId, $projectId);
                    $this->projectsRepository->addProjectRelation($userId, $projectId, $desiredRole);
                }
            }

            $this->connection->table('zp_projects')
                ->where('id', $projectId)
                ->update(['modified' => date('Y-m-d H:i:s')]);
        });

        return [
            'projectId' => $projectId,
            'project' => $this->projectsRepository->getProject($projectId),
            'members' => $this->projectsService->getUsersAssignedToProject($projectId, true),
        ];
    }
}
