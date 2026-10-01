<?php

declare(strict_types=1);

namespace Leantime\Domain\Reports\Models;

use Carbon\CarbonImmutable;

/**
 * Value object describing the reporting timeframe of a report screen.
 *
 * All boundaries are held in UTC (db timezone). Quarter presets are resolved against the
 * user's timezone so "this quarter" matches the user's calendar, then converted to UTC.
 */
final class ReportPeriod
{
    public const PRESET_LAST_QUARTER = 'lastQuarter';

    public const PRESET_THIS_QUARTER = 'thisQuarter';

    public const PRESET_NEXT_QUARTER = 'nextQuarter';

    public const PRESET_CUSTOM = 'custom';

    private function __construct(
        public readonly CarbonImmutable $from,
        public readonly CarbonImmutable $to,
        public readonly string $preset,
    ) {}

    /**
     * Builds a period from request parameters.
     *
     * Accepts either a `preset` (lastQuarter|thisQuarter|nextQuarter) or a custom range via
     * `from`/`to` in the user's date format and timezone. Falls back to the current quarter
     * when nothing (or nothing parseable) was provided.
     *
     * @param  array<string, mixed>  $params  Request parameters
     */
    public static function fromRequest(array $params): self
    {
        $preset = (string) ($params['preset'] ?? '');

        if ($preset === self::PRESET_LAST_QUARTER) {
            return self::lastQuarter();
        }

        if ($preset === self::PRESET_NEXT_QUARTER) {
            return self::nextQuarter();
        }

        if ($preset === self::PRESET_CUSTOM || (! empty($params['from']) && ! empty($params['to']))) {
            try {
                $from = dtHelper()->parseUserDateTime((string) $params['from'], 'start')->setToDbTimezone();
                $to = dtHelper()->parseUserDateTime((string) $params['to'], 'end')->setToDbTimezone();

                if ($from->lessThanOrEqualTo($to)) {
                    return new self($from, $to, self::PRESET_CUSTOM);
                }
            } catch (\Exception $e) {
                // Unparseable custom range: fall through to the default preset.
            }
        }

        return self::thisQuarter();
    }

    /**
     * The current quarter in the user's calendar.
     */
    public static function thisQuarter(): self
    {
        $now = dtHelper()->userNow();

        return new self(
            $now->startOfQuarter()->setToDbTimezone(),
            $now->endOfQuarter()->setToDbTimezone(),
            self::PRESET_THIS_QUARTER,
        );
    }

    /**
     * The previous full quarter in the user's calendar.
     */
    public static function lastQuarter(): self
    {
        $anchor = dtHelper()->userNow()->subQuarterNoOverflow();

        return new self(
            $anchor->startOfQuarter()->setToDbTimezone(),
            $anchor->endOfQuarter()->setToDbTimezone(),
            self::PRESET_LAST_QUARTER,
        );
    }

    /**
     * The next quarter in the user's calendar.
     */
    public static function nextQuarter(): self
    {
        $anchor = dtHelper()->userNow()->addQuarterNoOverflow();

        return new self(
            $anchor->startOfQuarter()->setToDbTimezone(),
            $anchor->endOfQuarter()->setToDbTimezone(),
            self::PRESET_NEXT_QUARTER,
        );
    }

    /**
     * The equivalent preceding period, used for period-over-period deltas. For quarter presets
     * this is the previous quarter; for custom ranges the same number of days directly before.
     */
    public function priorPeriod(): self
    {
        if ($this->preset !== self::PRESET_CUSTOM) {
            $anchor = $this->from->setToUserTimezone()->subQuarterNoOverflow();

            return new self(
                $anchor->startOfQuarter()->setToDbTimezone(),
                $anchor->endOfQuarter()->setToDbTimezone(),
                self::PRESET_CUSTOM,
            );
        }

        $lengthInDays = (int) $this->from->diffInDays($this->to) + 1;

        return new self(
            $this->from->subDays($lengthInDays),
            $this->to->subDays($lengthInDays),
            self::PRESET_CUSTOM,
        );
    }

    /**
     * End of the "coming up" horizon: two full quarters beyond the period end.
     */
    public function upcomingHorizon(): CarbonImmutable
    {
        return $this->to->setToUserTimezone()->addQuartersNoOverflow(2)->endOfQuarter()->setToDbTimezone();
    }

    /**
     * Whether the given UTC datetime falls inside the period.
     */
    public function contains(CarbonImmutable $dateTime): bool
    {
        return $dateTime->betweenIncluded($this->from, $this->to);
    }

    /**
     * Human readable label, e.g. "Q2 2026 · Apr 1 – Jun 30, 2026".
     */
    public function label(): string
    {
        $userFrom = $this->from->setToUserTimezone();
        $userTo = $this->to->setToUserTimezone();

        $range = $userFrom->formatDateForUser().' – '.$userTo->formatDateForUser();

        // A range spanning exactly one calendar quarter gets the quarter shorthand prefix.
        if (
            $userFrom->equalTo($userFrom->startOfQuarter())
            && $userTo->equalTo($userTo->endOfQuarter())
            && $userFrom->isSameQuarter($userTo)
        ) {
            return 'Q'.$userFrom->quarter.' '.$userFrom->year.' · '.$range;
        }

        return $range;
    }

    /**
     * Query string carrying the period across filter swaps and drill-down links.
     */
    public function toQueryString(): string
    {
        if ($this->preset !== self::PRESET_CUSTOM) {
            return http_build_query(['preset' => $this->preset]);
        }

        return http_build_query([
            'preset' => self::PRESET_CUSTOM,
            'from' => $this->from->setToUserTimezone()->formatDateForUser(),
            'to' => $this->to->setToUserTimezone()->formatDateForUser(),
        ]);
    }

    /**
     * Period start formatted for db comparisons (UTC, Y-m-d H:i:s).
     */
    public function fromDbString(): string
    {
        return $this->from->format('Y-m-d H:i:s');
    }

    /**
     * Period end formatted for db comparisons (UTC, Y-m-d H:i:s).
     */
    public function toDbString(): string
    {
        return $this->to->format('Y-m-d H:i:s');
    }
}
