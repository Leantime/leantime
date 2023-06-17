<?php

namespace leantime\plugins\services\llamadorian {

  use leantime\plugins\repositories;
  class dashboardListener
  {
    private repositories\llamadorian $llamadorianRepository;
    public function __construct() {
      $this->llamadorianRepository = new repositories\llamadorian();
    }

    public function install() {
      $this->llamadorianRepository->install();
      return true;
    }

    public function getStatusUpdatesDue(int $dueIn = 3):array {
        //Get all projects that have a status update due within the next 3 days.
        return array();
    }

    public function getEntitiesForUpdates(array $projects):array {

        return array();
    }




  }
}
