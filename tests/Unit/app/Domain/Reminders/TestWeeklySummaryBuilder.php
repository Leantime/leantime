<?php

namespace Unit\app\Domain\Reminders;

use Leantime\Domain\Reminders\Services\WeeklySummaryBuilder;

/**
 * The weekly builder with its lookups replaced ({@see FakeSummaryAccess}).
 */
class TestWeeklySummaryBuilder extends WeeklySummaryBuilder
{
    use FakeSummaryAccess;
}
