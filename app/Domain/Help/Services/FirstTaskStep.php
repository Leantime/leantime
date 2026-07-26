<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Events\DispatchesEvents;
use Leantime\Core\Exceptions\AuthorizationException;
use Leantime\Domain\Help\Contracts\OnboardingSteps;
use Leantime\Domain\Setting\Repositories\Setting;
use Leantime\Domain\Tickets\Services\Tickets;
use Throwable;

class FirstTaskStep implements OnboardingSteps
{
    use DispatchesEvents;

    public function __construct(
        private Setting $settingsRepo,
        private Tickets $ticketService
    ) {}

    /**
     * Get the title of the current project.
     *
     * @return string The title of the current project.
     */
    public function getTitle(): string
    {
        return 'Name your first task';
    }

    /**
     * Get the action for the current request.
     *
     * @return string The action for the current request.
     */
    public function getAction(): string
    {
        // TODO: Implement getAction() method.
        return 'ProjectIntro';
    }

    /**
     * Retrieves the template for the project introduction step.
     *
     * @return string The template name for the project introduction step.
     */
    public function getTemplate(): string
    {
        return 'help.firstTaskStep';
    }

    /**
     * Handle the given parameters.
     *
     * Persisting the firstLoginCompleted flag MUST NOT depend on the optional
     * first-task creation succeeding. Users without TicketsPermissions::CREATE
     * (e.g. the readonly role) would otherwise have quickAddTicket() throw a
     * permission error before the flag was ever written, trapping them in an
     * infinite onboarding modal loop (see GH #3683). The ticket creation is a
     * best-effort convenience; the completion flag is the load-bearing write.
     *
     * @param  array  $params  The parameters passed to the handle method.
     * @return bool Returns true on success.
     */
    public function handle($params): bool
    {
        $headline = isset($params['headline']) && is_string($params['headline'])
            ? trim($params['headline'])
            : '';

        if ($headline !== '') {
            try {
                $this->ticketService->quickAddTicket(['headline' => $headline]);
            } catch (AuthorizationException $e) {
                // Expected: the readonly role has no TicketsPermissions::CREATE. This is
                // a normal outcome, not an incident, so it is not reported — otherwise
                // every readonly first login would look like a recurring error to ops.
            } catch (Throwable $e) {
                // Anything else is genuinely unexpected and worth surfacing, but it
                // still must not block the completion flag write below.
                report($e);
            }
        }

        $this->settingsRepo->saveSetting('user.'.session()->get('userdata.id', -1).'.firstLoginCompleted', true);

        return true;
    }
}
