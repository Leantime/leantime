<?php

namespace Tests\Unit\App\Domain\Reports\Models;

use Carbon\CarbonImmutable;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\DateTimeHelper;
use Leantime\Domain\Reports\Models\ReportPeriod;
use Unit\TestCase;

class ReportPeriodTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    protected function setUp(): void
    {
        parent::setUp();

        $environmentMock = $this->make(Environment::class, [
            'defaultTimezone' => 'America/Los_Angeles',
            'language' => 'en-US',
        ]);
        app()->instance(Environment::class, $environmentMock);

        $languageMock = $this->createMock(Language::class);
        $languageMock->method('__')->willReturnCallback(function ($index) {
            $map = [
                'language.dateformat' => 'm/d/Y',
                'language.timeformat' => 'h:i A',
            ];

            return $map[$index] ?? null;
        });
        app()->instance(Language::class, $languageMock);

        // User calendar in LA (UTC-7 in summer) so quarter boundaries shift against UTC.
        CarbonImmutable::mixin(new CarbonMacros(
            'America/Los_Angeles',
            'en_US',
            'm/d/Y',
            'h:i A'
        ));

        app()->instance(DateTimeHelper::class, new DateTimeHelper);

        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-08 12:00:00', 'UTC'));
    }

    protected function tearDown(): void
    {
        CarbonImmutable::setTestNow();
        app()->forgetInstance(DateTimeHelper::class);

        parent::tearDown();
    }

    public function test_this_quarter_resolves_user_calendar_quarter_in_utc(): void
    {
        $period = ReportPeriod::thisQuarter();

        // Q3 2026 in LA: Jul 1 00:00 PDT = Jul 1 07:00 UTC, Sep 30 23:59:59 PDT = Oct 1 06:59:59 UTC.
        $this->assertSame('2026-07-01 07:00:00', $period->fromDbString());
        $this->assertSame('2026-10-01 06:59:59', $period->toDbString());
        $this->assertSame(ReportPeriod::PRESET_THIS_QUARTER, $period->preset);
    }

    public function test_last_and_next_quarter_presets(): void
    {
        $lastQuarter = ReportPeriod::lastQuarter();
        // Q2 2026 in LA starts Apr 1 00:00 PDT = Apr 1 07:00 UTC.
        $this->assertSame('2026-04-01 07:00:00', $lastQuarter->fromDbString());
        $this->assertSame('2026-07-01 06:59:59', $lastQuarter->toDbString());

        $nextQuarter = ReportPeriod::nextQuarter();
        // Q4 2026 in LA starts Oct 1 00:00 PDT = Oct 1 07:00 UTC.
        $this->assertSame('2026-10-01 07:00:00', $nextQuarter->fromDbString());
        // Dec 31 23:59:59 PST (UTC-8) = Jan 1 07:59:59 UTC.
        $this->assertSame('2027-01-01 07:59:59', $nextQuarter->toDbString());
    }

    public function test_quarter_follows_user_timezone_across_utc_quarter_boundary(): void
    {
        // Jul 1 03:00 UTC is still Jun 30 in LA — the user's "this quarter" is Q2, not Q3.
        CarbonImmutable::setTestNow(CarbonImmutable::parse('2026-07-01 03:00:00', 'UTC'));

        $period = ReportPeriod::thisQuarter();

        $this->assertSame('2026-04-01 07:00:00', $period->fromDbString());
        $this->assertSame('2026-07-01 06:59:59', $period->toDbString());
    }

    public function test_from_request_parses_presets_and_custom_ranges(): void
    {
        $preset = ReportPeriod::fromRequest(['preset' => 'lastQuarter']);
        $this->assertSame(ReportPeriod::PRESET_LAST_QUARTER, $preset->preset);

        $custom = ReportPeriod::fromRequest(['preset' => 'custom', 'from' => '04/01/2026', 'to' => '06/30/2026']);
        $this->assertSame(ReportPeriod::PRESET_CUSTOM, $custom->preset);
        $this->assertSame('2026-04-01 07:00:00', $custom->fromDbString());
        // End of day Jun 30 PDT.
        $this->assertSame('2026-07-01 06:59:59', $custom->toDbString());
    }

    public function test_from_request_falls_back_to_this_quarter(): void
    {
        $this->assertSame(ReportPeriod::PRESET_THIS_QUARTER, ReportPeriod::fromRequest([])->preset);
        $this->assertSame(
            ReportPeriod::PRESET_THIS_QUARTER,
            ReportPeriod::fromRequest(['preset' => 'custom', 'from' => 'not-a-date', 'to' => '06/30/2026'])->preset
        );
        // Inverted range is rejected.
        $this->assertSame(
            ReportPeriod::PRESET_THIS_QUARTER,
            ReportPeriod::fromRequest(['preset' => 'custom', 'from' => '06/30/2026', 'to' => '04/01/2026'])->preset
        );
    }

    public function test_prior_period_of_quarter_preset_is_previous_quarter(): void
    {
        $prior = ReportPeriod::thisQuarter()->priorPeriod();

        $this->assertSame('2026-04-01 07:00:00', $prior->fromDbString());
        $this->assertSame('2026-07-01 06:59:59', $prior->toDbString());
    }

    public function test_prior_period_of_custom_range_is_same_length_before(): void
    {
        $custom = ReportPeriod::fromRequest(['preset' => 'custom', 'from' => '06/21/2026', 'to' => '06/30/2026']);
        $prior = $custom->priorPeriod();

        // Ten-day window directly preceding Jun 21–Jun 30.
        $this->assertSame('2026-06-11 07:00:00', $prior->fromDbString());
        $this->assertSame('2026-06-21 06:59:59', $prior->toDbString());
    }

    public function test_upcoming_horizon_extends_two_quarters_past_period_end(): void
    {
        $horizon = ReportPeriod::thisQuarter()->upcomingHorizon();

        // Two quarters past Q3 2026 = end of Q1 2027 (Mar 31 23:59:59 PDT = Apr 1 06:59:59 UTC).
        $this->assertSame('2027-04-01 06:59:59', $horizon->format('Y-m-d H:i:s'));
    }

    public function test_contains_is_inclusive_of_bounds(): void
    {
        $period = ReportPeriod::thisQuarter();

        $this->assertTrue($period->contains($period->from));
        $this->assertTrue($period->contains($period->to));
        $this->assertFalse($period->contains($period->from->subSecond()));
        $this->assertFalse($period->contains($period->to->addSecond()));
    }

    public function test_query_string_round_trips(): void
    {
        $this->assertSame('preset=thisQuarter', ReportPeriod::thisQuarter()->toQueryString());

        $custom = ReportPeriod::fromRequest(['preset' => 'custom', 'from' => '04/01/2026', 'to' => '06/30/2026']);
        parse_str($custom->toQueryString(), $params);
        $roundTripped = ReportPeriod::fromRequest($params);

        $this->assertSame($custom->fromDbString(), $roundTripped->fromDbString());
        $this->assertSame($custom->toDbString(), $roundTripped->toDbString());
    }

    public function test_label_carries_quarter_shorthand_for_full_quarters(): void
    {
        $this->assertStringStartsWith('Q3 2026 · ', ReportPeriod::thisQuarter()->label());

        $custom = ReportPeriod::fromRequest(['preset' => 'custom', 'from' => '06/21/2026', 'to' => '06/30/2026']);
        $this->assertStringNotContainsString('Q2', $custom->label());
    }
}
