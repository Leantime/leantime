<?php

namespace Leantime\Domain\Timesheets\Services;

use Leantime\Domain\Timesheets\Services\Timesheets as TimesheetService;
use Leantime\Core\UI\Template;


class ShowMyMonthlyTimesheetService
{
    private TimesheetService $timesheetService;
    private Template $tpl;

    public function __construct(TimesheetService $timesheetService, Template $tpl)
    {
        $this->timesheetService = $timesheetService;
        $this->tpl = $tpl;
    }

    public function saveTimeSheet(array $postData): void
    {
        foreach ($postData as $key => $dateEntry) {

            $tempData = explode('|', $key);

            if (count($tempData) === 4) {
                $ticketId = $tempData[0];
                $kind = $tempData[1];
                $date = $tempData[2];
                $timestamp = $tempData[3];
                $hours = $dateEntry;

                if ($ticketId === 'new' || $ticketId === 0) {
                    $ticketId = (int) $postData['ticketId'];
                    $kind = $postData['kindId'];

                    if ($ticketId == 0 && $hours > 0) {
                        $this->tpl->setNotification('Task ID is required for new entries', 'error', 'save_timesheet');

                        return;
                    }
                }

                $parsedHours = $hours;
                if (!empty($hours) && !is_numeric($hours)) {
                    try {
                        $parser = app(\Leantime\Domain\Timesheets\Services\TimeParser::class);
                        $parsedHours = $parser->parseTimeToDecimal($hours);

                        if ($parsedHours > 24 * 365) {
                            throw new \InvalidArgumentException('Time value is unreasonably large. Please enter a valid amount of time.');
                        }
                    } catch (\InvalidArgumentException $e) {
                        $this->tpl->setNotification($e->getMessage(), 'error', 'time_parse_error');

                        continue;
                    }
                }

                $values = [
                    'userId' => session('userdata.id'),
                    'ticket' => $ticketId,
                    'date' => $date,
                    'timestamp' => $timestamp,
                    'hours' => $parsedHours,
                    'kind' => $kind,
                ];

                if ($timestamp !== 'false' && $timestamp != false) {
                    try {
                        $this->timesheetService->upsertTime($ticketId, $values);
                        $this->tpl->setNotification('Timesheet saved successfully', 'success', 'save_timesheet');
                    } catch (\Exception $e) {
                        $this->tpl->setNotification('Error logging time: ' . $e->getMessage(), 'error', 'save_timesheet');
                        report($e);
                        continue;
                    }
                }
            }
        }
    }
}
