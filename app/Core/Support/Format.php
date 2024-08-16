<?php

namespace Leantime\Core\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use Illuminate\Support\Facades\Date;
use Leantime\Core\Language;
use PHPUnit\Exception;

/**
 * Class Format
 *
 * This class provides various formatting methods for simple data types (strings, ints, floats)
 */
class Format
{
    private mixed $value = "";
    private mixed $value2 = "";
    private DateTimeHelper $dateTimeHelper;
    private Language $language;

    /**
     * Creates a new instance of the class.
     *
     * PSA: This class will NOT throw exceptions or error messages since it is a user facing string formatting class.
     * If you need to evaluate correct parsing of datetimes use the datetime helper and not this format class.
     *
     * @param string|int|float      $value      The value to be assigned. If empty, the constructor will return early.
     * @param null|string|int|float $value2     The second value to be assigned. It can be null. Used for certain cases
     *                                          as specified by $fromFormat.
     * @param FromFormat|null       $fromFormat The format of the values. Can be one of the constants defined in the
     *                                          FromFormat class.
     * @return void
     */
    public function __construct(
        string|int|float|null|\DateTimeInterface|CarbonInterface $value,
        string|int|float|null|\DateTimeInterface|CarbonInterface $value2,
        ?FromFormat $fromFormat = FromFormat::DbDate
    ) {

        $this->dateTimeHelper = app()->make(DateTimeHelper::class);
        $this->language = app()->make(Language::class);

        if (empty($value)) {
            return;
        }

        if($value instanceof \DateTime) {
            $value = CarbonImmutable::create($value);
            $this->value = CarbonImmutable::create($value);
        }

        if($value2 instanceof \DateTime) {
            $value2 = CarbonImmutable::create($value2);
            $this->value = CarbonImmutable::create($value2);
        }

        try {
            switch ($fromFormat) {
                case FromFormat::DbDate:
                    $this->value = $this->dateTimeHelper->parseDbDateTime($value);
                    break;
                case FromFormat::UserDateTime:
                    $this->value = $this->dateTimeHelper->parseUserDateTime($value, $value2);
                    break;
                case FromFormat::User24hTime:
                    $this->value = $this->dateTimeHelper->parseUser24hTime($value);
                    break;
                case FromFormat::Db24hTime:
                    $this->value = $this->dateTimeHelper->parseDb24hTime($value);
                    break;
                case FromFormat::UserDateStartOfDay:
                    $this->value = $this->dateTimeHelper->parseUserDateTime($value, "start");
                    break;
                case FromFormat::UserDateEndOfDay:
                    $this->value = $this->dateTimeHelper->parseUserDateTime($value, "end");
                    break;
                default:
                    $this->value = $value;
                    break;
            }
        } catch (\Throwable $e) {
            //Several things can throw exceptions in the date parsing scripts.
            //Most common is an invalid date format. This could also be an empty string or a 0000-00... date.
            //Since this format class is purely for user facing purposes we will not show an error message
            //but return an empty string.
            $this->value = $value;
            return;
        }
    }

    /**
     * Returns the user formatted date string based on the 'value' property.
     *
     * @param string $emptyOutput The output to be returned when the 'value' property is empty.
     *                            Defaults to an empty string.
     *
     * @return string             The formatted date string or the $emptyOutput if the 'value' property is empty or
     *                            the formatted date string is empty.
     */
    public function date(string $emptyOutput = ""): string
    {

        if (empty($this->value) || !$this->value instanceof CarbonImmutable) {
            return $emptyOutput;
        }

        $formattedDate = $this->value->formatDateForUser();


        return $formattedDate !== "" ? $formattedDate : $emptyOutput;
    }

    /**
     * Retrieve the formatted time string from the ISO value.
     * Returns an empty string if 'value' is null.
     *
     * @return string The formatted time string.
     */
    public function time(): string
    {
        if (empty($this->value) || !$this->value instanceof CarbonImmutable) {
            return "";
        }

        return $this->value->formatTimeForUser();
    }

