<?php

namespace Leantime\Domain\Clients\Services;

use Leantime\Core\Auth\Permissions\RequiresPermission;
use Leantime\Core\Domains\BaseService;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Core\Exceptions\EntityExistsException;
use Leantime\Core\Exceptions\MissingParameterException;
use Leantime\Domain\Clients\Permissions\ClientsPermissions;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Comments\Services\Comments as CommentService;
use Leantime\Domain\Files\Services\Files as FileService;
use Leantime\Domain\Projects\Permissions\ProjectsPermissions;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Projects\Services\Projects as ProjectService;
use Leantime\Domain\Users\Repositories\Users as UserRepository;

/**
 * Client service - Business logic for client management.
 */
class Clients extends BaseService
{
    private ProjectRepository $projectRepository;

    private ClientRepository $clientRepository;

    private CommentService $commentService;

    private FileService $fileService;

    private UserRepository $userRepository;

    private ProjectService $projectService;

    public function __construct(
        ProjectRepository $projectRepository,
        ClientRepository $clientRepository,
        CommentService $commentService,
        FileService $fileService,
        UserRepository $userRepository,
        ProjectService $projectService,
    ) {
        $this->projectRepository = $projectRepository;
        $this->clientRepository = $clientRepository;
        $this->commentService = $commentService;
        $this->fileService = $fileService;
        $this->userRepository = $userRepository;
        $this->projectService = $projectService;
    }

    /**
     * Gets clients accessible by a specific user based on their project assignments.
     *
     * @param  int  $userId  The user ID
     * @return array List of clients the user has access to
     *
     * @internal Internal-only; deliberately excluded from the JSON-RPC surface. The $userId is
     *           caller-supplied, so exposing it would let any user enumerate another user's
     *           client list (IDOR). Only ever called internally with the session user's id
     *           (e.g. the roadmap/milestone client-filter dropdown).
     */
    public function getUserClients(int $userId): array
    {
        $userProjects = $this->projectRepository->getUserProjects($userId);
        $clients = [];

        if (is_array($userProjects)) {
            foreach ($userProjects as $project) {
                if (! array_key_exists($project['clientId'], $clients)) {
                    $clients[$project['clientId']] = ['id' => $project['clientId'], 'name' => $project['clientName']];
                }
            }
        }

        return $clients;
    }

    /**
     * Gets all clients.
     *
     * @param  array|null  $searchparams  Optional search parameters
     * @return array List of all clients
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::VIEW, global: true)]
    public function getAll(?array $searchparams = null): array
    {
        return $this->clientRepository->getAll();
    }

    /**
     * Patches the client by key.
     *
     * @param  int  $id  Id of the object to be patched
     * @param  array  $params  Key=>value array where key represents the object field name and value the value
     * @return bool Returns true on success, false on failure
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::EDIT, global: true)]
    public function patch(int $id, array $params): bool
    {
        return $this->clientRepository->patch($id, $params);
    }

    /**
     * Updates an existing client.
     *
     * @param  array  $values  Client data including 'id' key
     * @return bool Returns true on success, false on failure
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::EDIT, global: true)]
    public function editClient(array $values): bool
    {
        return $this->clientRepository->editClient($values, $values['id']);
    }

    /**
     * Creates a new client.
     *
     * @param  array  $values  Client data to create
     * @return int|false Returns id of new element or false
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::CREATE, global: true)]
    public function create(array $values): int|false
    {
        return $this->clientRepository->addClient($values);
    }

    /**
     * Deletes a client and its projects.
     *
     * A client whose projects hold to-dos is not deleted. Its projects are deleted through
     * {@see ProjectService::deleteProject()}, so they are cleaned up exactly like a project deleted
     * from its settings (team memberships removed, cached project access refreshed). Deleting
     * projects needs projects.delete as well; without it nothing is deleted.
     *
     * @param  int  $id  Id of the client to be deleted
     * @return bool Returns true on success, false when the client has to-dos or a project could not be deleted
     *
     * @throws AuthorizationException When the client has projects and the user may not delete projects
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::DELETE, global: true)]
    public function delete(int $id): bool
    {
        if ($this->clientRepository->hasTickets($id)) {
            return false;
        }

        $clientProjects = $this->getProjectsDeletedWithClient($id);

        if ($clientProjects !== []) {
            $this->authorize(ProjectsPermissions::DELETE);
        }

        foreach ($clientProjects as $project) {
            $projectDeleted = $this->projectService->deleteProject((int) $project['id']);
            if (! $projectDeleted) {
                return false;
            }
        }

        return $this->clientRepository->deleteClient($id);
    }

    /**
     * Gets 1 specific client by id.
     *
     * @param  int  $id  Id of the client to be retrieved
     * @return array|false Returns client data or false if not found
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::VIEW, global: true)]
    public function get(int $id): array|false
    {
        return $this->clientRepository->getClient($id);
    }

    /**
     * Checks if a client with the same name and street already exists.
     *
     * @param  array  $values  Client data with 'name' and 'street' keys
     * @return bool Returns true if client exists
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::VIEW, global: true)]
    public function isClient(array $values): bool
    {
        return $this->clientRepository->isClient($values);
    }

    /**
     * Checks if a client has any tickets via its projects.
     *
     * @param  int  $id  Client id
     * @return bool Returns true if client has tickets
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::VIEW, global: true)]
    public function hasTickets(int $id): bool
    {
        return $this->clientRepository->hasTickets($id);
    }

    /**
     * Gets all users assigned to a client.
     *
     * @param  int  $clientId  Client id
     * @return array|false Returns list of users or false
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::VIEW, global: true)]
    public function getClientsUsers(int $clientId): array|false
    {
        return $this->clientRepository->getClientsUsers($clientId);
    }

    /**
     * Gets projects belonging to a client.
     *
     * @param  int  $clientId  Client id
     * @return array List of projects for this client
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::VIEW, global: true)]
    public function getClientProjects(int $clientId): array
    {
        return $this->projectRepository->getClientProjects($clientId);
    }

    /**
     * The projects that deleting this client would delete: all of its projects, including ones
     * that lists like getClientProjects() hide. The delete dialog lists them.
     *
     * @param  int  $clientId  Client id
     * @return array<int, array{id: int, name: string}> The client's projects
     */
    #[RequiresPermission(ClientsPermissions::VIEW, global: true)]
    public function getProjectsDeletedWithClient(int $clientId): array
    {
        return $this->projectRepository->getAllProjectsOfClient($clientId);
    }

