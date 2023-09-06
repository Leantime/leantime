<?php

namespace Leantime\Domain\Auth\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
use Leantime\Core\Controller;
    use Leantime\Domain\Files\Repositories\Files as FileRepository;
use Leantime\Domain\Auth\Services\Auth as AuthService;
class Logout extends Controller
    {
        private $fileRepo;
        private $authService;

        /**
         * init - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function init(FileRepository $fileRepo, AuthService $authService)
        {
            $this->fileRepo = $fileRepo;
            $this->authService = $authService;
        }


        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {

            $this->authService->logout();

            FrontcontrollerCore::redirect(BASE_URL . "/");
        }
    }

}
