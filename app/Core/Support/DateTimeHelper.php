<?php

namespace Leantime\Core\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Carbon\Exceptions\InvalidDateException;
use DateTime;
use DateTimeInterface;
use DateTimeZone;
use Illuminate\Contracts\Container\BindingResolutionException;
use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;

/**
 * Class DateTimeHelper
 *
 * A helper class for working with dates and times.
 * This class should NOT contain any formatting methods. Any datetime formatting should be included into the
 * CarbonMacros class
 *
 * @mixin CarbonMacros
 */
class DateTimeHelper extends CarbonImmutable
{
    private string $userTimezone;

    private string $userLanguage;

    private string $userDateFormat;

    private string $userTimeFormat;

    private readonly string $dbTimezone;

    private readonly string $dbFormat;

    private ?CarbonImmutable $datetime;

    /**
     * Constructs a new instance of the class.
     *
     * @param  DateTimeInterface|null|string  $time  Optional. The datetime object, ISO format string, or null.
     * @param  DateTimeZone|null|string  $tz  Optional. The timezone object, timezone identifier, or null.
     *
     * @throws BindingResolutionException
     */
    public function __construct($time = null, $tz = null)
    {
        parent::__construct($time, $tz);

        // Check if our custom macros are already registered
        if (! static::hasMacro('formatDateTimeForDb')) {
            static::mixin(new CarbonMacros(
                session('usersettings.timezone') ?? app()->make(Environment::class)->defaultTimezone,
                str_replace('-', '_', session('usersettings.language') ?? app()->make(Environment::class)->language),
                session('usersettings.date_format') ?? app()->make(Language::class)->__('language.dateformat'),
                session('usersettings.time_format') ?? app()->make(Language::class)->__('language.timeformat')
            ));
        }

        // Continue with regular initialization
        $language = app()->make(Language::class);
        $config = app()->make(Environment::class);

        // These are read only for a reason
        $this->dbFormat = 'Y-m-d H:i:s';
        $this->dbTimezone = 'UTC';

        // Session is set in middleware, unlikely to not be set but just in case set defaults.
        $this->userTimezone = session('usersettings.timezone') ?? $config->defaultTimezone;
        $this->userLanguage = str_replace('-', '_', (session('usersettings.language') ?? $config->language));

        $this->userDateFormat = session('usersettings.date_format') ?? $language->__('language.dateformat');
        $this->userTimeFormat = session('usersettings.time_format') ?? $language->__('language.timeformat');
    }

    /**
     * Parses a user input date and time and returns a CarbonImmutable object.
     *
     * @param  string  $userDate  The user input date in the format specified by $this->userDateFormat.
     * @param  ?string  $userTime  The user input time in the format specified by $this->userTimeFormat.
     *                             Defaults to an empty string. Can also be one of start|end to denote start or end time of
     *                             day
     * @return CarbonImmutable The parsed date and time in user timezone as a CarbonImmutable object.
     *
     * @throws InvalidDateException
     */
    public function parseUserDateTime(string $userDate, ?string $userTime = ''): CarbonImmutable
    {

        // Initialize result variable to null
        $this->datetime = null;

        // Validate input string
        if (! $this->isValidDateString($userDate)) {
            throw new InvalidDateException('The string is not a valid date time string to parse as user datetime string', $userDate);
        }

        // Define standard formats to try first
        $standardFormats = [
            DateTime::ATOM,
            DateTime::ISO8601,
            DateTime::W3C,
            "Y-m-d\TH:i:sP",     // ISO 8601 with timezone offset (e.g., 2025-04-16T00:00:00-04:00)
            "Y-m-d\TH:i:s\Z",    // ISO 8601 UTC/Zulu time (e.g., 2025-04-16T00:00:00Z)
            "Y-m-d\TH:i:s",      // ISO 8601 without timezone (e.g., 2025-04-16T00:00:00)
            "Y-m-d\TH:i:se",
            'Y-m-d',
        ];

        // Added in PHP 8.2
        if (defined('DateTime::ISO8601_EXPANDED')) {
            $standardFormats[] = DateTime::ISO8601_EXPANDED;
        }

        // Try standard formats first
        foreach ($standardFormats as $format) {
            try {

                if ($format === 'Y-m-d') {
                    $timezone = $this->userTimezone;
                    $this->datetime = CarbonImmutable::createFromFormat($format, $userDate, $this->userTimezone);
                } else {
                    $this->datetime = CarbonImmutable::createFromFormat($format, $userDate);
                }

                if ($this->datetime !== false && $this->datetime !== null) {
                    return $this->datetime;
                }
            } catch (\Exception $e) {
                // Continue to next format
            }
        }

        // If no standard format worked, handle user format cases
        $locale = substr($this->userLanguage, 0, 2);
        $trimmedDate = trim($userDate);

        if ($userTime === 'start') {
            $this->datetime = CarbonImmutable::createFromLocaleFormat('!'.$this->userDateFormat, $locale, $trimmedDate, $this->userTimezone)
                ->startOfDay();
        } elseif ($userTime === 'end') {
            $this->datetime = CarbonImmutable::createFromLocaleFormat('!'.$this->userDateFormat, $locale, $trimmedDate, $this->userTimezone)
                ->endOfDay();
        } elseif ($userTime === '' || $userTime === null) {
            $this->datetime = CarbonImmutable::createFromLocaleFormat('!'.$this->userDateFormat, $locale, $trimmedDate, $this->userTimezone);
        } else {
            $this->datetime = CarbonImmutable::createFromLocaleFormat('!'.$this->userDateFormat.' '.$this->userTimeFormat, $locale, trim($trimmedDate.' '.$userTime), $this->userTimezone);
        }

        return $this->datetime;
    }

