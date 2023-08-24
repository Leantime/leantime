<?php

namespace leantime\domain\services;

use leantime\core;
use leantime\core\eventhelpers;
use leantime\core\template;
use leantime\domain\repositories;
use leantime\domain\repositories\projects;

class clients
{
    private core\template $tpl;
    private repositories\projects $projectRepository;
    private repositories\clients $clientRepository;

    /**
     * @param core\template         $tpl
     * @param repositories\projects $projectRepository
     */
    public function __construct(
        core\template $tpl,
        repositories\projects $projectRepository,
        repositories\clients $clientRepository,
    ) {
        $this->tpl = $tpl;
        $this->projectRepository = $projectRepository;
        $this->clientRepository = $clientRepository;
    }

    /**
     * @param integer $userId
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
