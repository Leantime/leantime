<?php

namespace leantime\domain\controllers {

    use leantime\core;
    use leantime\domain\repositories;
    use leantime\domain\services;
    use leantime\domain\models;

    class index
    {

        private $tpl;
        private $usersService;
        private $redirectUrl;
        private $language;


        /**
         * constructor - initialize private variables
         *
         * @access public
         * @params parameters or body of the request
         */
        public function __construct()
        {

            $this->tpl = new core\template();
            $this->installRepo = new repositories\install();
            $this->language = new core\language();

            if ($this->installRepo->checkIfInstalled()) {
               core\frontcontroller::redirect(BASE_URL);
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

            $this->tpl->display("install.new", 200, "entry");

        }

        public function post($params) {

            $values = array(
                'email'			=>"",
                'password'		=>"",
                'firstname'		=>"",
                'lastname'		=>""
            );

            if(isset($_POST['install'])) {

                $values = array(
                    'email' => ($params['email']),
                    'password' => $params['password'],
                    'firstname' => ($params['firstname']),
                    'lastname' => ($params['lastname']),
                    'company' => ($params['company'])
                );

                if (isset($params['email']) == false || $params['email'] == '') {
                    $this->tpl->setNotification("notification.enter_email", "error");
                } else {
                    if (isset($params['password']) == false || $params['password'] == '') {
                        $this->tpl->setNotification("notification.enter_password", "error");
                    } else {
                        if (isset($params['firstname']) == false || $params['firstname'] == '') {
                            $this->tpl->setNotification("notification.enter_firstname", "error");
                        } else {
                            if (isset($params['lastname']) == false || $params['lastname'] == '') {
                                $this->tpl->setNotification("notification.enter_lastname", "error");
                            } else {
                                if (isset($params['company']) == false || $params['company'] == '') {
                                    $this->tpl->setNotification("notification.enter_company", "error");;
                                } else {
                                    $values['password'] = password_hash($_POST['password'], PASSWORD_DEFAULT);

                                    if($this->installRepo->setupDB($values)) {
                                        $this->tpl->setNotification(sprintf($this->language->__("notifications.installation_success"),BASE_URL), "success");
                                    }else{
                                        $this->tpl->setNotification($this->language->__('notification.error_installing'), "error");
                                    }

                                }
                            }
                        }
                    }
                }
            }

            core\frontcontroller::redirect( BASE_URL."/install");
        }
    }
}
