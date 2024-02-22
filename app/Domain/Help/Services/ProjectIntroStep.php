<?php

namespace Leantime\Domain\Help\Services;

use Leantime\Core\Eventhelpers;
use Leantime\Domain\Help\Contracts\OnboardingSteps;

/**
 *
 */
class ProjectIntroStep implements OnboardingSteps
{
    use Eventhelpers;

    public function getTitle(): string
    {
        // code here
    }

    public function getAction() : string{
        // TODO: Implement getAction() method.
    }

    public function getTemplate() : string{
        return "help.";
    }


    public function handle(): bool
    {

    }

}
