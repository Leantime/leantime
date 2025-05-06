<?php

namespace Leantime\Domain\Api\Controllers;

use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Http\Controller\Controller;
use Leantime\Domain\Setting\Services\Setting as SettingService;
use Symfony\Component\HttpFoundation\Response;

class Setting extends Controller
{
    private SettingService $settingService;

    /**
     * init - initialize private variables
     */
    public function init(SettingService $settingService): void
    {
        $this->settingService = $settingService;
    }

    /**
     * get - handle get requests
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function get(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * post - Updatind User Image
     *
     *
     * @param  array  $params  parameters or body of the request
     *
     * @throws BindingResolutionException
     */
    public function post(array $params): Response
    {
        if (! isset($_FILES['file'])) {
            return $this->tpl->displayJson(['status' => 'failure'], 500);
        }

        $_FILES['file']['name'] = 'logo.png';

        $this->settingService->setLogo($_FILES);

        session(['msg' => 'PICTURE_CHANGED']);
        session(['msgT' => 'success']);

        return $this->tpl->displayJson(['status' => 'ok']);
    }

    /**
     * put - handle put requests
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function patch(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }

    /**
     * delete - handle delete requests
     *
     *
     * @param  array  $params  parameters or body of the request
     */
    public function delete(array $params): Response
    {
        return $this->tpl->displayJson(['status' => 'Not implemented'], 501);
    }
}
