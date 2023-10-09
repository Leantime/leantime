<?php

namespace Leantime\Domain\Install\Controllers {

    use Leantime\Core\Frontcontroller as FrontcontrollerCore;
    use Leantime\Core\Controller;
    use Leantime\Domain\Install\Repositories\Install as InstallRepository;

    /**
     *
     */
    class Index extends Controller
    {
        private InstallRepository $installRepo;

        /**
         * init - initialize private variables
         *
         * @access public
         */
        public function init(InstallRepository $installRepo)
        {
            $this->installRepo = $installRepo;

            if ($this->installRepo->checkIfInstalled()) {
                FrontcontrollerCore::redirect(BASE_URL);
            }
        }

        /**
         * get - handle get requests
         *
         * @access public
         * @params parameters or body of the request
         */
        public function get($params)
        {
            $this->tpl->display("install.new", "entry");
        }

        /**
         * @param $params
         * @return void
         */
        public function post($params): void
        {

            $values = array(
                'email'         => "",
                'password'      => "",
                'firstname'     => "",
                'lastname'      => "",
            );

            if (isset($_POST['install'])) {
                $values = array(
                    'email' => ($params['email']),
                    'password' => $params['password'],
                    'firstname' => ($params['firstname']),
                    'lastname' => ($params['lastname']),
                    'company' => ($params['company']),
                );

                if (!isset($params['email']) || $params['email'] == '') {
                    $this->tpl->setNotification("notification.enter_email", "error");
                } else {
                    if (!isset($params['password']) || $params['password'] == '') {
                        $this->tpl->setNotification("notification.enter_password", "error");
                    } else {
                        if (!isset($params['firstname']) || $params['firstname'] == '') {
                            $this->tpl->setNotification("notification.enter_firstname", "error");
                        } else {
                            if (!isset($params['lastname']) || $params['lastname'] == '') {
                                $this->tpl->setNotification("notification.enter_lastname", "error");
                            } else {
                                if (!isset($params['company']) || $params['company'] == '') {
                                    $this->tpl->setNotification("notification.enter_company", "error");
                                    ;
                                } else {
                                    $values['password'] = $_POST['password'];

                                    if ($this->installRepo->setupDB($values)) {
                                        $this->tpl->setNotification(sprintf($this->language->__("notifications.installation_success"), BASE_URL), "success");
                                    } else {
                                        $this->tpl->setNotification($this->language->__('notification.error_installing'), "error");
                                    }
                                }
                            }
                        }
                    }
                }
            }

            FrontcontrollerCore::redirect(BASE_URL . "/install");
        }
    }
}
