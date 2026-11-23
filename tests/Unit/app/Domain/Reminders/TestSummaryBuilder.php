<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Domain\Reminders\Services\SummaryBuilder;

/**
 * The morning builder with its lookups replaced ({@see FakeSummaryAccess}).
 */
class TestSummaryBuilder extends SummaryBuilder
{
    use FakeSummaryAccess;
}