    /**
     * Parses a database date string and returns a CarbonImmutable instance.
     *
     * @param  string  $dbDate  The date string in the database format to parse.
     * @return CarbonImmutable The parsed CarbonImmutable instance in db timezone (UTC)
     *
     * @throws InvalidDateException
     */
    public function parseDbDateTime(string $dbDate): CarbonImmutable
    {
        if (! $this->isValidDateString($dbDate)) {
            throw new InvalidDateException('The string is not a valid date time string to parse as Database string', $dbDate);
        }

        $this->datetime = CarbonImmutable::createFromFormat($this->dbFormat, $dbDate, $this->dbTimezone)->locale($this->userLanguage);

        return $this->datetime;
    }

    /**
     * Parses a user 24-hour time string and returns a CarbonImmutable instance.
     *
     * @param  string  $local24Time  The 24-hour time string to parse.
     * @return CarbonImmutable The parsed CarbonImmutable instance in the user's timezone
     */
    public function parseUser24hTime(string $local24Time): CarbonImmutable
    {
        $this->datetime = CarbonImmutable::createFromFormat('!H:i', $local24Time, $this->userTimezone);

        return $this->datetime;
    }

    /**
     * Returns the current date and time based on the user's timezone and language.
     *
     * @return CarbonImmutable The current date and time in the user's timezone and language.
     */
    public function userNow(): CarbonImmutable
    {
        return CarbonImmutable::now($this->userTimezone)->locale($this->userLanguage);
    }

    /**
     * Returns the current date and time in the database timezone as a CarbonImmutable instance.
     *
     * @return CarbonImmutable The current date and time in the database timezone (UTC) as a CarbonImmutable instance.
     */
    public function dbNow(): CarbonImmutable
    {
        return CarbonImmutable::now($this->dbTimezone)->locale($this->userLanguage);
    }

    /**
     * Sets the CarbonImmutable date for the current instance.
     *
     * @param  CarbonImmutable|Carbon  $date  The CarbonImmutable or Carbon instance to set the date.
     * @return string|CarbonImmutable|false
     */
    public function setCarbonDate(CarbonImmutable|Carbon $date): string|CarbonImmutable|bool
    {
        return $this->datetime = CarbonImmutable::create($date)->locale($this->userLanguage);
    }

    /**
     * isValidDateString - checks if a given string is a valid date and time string
     *
     * @param  ?string  $dateTimeString  The date and time string to be validated
     * @return bool Returns true if the string is a valid string that is worth sending to a parser, false otherwise
     */
    public function isValidDateString(?string $dateTimeString): bool
    {
        return empty($dateTimeString) === false
            && $dateTimeString !== '1969-12-31 00:00:00'
            && $dateTimeString !== '0000-00-00 00:00:00';
    }
}
