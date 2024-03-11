<?php

namespace Leantime\Core\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Leantime\Core\ApiRequest;
use Leantime\Core\Environment;
use Leantime\Core\Language;
use DateTime;
use DateTimeZone;

/**
 * Class DateTimeHelper
 *
 * A helper class for working with dates and times.
 */
class DateTimeHelper extends CarbonImmutable
{
    private Language $language;

    private ApiRequest $apiRequest;

    private string $userTimezone;

    private string $userLanguage;

    private string $userDateFormat;

    private string $userTimeFormat;

    private Environment $config;

    private readonly string $dbTimezone;

    private readonly string $dbFormat;

    private CarbonImmutable $datetime;
    /**
     * Initializes a new instance of the class.
     *
     */
    public function __construct($time = null, $tz = null)
    {

        parent::__construct($time, $tz);

        $this->language = app()->make(Language::class);
        $this->apiRequest = app()->make(ApiRequest::class);
        $this->config = app()->make(Environment::class);

        //These are read only for a reason
        $this->dbFormat = "Y-m-d H:i:s";
        $this->dbTimezone = "UTC";

        //Session is set in middleware, unlikely to not be set but just in case set defaults.
        $this->userTimezone = $_SESSION['usersettings.timezone'] ?? $this->config->defaultTimezone;
        $this->userLanguage = $_SESSION['usersettings.language'] ?? $this->config->language;
        $this->userDateFormat = $_SESSION['usersettings.language.date_format'] ?? $this->language->__("language.dateformat");
        $this->userTimeFormat = $_SESSION['usersettings.language.time_format'] ?? $this->language->__("language.timeformat");

        CarbonImmutable::mixin(new CarbonMacros(
            $this->userTimezone,
            $this->userLanguage,
            $this->userDateFormat,
            $this->userTimeFormat,
            $this->dbFormat,
            $this->dbTimezone
        ));
    }

    /**
     * Parses a user input date and time and returns a CarbonImmutable object.
     *
     * @param string $userDate The user input date in the format specified by $this->userDateFormat.
     * @param string $userTime The user input time in the format specified by $this->userTimeFormat.
     *                         Defaults to an empty string. Can also be one of start|end to denot start or end time of day
     * @return CarbonImmutable     The parsed date and time as a CarbonImmutable object.
     */
    public function parseUserDateTime(string $userDate, string $userTime = ""): CarbonImmutable
    {

        if ($userTime == "start") {
            $this->datetime = CarbonImmutable::createFromFormat("!" . $this->userDateFormat, trim($userDate), $this->userTimezone)

                ->startOfDay();
        } elseif ($userTime == "end") {
            $this->datetime = CarbonImmutable::createFromFormat("!" . $this->userDateFormat, trim($userDate), $this->userTimezone)

                ->endOfDay();
        } elseif ($userTime == "") {
            $this->datetime = CarbonImmutable::createFromFormat("!" . $this->userDateFormat, trim($userDate), $this->userTimezone
            );
        } else {
            $this->datetime = CarbonImmutable::createFromFormat("!" . $this->userDateFormat . " " . $this->userTimeFormat, trim($userDate . " " . $userTime), $this->userTimezone
            );
        }

        return $this->datetime;
    }

    /**
     * Parses a database date string and returns a CarbonImmutable instance.
     *
     * @param string $dbDate The date string in the database format to parse.
     * @return CarbonImmutable The parsed CarbonImmutable instance.
     */
    public function parseDbDateTime(string $dbDate): CarbonImmutable
    {

        $this->datetime = CarbonImmutable::createFromFormat($this->dbFormat, $dbDate, $this->dbTimezone)->locale($this->userLanguage);

        return $this->datetime;
    }

    public function userNow()
    {
        return CarbonImmutable::now($this->userTimezone)->locale($this->userLanguage);
    }

    public function dbNow()
    {
        return CarbonImmutable::now($this->dbTimezone)->locale($this->userLanguage);
    }


    /**
     * Loads the custom macros for Carbon library.
     *
     * The method defines custom macros for Carbon library that allow formatting dates and times
     * based on user preferences and for storage in the database.
     *
     * Macros:
     *  - formatDateForUser: Sets the timezone to local timezone and returns the date
     *    formatted according to the user's preferred date format.
     *  - formatTimeForUser: Sets the timezone to local timezone and returns the time
     *    formatted according to the user's preferred time format.
     *  - formatDateTimeForDb: Sets the timezone to UTC and returns the date and time
     *    formatted according to the specified format for storage in the database.
     *
     * @return void
     */
    public function loadCarbonMacros()
    {
    }


    public function setCarbonDate(CarbonImmutable|Carbon $date)
    {
        return $this->datetime = CarbonImmutable::create($date)->locale($this->userLanguage);
    }




/* OLD Stuff */





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
            fromFormat: $this->language->__("language.dateformat") . " H:i:s",
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
            fromFormat: $this->language->__("language.dateformat") . " " . $this->language->__("language.timeformat"),
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
            fromFormat: $this->language->__("language.dateformat") . " H:i",
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
        } else {
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
    public function getTimestampFromTimezone(?string $date, bool $db2User = true): bool|int
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
            $fromDateTimeZone = new \DateTimeZone($fromTz);

            //If it is an API Request, don't do any formatting, return ATOM in target tz
            if ($this->apiRequest->isApiRequest()) {
                $utcDate = new DateTime($dateTime, $fromDateTimeZone);
                return is_object($utcDate) ? $utcDate->format(DateTime::ATOM) : false;
            }

            $timestamp = DateTime::createFromFormat($fromFormat, $dateTime, $fromDateTimeZone);

            if (is_object($timestamp)) {
                $toTimezone = new \DateTimeZone($toTz);
                $timestamp->setTimezone($toTimezone);
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
