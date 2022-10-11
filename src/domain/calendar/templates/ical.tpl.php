<?php
$eol = "\r\n";
echo "BEGIN:VCALENDAR".$eol;
echo "CALSCALE:GREGORIAN".$eol;
echo "METHOD:PUBLISH".$eol;
echo "PRODID:-//Leantime Cal//EN".$eol;
echo "VERSION:2.0".$eol;

if ($this->get('calendar')) {
    foreach ($this->get('calendar') as $calendar){

        if(isset($calendar['eventType']) && $calendar['eventType'] == 'calendar') {
            $url = BASE_URL."/calendar/editEvent/".$calendar['id']."";
        }else {
            $url = BASE_URL."/tickets/showTicket".$calendar['id']."?projectId=".$calendar['projectId']."";
        }

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

    }
}

echo "END:VCALENDAR".$eol;