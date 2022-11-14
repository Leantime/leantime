<?php
$calendars = $this->get('calendar');
$eol = "\r\n";

$this->dispatchTplEvent('beforeOutput', $calendars, ['eol' => $eol]);

echo "BEGIN:VCALENDAR".$eol;
echo "CALSCALE:GREGORIAN".$eol;
echo "METHOD:PUBLISH".$eol;
echo "PRODID:-//Leantime Cal//EN".$eol;
echo "VERSION:2.0".$eol;

if ($calendars) {
    foreach ($calendars as $calendar){

        if(isset($calendar['eventType']) && $calendar['eventType'] == 'calendar') {
            $url = BASE_URL."/calendar/editEvent/".$calendar['id']."";
        }else {
            $url = BASE_URL."/tickets/showTicket".$calendar['id']."?projectId=".$calendar['projectId']."";
        }

        $this->dispatchTplEvent('calendarOutputBeginning', $calendar, [
            'url' => $url,
            'eol' => $eol
        ]);

        if($calendar['dateFrom']['ical'] != '-00011130T000000Z' &&  $calendar['dateTo']['ical'] != '-00011130T000000Z') {
            echo "BEGIN:VEVENT" . $eol;
            echo "DTSTAMP:".date('Ymd\THis\Z')."". $eol;
            echo "UID:" . hash('sha1', $calendar['id']) . "@" . BASE_URL . "" . $eol;
            echo "DTSTART:" . $calendar['dateFrom']['ical'] . "" . $eol;
            echo "DTEND:" . $calendar['dateTo']['ical'] . "" . $eol;
            echo "SUMMARY:" . substr(htmlspecialchars($calendar['title']), 0, 74) . "" . $eol;
            echo "URL;VALUE=URI:" . $url . "" . $eol;
            echo "END:VEVENT" . $eol;
        }

        $this->dispatchTplEvent('calendarOutputEnd', $calendar, [
            'url' => $url,
            'eol' => $eol
        ]);

    }
}

echo "END:VCALENDAR".$eol;

$this->dispatchTplEvent('afterOutput', $calendars, ['eol' => $eol]);
