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
        'blueprints.showBoards' => [
            'id' => 'blueprints.showBoards',
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
     * Resolves which first-login onboarding step should be displayed.
     *
     * Given an optional requested step key (from the request), this returns
     * the resolved step including its template and whether the onboarding flow
     * has reached the final "end" step. When no valid step key is provided the
     * first available step is used.
     *
     * @param  string|null  $requestedStep  The requested step key (e.g. a numeric index or "end").
     * @return array{key: int|string, next: string|int|null, template: string|null, isEnd: bool} The resolved step information.
     *
     * @api
     */
    public function resolveFirstLoginStep(?string $requestedStep): array
    {
        if ($requestedStep === 'end') {
            return [
                'key' => 'end',
                'next' => null,
                'template' => 'help.firstLoginEnd',
                'isEnd' => true,
            ];
        }

        $allSteps = $this->getFirstLoginSteps();

        $currentStepKey = collect($allSteps)->keys()->first();

        if ($requestedStep !== null && isset($allSteps[$requestedStep])) {
            $currentStepKey = (int) $requestedStep;
        }

        $currentStep = $allSteps[$currentStepKey];

        /** @var \Leantime\Domain\Help\Contracts\OnboardingSteps $stepObject */
        $stepObject = app()->make($currentStep['class']);

        return [
            'key' => $currentStepKey,
            'next' => $currentStep['next'],
            'template' => $stepObject->getTemplate(),
            'isEnd' => false,
        ];
    }

    /**
     * Handles the submission of a first-login onboarding step.
     *
     * Resolves the current step from the submitted parameters, delegates handling
     * to the step's class, and returns whether the step was valid along with the
     * key of the step the user should be redirected to next.
     *
     * @param  array  $params  The submitted request parameters. Must contain a numeric "currentStep".
     * @return array{valid: bool, next: string|int} Result of the step handling: "valid" indicates whether the
     *                                              submitted step was recognized, "next" is the step key to navigate to.
     *
     * @api
     */
    public function handleFirstLoginStep(array $params): array
    {
        $allSteps = $this->getFirstLoginSteps();

        if (
            ! isset($params['currentStep'])
            || ! is_numeric($params['currentStep'])
            || ! isset($allSteps[$params['currentStep']])
        ) {
            return ['valid' => false, 'next' => ''];
        }

        $currentStep = $allSteps[$params['currentStep']];

        /** @var \Leantime\Domain\Help\Contracts\OnboardingSteps $stepObject */
        $stepObject = app()->make($currentStep['class']);

        $result = $stepObject->handle($params);

        if ($result) {
            return ['valid' => true, 'next' => $currentStep['next']];
        }

        return ['valid' => true, 'next' => $params['currentStep']];
    }

    /**
     * Marks an onboarding modal as seen for a given module and returns its template name.
     *
     * Ensures the per-session modal tracking store exists, sanitizes the module
     * identifier, records that the modal has been shown once for this session, and
     * returns the (sanitized) template name to render.
     *
     * @param  string  $module  The module identifier whose modal should be marked as seen.
     * @return string The sanitized template name to render (without the "help." prefix).
     *
     * @api
     */
    public function markModalSeenForModule(string $module): string
    {
        $this->ensureModalSessionStore();

        $template = htmlspecialchars($module);

        if (! session()->exists('usersettings.modals.'.$template)) {
            session(['usersettings.modals.'.$template => 1]);
        }

        return $template;
    }

    /**
     * Marks an onboarding modal as seen for a given route and returns its template name.
     *
     * Sanitizes the route, resolves the matching helper modal, ensures the per-session
     * modal tracking store exists, records that the modal has been shown once for this
     * session, and returns the template name to render.
     *
     * @param  string  $route  The route identifier whose helper modal should be marked as seen.
     * @return string The template name to render (without the "help." prefix).
     *
     * @api
     */
    public function markModalSeenForRoute(string $route): string
    {
        $this->ensureModalSessionStore();

        $filteredRoute = htmlspecialchars($route);

        $modal = $this->getHelperModalByRoute($filteredRoute);

        if (! session()->exists('usersettings.modals.'.$modal['template'])) {
            session(['usersettings.modals.'.$modal['template'] => 1]);
        }

        return $modal['template'];
    }

    /**
     * Ensures the per-session modal tracking store exists.
     *
     * Initializes "usersettings.modals" to an empty array when it has not yet
     * been set so that modals are only shown once per session.
     */
    private function ensureModalSessionStore(): void
    {
        if (! session()->exists('usersettings.modals')) {
            session(['usersettings.modals' => []]);
        }
    }

    /**
     * Checks if this is the user's first login.
     *
     * NOTE: This is now a pure check with no side effects.
     * Default project creation has been moved to ensureDefaultProject()
     * which is called from the CurrentProject middleware, not from view composers.
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
     * Ensures the user has a default project.
     * Creates one if the user has no current project set.
     * Called from middleware (not from view composers) to avoid
     * write operations during template rendering.
     *
     * @param  int  $userId  The user ID to check.
     * @param  string  $role  The user's role for determining task content.
     */
    public function ensureDefaultProject(int $userId, string $role = 'editor'): void
    {
        $currentProject = session('currentProject');
        if ($currentProject === null || $currentProject === 0 || $currentProject === '' || $currentProject === false) {
            $this->createDefaultProject($userId, $role);
        }
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

    public function createDefaultProject(int $userId, string $role = 'editor')
    {

        // Create Project
        $projectService = app()->make(\Leantime\Domain\Projects\Services\Projects::class);

        $values = [
            'name' => 'My Project',
            'details' => 'Welcome to your first project in Leantime!<br />This is your space to organize tasks, track goals, and plan your work. Feel free to modify anything here or create additional projects as you grow. This project is just for you to get started',
            'clientId' => 0,
            'hourBudget' => 0,
            'assignedUsers' => [['id' => $userId, 'projectRole' => '']],
            'dollarBudget' => 0,
            'psettings' => 'restricted',
            'type' => 'project',
            'start' => null,
            'end' => null,
        ];

        $projectId = $projectService->addProject($values);

        // Create Milestone
        $ticketService = app()->make(\Leantime\Domain\Tickets\Services\Tickets::class);
        $values = [
            'headline' => '🚀 Getting Started',
            'projectId' => $projectId,
            'editorId' => $userId,
            'userId' => $userId,
            'date' => dtHelper()->userNow()->formatDateTimeForDb(),
            'editFrom' => dtHelper()->userNow()->formatDateTimeForDb(),
            'editTo' => dtHelper()->userNow()->addDays(14)->formatDateTimeForDb(),
            'tags' => '#124F7D',
        ];
        $milestoneId = $ticketService->quickAddMilestone($values);

        // Create Tasks
        $values = [
            'headline' => '',
            'description' => '',
            'projectId' => $projectId,
            'editorId' => $userId,
            'userId' => $userId,
            'dateToFinish' => dtHelper()->userNow()->addDays(3)->formatDateTimeForDb(),
            'milestone' => $milestoneId,
        ];

        $values['headline'] = '💬 Join our community chat';
        $values['description'] = 'Our community chat is a great resource to ask questions and get feedback on project set up. <a href="https://discord.gg/4zMzJtAq9z" target="_blank">Community Chat</a>';
        $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
        $ticketService->quickAddTicket($values);

        if (in_array($role, ['admin', 'owner', 'manager'])) {

            $values['headline'] = '👥 Invite your team mates';
            $values['description'] = 'Whether you are working with someone or just need an accountability buddy. Using Leantime as a group helps to stay on track and motivated <a href="'.BASE_URL.'/users/showAll">User Management</a>';
            $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
            $ticketService->quickAddTicket($values);
        }

        $values['headline'] = '🎯 Learn More about Leantime\'s Project Structure';
        $values['description'] = 'We have a lot of additional resources on our help documentation. To learn more about project structure in Leantime and best practices visit: <a href="https://support.leantime.io/en/article/getting-started-in-leantime-an-introduction-to-setting-structure-to-the-work-14t1qip/" target="_blank">https://help.leantime.io</a>';
        $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
        $ticketService->quickAddTicket($values);

        $values['headline'] = '🎯 Create a Goal';
        $values['description'] = 'Goals are used to track and measure long term objectives. They should be measurable using metrics you can update on a regular basis. Goals and Milestones can be connected to view the execution progress while viewing the metric progress <a href="'.BASE_URL.'/goalcanvas/dashboard">Project Goals</a>';
        $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
        $ticketService->quickAddTicket($values);

        $values['headline'] = '🚩 Create a Milestone';
        $values['description'] = 'Milestones allow you to categorize phases of your projects into discrete outcomes. Each milestone has a start and end date and should deliver some output <a href="'.BASE_URL.'/tickets/roadmap/">Project Milestone</a>';
        $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
        $ticketService->quickAddTicket($values);

        $values['headline'] = '🗺️ Explore your Personal Project';
        $values['description'] = 'Your personal project is a space where you can organize your tasks, goals and work. You can access it via the project selector on the top or by clicking this link here: <a href="'.BASE_URL.'/projects/changeCurrentProject/'.$projectId.'/">My Project</a>';
        $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
        $ticketService->quickAddTicket($values);

        $values['headline'] = '🖼️ Complete my Leantime profile';
        $values['description'] = 'Update profile picture and complete work preferences to personalize my experience. <a href="'.BASE_URL.'/users/editOwn/">My Profile</a>';
        $values['dateToFinish'] = dtHelper()->userNow()->addDays(1)->formatDateForUser();
        $ticketService->quickAddTicket($values);

        $values['headline'] = '📌 Create your first task';
        $values['description'] = '';
        $values['dateToFinish'] = dtHelper()->userNow();
        $values['status'] = 0;
        $ticketService->quickAddTicket($values);

        // Create Goal
        $goalService = app()->make(\Leantime\Domain\Goalcanvas\Services\Goalcanvas::class);
        $values = [
            'title' => 'My Goals',
            'author' => $userId,
            'projectId' => $projectId,
        ];
        $currentCanvasId = $goalService->createGoalboard($values);

        $values = [
            'description' => 'Tasks completed on time', // Metric
            'title' => 'Build My Productivity System', // Objective
            'box' => 'goal',
            'author' => $userId,
            'canvasId' => $currentCanvasId,
            'milestoneId' => $milestoneId,
            'startDate' => dtHelper()->userNow()->formatDateForUser(),
            'endDate' => dtHelper()->userNow()->addMonths(2)->formatDateForUser(),
            'metricType' => 'percent',
            'assignedTo' => $userId,
            'startValue' => '0',
            'currentValue' => '0',
            'endValue' => '80',
        ];

        $goalService->createGoal($values);

        $projectService->changeCurrentSessionProject($projectId);

    }
}
