<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Domain\Setting\Repositories\Setting;

class Helper
{
    use DispatchesEvents;

    private $availableModals = [
        'dashboard.show' => [
            'id' => 'dashboard.show',
            'template' => 'projectDashboard',
            'tour' => 'dashboard',
            'autoLoad' => true,
        ],
        'dashboard.home' => [
            'id' => 'dashboard.home',
            'template' => 'home',
            'tour' => 'myWorkDashboard',
            'autoLoad' => true,
        ],
        'tickets.showKanban' => [
            'id' => 'tickets.showKanban',
            'template' => 'kanban',
            'tour' => '',
            'autoLoad' => true,
        ],
        'tickets.roadmap' => [
            'id' => 'tickets.roadmap',
            'template' => 'roadmap',
            'tour' => '',
            'autoLoad' => true,
        ],
        'goalcanvas.dashboard' => [
            'id' => 'goalcanvas.dashboard',
            'template' => 'goals',
            'tour' => '',
            'autoLoad' => true,
        ],
        'leancanvas.showCanvas' => [
            'id' => 'leancanvas.showCanvas',
            'template' => 'fullLeanCanvas',
            'tour' => '',
            'autoLoad' => false,
        ],
        'leancanvas.simpleCanvas' => [
            'id' => 'leancanvas.simpleCanvas',
            'template' => 'simpleLeanCanvas',
            'tour' => '',
            'autoLoad' => false,
        ],
        'ideas.showBoards' => [
            'id' => 'ideas.showBoards',
            'template' => 'ideaBoard',
            'tour' => '',
            'autoLoad' => false,
        ],
        'ideas.advancedBoards' => [
            'id' => 'ideas.advancedBoards',
            'template' => 'advancedBoards',
            'tour' => '',
            'autoLoad' => false,
        ],
        'retroscanvas.showBoards' => [
            'id' => 'retroscanvas.showBoards',
            'template' => 'retroscanvas',
            'tour' => '',
            'autoLoad' => false,
        ],
        'timesheets.showMy' => [
            'id' => 'timesheets.showMy',
            'template' => 'mytimesheets',
            'tour' => '',
            'autoLoad' => false,
        ],
        'projects.newProject' => [
            'id' => 'projects.newProject',
            'template' => 'newProject',
            'tour' => '',
            'autoLoad' => false,
        ],
        'projects.showAll' => [
            'id' => 'projects.showAll',
            'template' => 'showProjects',
            'tour' => '',
            'autoLoad' => false,
        ],
        'clients.showAll' => [
            'id' => 'clients.showAll',
            'template' => 'showClients',
            'tour' => '',
            'autoLoad' => false,
        ],
        'strategy.showBoards' => [
            'id' => 'strategy.showBoards',
            'template' => 'blueprints',
            'tour' => '',
            'autoLoad' => false,
        ],
        'wiki.show' => [
            'id' => 'wiki.show',
            'template' => 'wiki',
            'tour' => '',
            'autoLoad' => false,
        ],

    ];

    /**
     * Constructor for the class.
     * Initializes the availableModals property by dispatching the "addHelperModal" event.
     *
     * @param  \Leantime\Domain\Users\Repositories\Users  $userRepository
     * @return void
     */
    public function __construct(private Setting $settingsRepo)
    {

        $this->availableModals = self::dispatch_filter('addHelperModal', $this->availableModals);
    }

    /**
     * Returns an array of all available helper modals.
     *
     * @return array The array of available helper modals.
     */
    public function getAllHelperModals(): array
    {
        return $this->availableModals;
    }

    /**
     * Retrieves the corresponding helper modal for a given route.
     *
     * @param  string  $route  The route for which to retrieve the helper modal.
     * @return array|string The helper modal associated with the given route. If not found, 'notfound' is returned.
     */
    public function getHelperModalByRoute(string $route): array
    {
        return $this->availableModals[$route] ?? ['template' => 'notfound'];
    }

    /**
     * Retrieves the first login steps.
     *
     * This method returns an array of steps that a user needs to follow during the first login.
     *
     * Each step consists of a template and a button label.
     *
     * @return array The first login steps.
     */
    public function getFirstLoginSteps(): array
    {
        $steps = [
            0 => ['class' => "Leantime\Domain\Help\Services\FirstTaskStep", 'next' => 'end'],
        ];

        // make array of onboarding steps.
        $steps = self::dispatch_filter('filterSteps', $steps);

        return $steps;
    }

    /**
     * Checks if this is the user's first login
     *
     * @param  int  $userId  The user ID to check
     * @return bool True if this is the first login, false otherwise
     */
    public function isFirstLogin(int $userId): bool
    {
        $onboardingComplete = $this->settingsRepo->getSetting('user.'.$userId.'.firstLoginCompleted');

        return ! isset($onboardingComplete) || $onboardingComplete === false;
    }

    /**
     * Marks the first login as completed for a user
     *
     * @param  int  $userId  The user ID to update
     * @return bool Success status
     */
    public function markFirstLoginComplete(int $userId): bool
    {

        return $this->settingsRepo->saveSetting('user.'.$userId.'.firstLoginCompleted', true);

    }

    /**
     * Saves the user's first task
     *
     * @param  int  $userId  The user ID
     * @param  string  $taskText  The task text entered by the user
     * @return bool Success status
     */
    public function saveFirstTask(int $userId, string $taskText): bool
    {
        return $this->userRepository->updateUserSettings($userId, ['onboarding.firstTask' => $taskText]);
    }

    public function getOnboardingChecklist(int $userId): array|false
    {

        $checklist = $this->settingsRepo->getSetting('user.'.$userId.'.onboardingChecklist');
        $checklist = json_decode($checklist, true);

        //        if(!$checklist) {
        //            return false;
        //        }

        // Checklist Debug
        $checklist = [
            'step1' => [
                'completed' => true,
                'label' => 'Create your first task',
            ],
            'step2' => [
                'completed' => false,
                'label' => 'Complete the My Work Dashboard Tour',
            ],
            'step3' => [
                'completed' => false,
                'label' => 'Review your personal project',
                'url' => '',
            ],
            'step4' => [
                'completed' => false,
                'label' => 'Review your project',
                'url' => '',
            ],
            'step5' => [
                'completed' => false,
                'label' => 'Create a milestone',
                'url' => '',
            ],
            'step6' => [
                'completed' => false,
                'label' => 'Create a goal',
                'url' => '',
            ],
            'step7' => [
                'completed' => false,
                'label' => 'Comment on a task',
                'url' => '',
            ],

        ];

        return $checklist;

    }
}
