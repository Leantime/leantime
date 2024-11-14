<?php

namespace Leantime\Domain\Clients\Services;

use Leantime\Core\UI\Template as TemplateCore;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;

/**
 * Class Clients
 */
class Clients
{
    private ProjectRepository $projectRepository;

    private ClientRepository $clientRepository;

    /**
     * @param  TemplateCore  $tpl
     */
    public function __construct(
        ProjectRepository $projectRepository,
        ClientRepository $clientRepository,
    ) {
        $this->projectRepository = $projectRepository;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @api
     */
    public function getUserClients(int $userId): array
    {
        $userProjects = $this->projectRepository->getUserProjects($userId);
        $clients = [];

        if (is_array($userProjects)) {
            $userClients = [];
            foreach ($userProjects as $project) {
                if (! array_key_exists($project['clientId'], $clients)) {
                    $clients[$project['clientId']] = ['id' => $project['clientId'], 'name' => $project['clientName']];
                }
            }
        }

        return $clients;
    }

    /**
     * @api
     */
    public function getAll(?array $searchparams = null): array
    {
        return $this->clientRepository->getAll();
    }

    /**
     * patches the client by key.
     *
     * @param  int  $id  Id of the object to be patched
     * @param  array  $params  Key=>value array where key represents the object field name and value the value.
     * @return bool returns true on success, false on failure
     *
     * @api
     */
    public function patch(int $id, array $params): bool
    {
        return $this->clientRepository->patch($id, $params);
    }

    /**
     * updates the client by key.
     *
     * @param  object|array  $values  expects the entire object to be updated as object or array
     * @return bool Returns true on success, false on failure
     *
     * @api
     */
    public function editClient(object|array $values): bool
    {
        return $this->clientRepository->editClient($values, $values['id']);
    }

    /**
     * Creates a new client
     *
     * @param  object|array  $values  Object or array to be created
     * @return int|false Returns id of new element or false
     *
     * @api
     */
    public function create(object|array $values): int|false
    {
        return $this->clientRepository->addClient($values);
    }

    /**
     * Deletes a client
     *
     * @param  int  $id  Id of the object to be deleted
     * @return bool Returns id of new element or false
     *
     * @api
     */
    public function delete(int $id): bool
    {
        return $this->clientRepository->deleteClient($id);
    }

    /**
     * Gets 1 specific client by id
     *
     * @param  int  $id  Id of the object to be retrieved
     * @return object|array|false Returns object or array. False on failure or if item cannot be found
     *
     * @api
     */
    public function get(int $id): object|array|false
    {
        return $this->clientRepository->getClient($id);
    }
}
