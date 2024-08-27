<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Symfony\Component\HttpFoundation\Response;

/**
 *
 */
class Setting extends Controller
{
    private SettingService $settingService;

    /**
     * init - initialize private variables
     *
     * @access public
     *
     * @param SettingService $settingService
     *
     * @return void
     */
    public function init(SettingService $settingService): void
    {
        $this->settingService = $settingService;
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
     * post - Updatind User Image
     *
     * @access public
     *
     * @param array $params parameters or body of the request
     *
     * @return Response
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        if (! isset($_FILES['file'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        $_FILES['file']['name'] = "logo.png";

        $this->settingService->setLogo($_FILES);

        session(["msg" => "PICTURE_CHANGED"]);
        session(["msgT" => "success"]);

        return $this->tpl->displayJson(['status' => 'ok']);
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
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
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
