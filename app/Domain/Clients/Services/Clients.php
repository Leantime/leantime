<?php

namespace Leantime\Domain\Clients\Services;

use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;

/**
 * Client service - Business logic for client management.
 */
class Clients
{
    private ProjectRepository $projectRepository;

    private ClientRepository $clientRepository;

    public function __construct(
        ProjectRepository $projectRepository,
        ClientRepository $clientRepository,
    ) {
        $this->projectRepository = $projectRepository;
        $this->clientRepository = $clientRepository;
    }

    /**
     * Gets clients accessible by a specific user based on their project assignments.
     *
     * @param  int  $userId  The user ID
     * @return array List of clients the user has access to
     *
     * @api
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
    public function create(array $values): int|false
    {
        return $this->clientRepository->addClient($values);
    }

    /**
     * Deletes a client and its associated projects.
     *
     * @param  int  $id  Id of the client to be deleted
     * @return bool Returns true on success, false on failure
     *
     * @api
     */
    public function delete(int $id): bool
    {
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
    public function getClientProjects(int $clientId): array
    {
        return $this->projectRepository->getClientProjects($clientId);
    }
}
