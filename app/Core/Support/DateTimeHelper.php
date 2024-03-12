<?php

namespace Leantime\Core\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidDateException;
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
     * Constructs a new instance of the class.
     *
     * @param DateTimeInterface|null|string $time Optional. The datetime object, ISO format string, or null. Defaults to null.
     * @param DateTimeZone|null|string $tz Optional. The timezone object, timezone identifier, or null. Defaults to null.
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

        if(!$this->isValidDateString($userDate)){
            throw new InvalidDateException("The string is not a valid date time string to parse as user datetime string", $userDate);
        }


        if ($userTime == "start") {
            $this->datetime = CarbonImmutable::createFromFormat("!" . $this->userDateFormat, trim($userDate), $this->userTimezone)
                ->startOfDay();
        } elseif ($userTime == "end") {
            $this->datetime = CarbonImmutable::createFromFormat("!" . $this->userDateFormat, trim($userDate), $this->userTimezone)

                ->endOfDay();
        } elseif ($userTime == "") {
            $this->datetime = CarbonImmutable::createFromFormat("!" . $this->userDateFormat, trim($userDate), $this->userTimezone);
        } else {
            $this->datetime = CarbonImmutable::createFromFormat("!" . $this->userDateFormat . " " . $this->userTimeFormat, trim($userDate . " " . $userTime), $this->userTimezone);
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

        if(!$this->isValidDateString($dbDate)){
            throw new InvalidDateException("The string is not a valid date time string to parse as Database string", $dbDate);
        }

        $this->datetime = CarbonImmutable::createFromFormat($this->dbFormat, $dbDate, $this->dbTimezone)->locale($this->userLanguage);

        return $this->datetime;
    }

    public function parseUser24hTime(string $local24Time) {

        $this->datetime = CarbonImmutable::createFromFormat("!H:m" . trim($local24Time), $this->userTimezone);

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


    public function setCarbonDate(CarbonImmutable|Carbon $date)
    {
        return $this->datetime = CarbonImmutable::create($date)->locale($this->userLanguage);
    }


    /**
     * isValidDateString - checks if a given string is a valid date and time string
     *
     * @access private
     * @param string $dateTimeString The date and time string to be validated
     * @return bool Returns true if the string is a valid date and time string, false otherwise
     */
    public function isValidDateString(?string $dateTimeString): bool
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
