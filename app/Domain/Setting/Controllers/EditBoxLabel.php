<?php

namespace Leantime\Domain\Setting\Controllers;

use Illuminate\Support\Facades\Cache;
use Leantime\Core\Controller\Controller;
use Leantime\Domain\Auth\Models\Roles;
use Leantime\Domain\Auth\Services\Auth;
use Leantime\Domain\Ideas\Repositories\Ideas as IdeaRepository;
use Leantime\Domain\Leancanvas\Repositories\Leancanvas as LeancanvaRepository;
use Leantime\Domain\Retroscanvas\Repositories\Retroscanvas as RetroscanvaRepository;
use Leantime\Domain\Setting\Repositories\Setting as SettingRepository;
use Leantime\Domain\Tickets\Repositories\Tickets as TicketRepository;

class EditBoxLabel extends Controller
{
    private TicketRepository $ticketsRepo;

    private SettingRepository $settingsRepo;

    private LeancanvaRepository $canvasRepo;

    private RetroscanvaRepository $retroRepo;

    private IdeaRepository $ideaRepo;

    /**
     * init - initialize private variables
     */
    public function init(
        TicketRepository $ticketsRepo,
        SettingRepository $settingsRepo,
        LeancanvaRepository $canvasRepo,
        RetroscanvaRepository $retroRepo,
        IdeaRepository $ideaRepo
    ) {
        Auth::authOrRedirect([Roles::$owner, Roles::$admin, Roles::$manager]);

        $this->ticketsRepo = $ticketsRepo;
        $this->settingsRepo = $settingsRepo;
        $this->canvasRepo = $canvasRepo;
        $this->retroRepo = $retroRepo;
        $this->ideaRepo = $ideaRepo;
    }

    /**
     * get - handle get requests
     */
    public function get($params)
    {

        if (Auth::userIsAtLeast(Roles::$manager)) {
            $currentLabel = '';

            if (isset($params['module']) && isset($params['label'])) {
                $module = htmlspecialchars($params['module'], ENT_QUOTES, 'UTF-8');
                $label = filter_var($params['label'], FILTER_SANITIZE_NUMBER_INT);

                // Move to settings service
                if ($module === 'ticketlabels') {
                    $stateLabels = $this->ticketsRepo->getStateLabels();
                    if (isset($stateLabels[$label]['name'])) {
                        $currentLabel = $stateLabels[$label]['name'];
                    }
                }

                if ($module === 'retrolabels') {
                    $stateLabels = $this->retroRepo->getCanvasLabels();
                    if (isset($stateLabels[$label])) {
                        $currentLabel = $stateLabels[$label];
                    }
                }

                if ($module === 'researchlabels') {
                    $stateLabels = $this->canvasRepo->getCanvasLabels();
                    if (isset($stateLabels[$label])) {
                        $currentLabel = $stateLabels[$label];
                    }
                }

                if ($module === 'idealabels') {
                    $stateLabels = $this->ideaRepo->getCanvasLabels();
                    if (isset($stateLabels[$label]['name'])) {
                        $currentLabel = $stateLabels[$label]['name'];
                    }
                }
            }

            $this->tpl->assign('currentLabel', $currentLabel);

            return $this->tpl->displayPartial('setting.editBoxDialog');
        } else {
            return $this->tpl->display('errors.error403');
        }
    }

    /**
     * post - handle post requests
     */
    public function post($params)
    {
        // If ID is set its an update
        $sanitizedString = '';
        if (isset($_GET['module']) && isset($_GET['label'])) {
            $module = htmlspecialchars($_GET['module'], ENT_QUOTES, 'UTF-8');
            $labelKey = filter_var($_GET['label'], FILTER_SANITIZE_NUMBER_INT);
            $sanitizedString = htmlspecialchars(strip_tags($params['newLabel'] ?? ''), ENT_QUOTES, 'UTF-8');

            // Move to settings service
            if ($module === 'ticketlabels') {
                $currentStateLabels = $this->ticketsRepo->getStateLabels();

                if (isset($currentStateLabels[$labelKey]) && is_array($currentStateLabels[$labelKey])) {
                    $currentStateLabels[$labelKey]['name'] = $sanitizedString;

                    $this->settingsRepo->saveSetting(
                        'projectsettings.'.session('currentProject').'.ticketlabels',
                        serialize($currentStateLabels)
                    );

                    Cache::forget('projectsettings.'.session('currentProject').'.ticketlabels');
                }
            }

            if ($module === 'retrolabels') {
                $stateLabels = $this->retroRepo->getCanvasLabels();
                $stateLabels[$labelKey] = $sanitizedString;
                session()->forget('projectsettings.retrolabels');
                $this->settingsRepo->saveSetting(
                    'projectsettings.'.session('currentProject').'.retrolabels',
                    serialize($stateLabels)
                );
            }

            if ($module === 'researchlabels') {
                $stateLabels = $this->canvasRepo->getCanvasLabels();
                $stateLabels[$labelKey] = $sanitizedString;
                session()->forget('projectsettings.researchlabels');
                $this->settingsRepo->saveSetting(
                    'projectsettings.'.session('currentProject').'.researchlabels',
                    serialize($stateLabels)
                );
            }

            if ($module === 'idealabels') {
                $stateLabels = $this->ideaRepo->getCanvasLabels();
                $newStateLabels = [];
                foreach ($stateLabels as $key => $label) {
                    $newStateLabels[$key] = $label['name'];
                }
                $newStateLabels[$labelKey] = $sanitizedString;

                session()->forget('projectsettings.idealabels');
                $this->settingsRepo->saveSetting(
                    'projectsettings.'.session('currentProject').'.idealabels',
                    serialize($newStateLabels)
                );
            }

            $this->tpl->setNotification($this->language->__('notifications.label_changed_successfully'), 'success');
        }

        $this->tpl->assign('currentLabel', $sanitizedString);

        return $this->tpl->displayPartial('setting.editBoxDialog');
    }

    /**
     * put - handle put requests
     */
    public function put($params) {}

    /**
     * delete - handle delete requests
     */
    public function delete($params) {}
}