    /**
     * Creates a new client after validating it and checking for duplicates.
     *
     * Encapsulates the name-required validation and the duplicate-name check
     * that previously lived in the controller.
     *
     * @param  array  $values  Client data to create (requires a non-empty 'name')
     * @return int Id of the newly created client
     *
     * @throws MissingParameterException When the client name is empty
     * @throws EntityExistsException When a client with the same name/street already exists
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::CREATE, global: true)]
    public function createClient(array $values): int
    {
        if (($values['name'] ?? '') === '') {
            throw new MissingParameterException('Client name not specified');
        }

        if ($this->isClient($values) === true) {
            throw new EntityExistsException('Client exists already');
        }

        return (int) $this->clientRepository->addClient($values);
    }

    /**
     * Updates an existing client after validating the name is present.
     *
     * @param  array  $values  Client data including 'id' key (requires a non-empty 'name')
     * @return bool Returns true on success, false on failure
     *
     * @throws MissingParameterException When the client name is empty
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::EDIT, global: true)]
    public function updateClient(array $values): bool
    {
        if (($values['name'] ?? '') === '') {
            throw new MissingParameterException('Client name not specified');
        }

        return $this->editClient($values);
    }

    /**
     * Removes a user from a client by clearing the user's client assignment.
     *
     * Keeps the Clients controllers within the Clients service surface while the
     * underlying mutation lives on the Users repository.
     *
     * @param  int  $clientId  Client the user should be removed from
     * @param  int  $userId  User to remove from the client
     * @return bool Returns true on success, false on failure
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::EDIT, global: true)]
    public function removeUser(int $clientId, int $userId): bool
    {
        if ($clientId === 0 || $userId === 0) {
            return false;
        }

        return $this->userRepository->removeFromClient($userId);
    }

    /**
     * Assembles the template data needed to render the client detail page.
     *
     * Centralizes the shared assignment block previously duplicated across the
     * GET and POST handlers of the ShowClient controller.
     *
     * @param  int  $id  Client id
     * @return array{userClients: array|false, comments: array|false, imgExtensions: array<int, string>, clientProjects: array, files: array|false}
     *
     * @api
     */
    #[RequiresPermission(ClientsPermissions::VIEW, global: true)]
    public function getClientPageData(int $id): array
    {
        return [
            'userClients' => $this->getClientsUsers($id),
            'comments' => $this->commentService->getComments('client', $id),
            'imgExtensions' => ['jpg', 'jpeg', 'png', 'gif', 'psd', 'bmp', 'tif', 'thm', 'yuv'],
            'clientProjects' => $this->getClientProjects($id),
            'files' => $this->fileService->getFilesByModule('client', $id),
        ];
    }
}
