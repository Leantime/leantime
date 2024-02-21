<?php

use Leantime\Core\Environment;

foreach ($__data as $var => $val) {
    $$var = $val;
} // necessary for blade refactor
$calendars = $tpl->get('calendar');
$eol = "\r\n";
$timezone = $_SESSION['usersettings.timezone'] ?? "UTC";
$timezoneObject = new DateTimeZone($timezone);
$tpl->dispatchTplEvent('beforeOutput', $calendars, ['eol' => $eol]);
echo "BEGIN:VCALENDAR" . $eol;
echo "CALSCALE:GREGORIAN" . $eol;
echo "METHOD:PUBLISH" . $eol;
echo "PRODID:-//Leantime Cal//EN" . $eol;
echo "VERSION:2.0" . $eol;
if ($calendars) {
    $from = strtotime($calendars[0]['dateFrom']['ical']);
    $to = strtotime($calendars[(count($calendars) - 1)]['dateFrom']['ical']);
    echo "BEGIN:VTIMEZONE" . $eol;
    echo "TZID:" . $timezone . $eol;
    $year = 86400 * 360;
    $transitions = $timezoneObject->getTransitions($from - $year, $to + $year);
    $std = null;
    $dst = null;
    foreach ($transitions as $i => $trans) {
        $cmp = null;
        // skip the first entry...
        if ($i == 0) {
            // ... but remember the offset for the next TZOFFSETFROM value
            $tzfrom = $trans['offset'] / 3600;
            //Offsets need to be 4 characters long. Pad with 0
            if ($tzfrom < 0) {
                $tzfrom = floor($tzfrom) * -1;
                $tzfrom = "-" . str_pad($tzfrom, 2, "0", STR_PAD_LEFT);
            } else {
                $tzfrom = str_pad($tzfrom, 2, "0", STR_PAD_LEFT);
            }
            continue;
        }

        // daylight saving time definition
        if ($trans['isdst']) {
            $t_dst = $trans['ts'];
            echo "BEGIN:DAYLIGHT" . $eol;
        }
        // standard time definition
        else {
            $t_std = $trans['ts'];
            echo "BEGIN:STANDARD" . $eol;
        }

        $dt = new DateTime($trans['time']);
        $offset = $trans['offset'] / 3600;
        if ($offset < 0) {
            $offset = floor($offset) * -1;
            $offset = "-" . str_pad($offset, 2, "0", STR_PAD_LEFT);
        } else {
            $offset = str_pad($offset, 2, "0", STR_PAD_LEFT);
        }

        echo "DTSTART:" . $dt->format('Ymd\THis') . $eol;
        echo "TZOFFSETFROM:" . sprintf('%s%s%02d', $tzfrom >= 0 ? '+' : '', $tzfrom, ($tzfrom - $tzfrom) * 60) . $eol;
        echo "TZOFFSETTO:" . sprintf('%s%s%02d', $offset >= 0 ? '+' : '', $offset, ($offset - $offset) * 60) . $eol;
        // add abbreviated timezone name if available
        if (!empty($trans['abbr'])) {
            echo "TZNAME:" . $trans['abbr'] . $eol;
        }



        if ($trans['isdst']) {
            $t_dst = $trans['ts'];
            echo "END:DAYLIGHT" . $eol;
        }
        // standard time definition
        else {
            $t_std = $trans['ts'];
            echo "END:STANDARD" . $eol;
        }

        // we covered the entire date range
        if ($std && $dst && min($t_std, $t_dst) < $from && max($t_std, $t_dst) > $to) {
            break;
        }
    }
    echo "END:VTIMEZONE" . $eol;
    foreach ($calendars as $calendar) {
        if (isset($calendar['eventType']) && $calendar['eventType'] == 'calendar') {
            $url = BASE_URL . "/calendar/editEvent/" . $calendar['id'] . "";
        } else {
            $url = BASE_URL . "/dashboard/home#/tickets/showTicket" . $calendar['id'] . "?projectId=" . $calendar['projectId'] . "";
        }

        $tpl->dispatchTplEvent('calendarOutputBeginning', $calendar, [
        'url' => $url,
            'eol' => $eol,
        ]);
        if (str_contains($calendar['dateFrom']['ical'], '-00011130T000000') === false &&  str_contains($calendar['dateTo']['ical'], '-00011130T000000') === false) {
            echo "BEGIN:VEVENT" . $eol;
            echo "DTSTAMP;TZID=" . $timezone . ":" . date('Ymd\THis\Z') . "" . $eol;
            echo "UID:" . hash('sha1', $calendar['id'] . $calendar['dateContext']) . "@" . BASE_URL . "" . $eol;
            echo "DTSTART;TZID=" . $timezone . ":" . $calendar['dateFrom']['ical'] . "" . $eol;
            echo "DTEND;TZID=" . $timezone . ":" . $calendar['dateTo']['ical'] . "" . $eol;
            echo "SUMMARY:" . substr(($calendar['title']), 0, 74) . "" . $eol;
            echo "URL;VALUE=URI:" . $url . "" . $eol;
            echo "END:VEVENT" . $eol;
        }

        $tpl->dispatchTplEvent('calendarOutputEnd', $calendar, [
        'url' => $url,
            'eol' => $eol,
        ]);
    }
}

echo "END:VCALENDAR" . $eol;
$tpl->dispatchTplEvent('afterOutput', $calendars, ['eol' => $eol]);
