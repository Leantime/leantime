<?php

namespace Leantime\Domain\Clients\Services;

use Leantime\Core\Template as TemplateCore;
use Leantime\Core\Eventhelpers;
use Leantime\Core\Template;
use Leantime\Domain\Projects\Repositories\Projects as ProjectRepository;
use Leantime\Domain\Clients\Repositories\Clients as ClientRepository;
use Leantime\Domain\Projects\Repositories\Projects;

/**
 *
 */

/**
 *
 */
class Clients
{
    private TemplateCore $tpl;
    private ProjectRepository $projectRepository;
    private ClientRepository $clientRepository;

    /**
     * @param TemplateCore      $tpl
     * @param ProjectRepository $projectRepository
     * @param ClientRepository  $clientRepository
     */
    public function __construct(
        TemplateCore $tpl,
        ProjectRepository $projectRepository,
        ClientRepository $clientRepository,
    ) {
        $this->tpl = $tpl;
        $this->projectRepository = $projectRepository;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @param int $userId
     * @return array
     */
    public function getUserClients(int $userId): array
    {
        $userProjects = $this->projectRepository->getUserProjects($userId);
        $clients = [];

        if (is_array($userProjects)) {
            $userClients = [];
            foreach ($userProjects as $project) {
                if (!array_key_exists($project["clientId"], $clients)) {
                    $clients[$project["clientId"]] = array("id" => $project["clientId"], "name" => $project['clientName']);
                }
            }
        }

        return $clients;
    }

    /**
     * @return array
     */
    public function getAll(): array
    {
        return $this->clientRepository->getAll();
    }
}
