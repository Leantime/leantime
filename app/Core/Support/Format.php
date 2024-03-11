<?php

namespace Leantime\Core\Support;

use Leantime\Core\Language;

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
     * @param string|int|float $value The value to be assigned. If empty, the constructor will return early.
     * @param null|string|int|float $value2 The second value to be assigned. It can be null. Used for certain cases as specified by $fromFormat.
     * @param FromFormat|null $fromFormat The format of the values. Can be one of the constants defined in the FromFormat class.
     * @return void
     */
    public function __construct(string|int|float $value, null|string|int|float $value2, ?FromFormat $fromFormat)
    {

        $this->dateTimeHelper = app()->make(DateTimeHelper::class);
        $this->language = app()->make(Language::class);

        if(empty($value)) {
            return;
        }

        switch($fromFormat){
            case FromFormat::DbDate:
                $this->value = $this->dateTimeHelper->parseDbDateTime($value);
                break;
            case FromFormat::UserDateTime:
                $this->value = $this->dateTimeHelper->parseUserDateTime($value, $value2);
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

    }

    /**
     * Returns the user formatted date string based on the 'value' property.
     *
     * @param string $emptyOutput The output to be returned when the 'value' property is empty. Defaults to an empty string.
     *
     * @return string The formatted date string or the $emptyOutput if the 'value' property is empty or the formatted date string is empty.
     */
    public function date(string $emptyOutput = "", ): string
    {

        if (empty($this->value)) {
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
        if ($this->value == null) {
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
        if ($this->value == null) {
            return "";
        }
        return $this->value->formatDateTimeForDb();
    }














 /* # # # # # # # # # # Refactor # # # # # */



    /**
     * Retrieves the 24-hour time string from the ISO formatted value property.
     *
     * @return string The 24-hour time string. If the value property is null, an empty string is returned.
     */
    public function time24(): string
    {
        if ($this->value == null) {
            return "";
        }
        return $this->dateTimeHelper->get24HourTimestringFromISO($this->value);
    }

    public function time24toLocalTime(bool $ignoreTimezone = false): string
    {
        if ($this->value == null) {
            return "";
        }

        return $this->dateTimeHelper->getLocalFormatFrom24hTime($this->value, $ignoreTimezone);

    }




    /**
     * Generates an ISO 8601 formatted date and time string from user defined date and 24h time.
     * Html time fields will always return 24h time
     *
     * @return string The ISO 8601 formatted date and time string. Returns an empty string if the value is null.
     */
    public function isoDateTimeFrom24h(): string
    {
        if ($this->value == null) {
            return "";
        }
        return $this->dateTimeHelper->getISODateTimeStringFrom24h($this->value, $this->value2);
    }



    /**
     * Generate unix timestamp from date.
     *
     * @return int|bool
     */
    public function timestamp(): int|bool
    {
        if ($this->value == null) {
            return "";
        }

        return $this->dateTimeHelper->parseDbDateTime($this->value)->getTimestamp();
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
            return $this->value . "%";
        }

        //Both values set. Return percent calculation
        $percent = ($this->value / $this->value2) * 100;
        return number_format($percent, 2) . "%";
    }

    public function decimal(): string
    {
        return number_format($this->value, 2);
    }
}
