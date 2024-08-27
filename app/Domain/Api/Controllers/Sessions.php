<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Leantime\Domain\Users\Services\Users as UserService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Sessions extends Controller
{
    private UserService $userService;
    private MenuRepository $menu;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param UserService    $userService
     * @param MenuRepository $menu
     *
     * @return void
     */
    public function init(UserService $userService, MenuRepository $menu): void
    {
        $this->userService = $userService;
        $this->menu = $menu;
    }

    /**
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * post - handle post requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function post(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * put - Special handling for settings
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function patch(array $params): Response
    {
        if (isset($params['tourActive'])) {
            session(["tourActive" => filter_var($params['tourActive'], FILTER_SANITIZE_NUMBER_INT)]);
            return $this->tpl->displayJson(['status' => 'ok']);
        }

        if (isset($params['menuState'])) {
            session(["menuState" => htmlentities($params['menuState'])]);
            $this->menu->setSubmenuState("mainMenu", $params['menuState']);
            return $this->tpl->displayJson(['status' => 'ok']);
        }

        return $this->tpl->displayJson(['status' => 'failure'], 400);
    }

    /**
     * delete - handle delete requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}
