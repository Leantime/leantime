<?php

namespace Leantime\Core\Support;

use Leantime\Core\ApiRequest;
use Leantime\Core\Language;
use DateTime;
use DateTimeZone;

/**
 * Class DateTimeHelper
 *
 * A helper class for working with dates and times.
 */
class DateTimeHelper
{
    private Language $language;

    private ApiRequest $apiRequest;

    /**
     * Initializes a new instance of the class.
     *
     * @param Language   $language   The Language object to use for translations.
     * @param ApiRequest $apiRequest The ApiRequest object to use for accessing the API.
     */
    public function __construct(Language $language, ApiRequest $apiRequest)
    {
            $this->language = $language;
            $this->apiRequest = $apiRequest;
    }

    /**
     * Get the formatted date string from ISO format.
     *
     * @param string|null $date         The date string in ISO format or null.
     * @param string      $fromTimezone The timezone string from which to convert the date. Defaults to "UTC".
     * @return string The formatted date string or an empty string if the input is invalid.
     */
    public function getFormattedDateStringFromISO(?string $date, string $fromTimezone = "UTC"): string
    {

        //By default, string values in db are stored as UTC time.
        if (strlen($date) == 10) {
            $isoFromFormat = "!Y-m-d";
        } else {
            $isoFromFormat = "!Y-m-d H:i:s";
        }

        return $this->convertDateTime(
            dateTime: $date,
            fromFormat: $isoFromFormat,
            fromTz: $fromTimezone,
            toFormat:$this->language->__("language.dateformat"),
            toTz: $_SESSION['usersettings.timezone']
        );
    }

    /**
     * Get the formatted date string from ISO format.
     *
     * @param string|null $date         The date string in ISO format or null.
     * @param string      $fromTimezone The timezone string from which to convert the date. Defaults to "UTC".
     * @return string The formatted date string or an empty string if the input is invalid.
     */
    public function getFormattedUTCDateStringFromISO(?string $date, string $fromTimezone = "UTC"): string
    {

        //By default, string values in db are stored as UTC time.
        if (strlen($date) == 10) {
            $isoFromFormat = "!Y-m-d";
        } else {
            $isoFromFormat = "!Y-m-d H:i:s";
        }

        return $this->convertDateTime(
            dateTime: $date,
            fromFormat: $isoFromFormat,
            fromTz: $fromTimezone,
            toFormat:$this->language->__("language.dateformat"),
            toTz: "UTC"
        );
    }


    /**
     * Converts an ISO format date string to a formatted time string.
     *
     * @param ?string $date         The ISO format date string to convert.
     * @param string  $fromTimezone The timezone of the input date string. Defaults to "UTC".
     * @return string The formatted time string.
     */
    public function getFormattedTimeStringFromISO(?string $date, string $fromTimezone = "UTC"): string
    {

        if (strlen($date) == 10) {
            $isoFromFormat = "!Y-m-d";
        } else {
            $isoFromFormat = "!Y-m-d H:i:s";
        }

        return $this->convertDateTime(
            dateTime: $date,
            fromFormat: $isoFromFormat,
            fromTz: $fromTimezone,
            toFormat:$this->language->__("language.timeformat"),
            toTz: $_SESSION['usersettings.timezone']
        );
    }

    /**
     * Converts an ISO format date string to a 24-hour formatted time string.
     *
     * @param ?string $date         The ISO format date string to convert.
     * @param string  $fromTimezone The timezone of the input date string. Defaults to "UTC".
     * @return string The 24-hour formatted time string.
     */
    public function get24HourTimestringFromISO(?string $date, string $fromTimezone = "UTC"): string
    {

        return $this->convertDateTime(
            dateTime: $date,
            fromFormat: "!Y-m-d H:i:s",
            fromTz: $fromTimezone,
            toFormat: "H:i",
            toTz: $_SESSION['usersettings.timezone']
        );
    }

    /**
     * Converts a date string to an ISO format date string with a specific time of day.
     *
     * @param ?string $date        The date string to convert.
     * @param string  $timeOfDay   The time of day to include in the ISO format date string. Possible values are "b" for the beginning of the day (00:00:00), "m" for the middle of the day (
     *12:00:00), and "e" for the end of the day (23:59:00). Defaults to "b".
     * @param string  $fromTimzone The timezone of the input date string. Defaults to "user".
     * @return bool|string The ISO format date string with the specified time of day, or false if conversion fails.
     */
    public function getISODateString(?string $date, string $timeOfDay = "b", string $fromTimzone = "user"): bool|string
    {

        $time = "";
        switch ($timeOfDay) {
            case "b":
                $time = "00:00:00";
                break;
            case "m":
                $time = "12:00:00";
                break;
            case "e":
                $time = "23:59:00";
                break;
        }

        return $this->convertDateTime(
            dateTime: $date . " " . $time,
            fromFormat: $this->language->__("language.dateformat"). " H:i:s",
            fromTz: $fromTimzone,
            toFormat: "Y-m-d H:i:s",
            toTz: "UTC"
        );
    }

    /**
     * Converts a date and time string to an ISO format date-time string.
     *
     * @param ?string $date         The date string to convert. Can be null.
     * @param ?string $time         The time string to convert. Can be null.
     * @param string  $fromTimezone The timezone of the input date and time. Defaults to "user".
     * @return string The ISO format date-time string in the UTC timezone.
     */
    public function getISODateTimeString(?string $date, ?string $time, string $fromTimezone = "user"): string
    {

        return $this->convertDateTime(
            dateTime: $date . " " . $time,
            fromFormat: $this->language->__("language.dateformat"). " ".$this->language->__("language.timeformat") ,
            fromTz: $fromTimezone,
            toFormat: "Y-m-d H:i:s",
            toTz: "UTC"
        );
    }

