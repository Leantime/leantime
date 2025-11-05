<?php

namespace Leantime\Domain\Timesheets\Controllers\Concerns;

trait ValidatesTimesheetValues
{
    /**
     * Determine if required fields for a timesheet entry are missing or invalid.
     *
     * @param array<string, mixed> $values
     *
     * @return string|null Returns error code when validation fails, null otherwise
     */
    protected function determineTimesheetFieldError(array $values): ?string
    {
        if (empty($values['ticket']) || empty($values['project'])) {
            return 'NO_TICKET';
        }

        if (empty($values['kind'])) {
            return 'NO_KIND';
        }

        if (empty($values['date'])) {
            return 'NO_DATE';
        }

        if (! isset($values['hours']) || $values['hours'] === '' || (is_numeric($values['hours']) && (float) $values['hours'] <= 0)) {
            return 'NO_HOURS';
        }

        return null;
    }
}