    /**
     * Generates an ISO 8601 formatted date and time string.
     *
     * @return string The ISO 8601 formatted date and time string. Returns an empty string if the value is null.
     */
    public function isoDateTime(): string
    {
        if (empty($this->value) || !$this->value instanceof CarbonImmutable) {
            return "";
        }
        return $this->value->formatDateTimeForDb();
    }

    /**
     * @deprecated
     *
     * This method is deprecated and only included because of plugin backwards compatibility.
     * Once all plugins are updated this will be removed.
     *
     * @return string The ISO 8601 formatted date string. Returns an empty string if the value is null.
     */
    public function isoDate(): string
    {

        if (empty($this->value)) {
            return "";
        }

        //This method should not be used anymore however we have plugins that are still using it and they will not have
        //the new enum values. So they are still calling format($var)->isoDate() without a enum modifier that would
        //indicate that this is a user date (which it was historically).
        //So now we have to shuffle things around and since the format was probably not correct anyways, let's reparse

        if(!$this->value instanceof CarbonImmutable) {

            try {
                $this->value = $this->dateTimeHelper->parseUserDateTime($this->value, "start");
            }catch(Exception $e) {
                report($e);
                return "";
            }

        }else{

            //If for some reason Carbon was able to parse the date we'll need to make sure the timezone is set to the
            //users timezone.

            //Date was falsly parsed as UTC but is actually user date. Shift timezone.
            $userTimezone = session("usersettings.timezone");
            //Carbon shift timezone will change timezone without actually changing the numbers
            $this->value->shiftTimezone($userTimezone);
        }

        return $this->value->formatDateTimeForDb();
    }

    /**
     * Generate unix timestamp from date.
     *
     * @return int|bool
     */
    public function timestamp(): int|bool
    {
        if (empty($this->value) || !$this->value instanceof CarbonImmutable) {
            return "";
        }

        return $this->value->getTimestamp();
    }

    /**
     * Generate unix timestamp from date in miliseconds for javascript usage
     *
     * @return int|bool
     */
    public function jsTimestamp(): int|bool
    {
        if (empty($this->value) || !$this->value instanceof CarbonImmutable) {
            return "";
        }

        return $this->value->getTimestampMs();
    }

    /**
     * Retrieves the 24-hour time string from the ISO formatted value property.
     *
     * @return string The 24-hour time string. If the value property is null, an empty string is returned.
     */
    public function time24(): string
    {
        if (empty($this->value) || !$this->value instanceof CarbonImmutable) {
            return "";
        }

        return $this->value->format24HTimeForUser();
    }


    /**
     * Converts a 24-hour formatted time string to a user-friendly time string.
     *
     * @return string The user-friendly time string in the format "H:i A". Returns an empty string if the value is null.
     */
    public function userTime24toUserTime(): string
    {
        if (empty($this->value) || !$this->value instanceof CarbonImmutable) {
            return "";
        }

        return $this->value->formatTimeForUser();

    }


    /**
     * Generates a string representation of currency.
     *
     * @return string The string representation of currency.
     */
    public function currency(): string
    {
        if ($this->value == null) {
            return "";
        }

        return $this->language->__("language.currency") . "" . $this->value;
    }

    /**
     * Generates a string representation of a percentage.
     *
     * @return string The percentage string. Returns an empty string if the value is null.
     * If the second value is empty, the first value is returned with a "%" sign appended.
     * If both values are set, the percentage is calculated and formatted to two decimal places, followed by a "%" sign.
     */
    public function percent(): string
    {
        //First value empty, just return empty string
        if ($this->value == null) {
            return "";
        }

        //Second value empty return first value with % sign
        if (empty($this->value2)) {
            return number_format($this->value, 2) . "%";
        }

        //Both values set. Return percent calculation
        $percent = ($this->value / $this->value2) * 100;
        return number_format($percent, 2) . "%";
    }

    /**
     * Formats a decimal number with two decimal places.
     *
     * @return string The decimal number formatted with two decimal places.
     */
    public function decimal(): string
    {
        return number_format((float)$this->value, 2);
    }
}
