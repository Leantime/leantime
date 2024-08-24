<?php

/**
 * AJAX class - Save menu state in a persistent way
 */

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Leantime\Domain\Menu\Repositories\Menu as MenuRepository;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Submenu extends Controller
{
    private MenuRepository $menuRepos;

    /**
     * constructor - initialize private variables
     *
     * @access public
     *
     * @return void
     */
    public function init(MenuRepository $menu): void
    {
        $this->menuRepos = $menu;
    }

    /**
     * get - handle get requests
     *
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
     * put - handle put requests
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     */
    public function patch(array $params): Response
    {
        if (! isset($params['submenu'], $params['state'])) {
            return $this->tpl->displayJson(['status' => false], 500);
        }

        $this->menuRepos->setSubmenuState($params['submenu'], $params['state']);
        return $this->tpl->displayJson(['status' => 'ok']);
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
