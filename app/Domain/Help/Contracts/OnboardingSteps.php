<?php

namespace Leantime\Domain\Help\Contracts;

/**
 *
 */
interface OnboardingSteps
{

    public function getTitle(): string;
    public function getAction(): string;

    public function getTemplate(): string;

    public function handle($params): bool;

}