    /**
     * Converts a date and time string to an ISO format date-time string with time being 24hours format.
     * This is the case when using time fields in html. Date always comes back as 24h
     *
     * @param ?string $date         The date string to convert. Can be null.
     * @param ?string $time         The time string to convert. Can be null.
     * @param string  $fromTimezone The timezone of the input date and time. Defaults to "user".
     * @return string The ISO format date-time string in the UTC timezone.
     */
    public function getISODateTimeStringFrom24h(?string $date, ?string $time, string $fromTimezone = "user"): string
    {

        return $this->convertDateTime(
            dateTime: $date . " " . $time,
            fromFormat: $this->language->__("language.dateformat"). " H:i",
            fromTz: $fromTimezone,
            toFormat: "Y-m-d H:i:s",
            toTz: "UTC"
        );
    }


    /**
     * Converts a date and time string to an ISO format date-time string with time being 24hours format.
     * This is the case when using time fields in html. Date always comes back as 24h
     *
     * @param ?string $date         The date string to convert. Can be null.
     * @param ?string $time         The time string to convert. Can be null.
     * @param string  $fromTimezone The timezone of the input date and time. Defaults to "user".
     * @return string The ISO format date-time string in the UTC timezone.
     */
    public function getLocalFormatFrom24hTime(?string $time, bool $ignoreTimezone = true): string
    {
        if ($ignoreTimezone) {
            return $this->convertDateTime(
                dateTime: $time,
                fromFormat: "H:i",
                fromTz: "UTC",
                toFormat: $this->language->__("language.timeformat"),
                toTz: "UTC"
            );
        }else{

            return $this->convertDateTime(
                dateTime: $time,
                fromFormat: "H:i",
                fromTz: "user",
                toFormat: $this->language->__("language.timeformat"),
                toTz: "UTC"
            );
        }
    }

    /**
     * Converts a date string to a timestamp.
     *
     * @param ?string $date    The date string to convert.
     * @param bool    $db2User Indicates whether the date string is in database format. Defaults to true.
     * @return bool|int The timestamp of the converted date string, or 0 if the conversion fails.
     */
    public function getTimestamp(?string $date, bool $db2User = true): bool|int
    {
        if ($this->isValidDateString($date)) {
            if ($db2User == true) {
                $fromTz = new \DateTimeZone("UTC");
                $timestamp = DateTime::createFromFormat("Y-m-d H:i:s", $date, $fromTz);
            } else {
                $fromTz = new \DateTimeZone($_SESSION['usersettings.timezone']);
                $timestamp = DateTime::createFromFormat($this->language->__("language.dateformat"), $date, $fromTz);
            }

            if (is_object($timestamp)) {
                if ($db2User == true) {
                    $toTz = new \DateTimeZone($_SESSION['usersettings.timezone'] ?? "UTC");
                } else {
                    $toTz = new \DateTimeZone("UTC");
                }

                $timestamp->setTimezone($toTz);

                return $timestamp->getTimestamp();
            }
        }

        return 0;
    }

    /**
     * Converts a date and time string from one format and timezone to another format and timezone.
     *
     * @param string $dateTime   The date and time string to convert.
     * @param string $fromFormat The format of the input date and time string.
     * @param string $fromTz     The timezone of the input date and time string.
     * @param string $toFormat   The format of the output date and time string.
     * @param string $toTz       The timezone to convert the date and time string to.
     * @return bool|string The converted date and time string, or false if conversion fails.
     */
    public function convertDateTime(
        string $dateTime,
        string $fromFormat,
        string $fromTz,
        string $toFormat,
        string $toTz
    ): bool|string {

        if ($fromTz == "user") {
            $fromTz = $_SESSION['usersettings.timezone'];
        }

        $dateTime = trim($dateTime);

        if ($this->isValidDateString($dateTime)) {
            $fromDateTimeZone = new DateTimeZone($fromTz);

            //If it is an API Request, don't do any formatting, return ATOM in target tz
            if ($this->apiRequest->isApiRequest()) {
                $utcDate = new DateTime($dateTime, $fromDateTimeZone);
                return is_object($utcDate) ? $utcDate->format(DateTime::ATOM) : false;
            }
            $timestamp = DateTime::createFromFormat($fromFormat, $dateTime, new DateTimeZone($toTz));

            if ($timestamp instanceof DateTime) {
                return $timestamp->format($toFormat);
            }
        }

        return false;
    }

    /**
     * extractTime - extracts the time from a given date and time string
     *
     * @access public
     * @param string|null $dateTime - The date and time string to extract the time from
     * @return string|bool - The extracted time in "H:m" format, or false if the input is not a valid date and time
     */
    public function extractTime(?string $dateTime): bool|string
    {
        if ($this->isValidDateString($dateTime)) {
            $timestamp = date_create_from_format("Y-m-d H:i:00", $dateTime);

            if (is_object($timestamp)) {
                return $timestamp->format("H:m");
            }
        }

        return false;
    }

    /**
     * isValidDateString - checks if a given string is a valid date and time string
     *
     * @access private
     * @param string $dateTimeString The date and time string to be validated
     * @return bool Returns true if the string is a valid date and time string, false otherwise
     */
    private function isValidDateString(?string $dateTimeString): bool
    {

        if (
            empty($dateTimeString) === false
            && $dateTimeString != "1969-12-31 00:00:00"
            && $dateTimeString != "0000-00-00 00:00:00"
        ) {
            return true;
        }

        return false;
    }
}
