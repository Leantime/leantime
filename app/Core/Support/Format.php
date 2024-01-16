<?php

namespace Leantime\Core\Support;

use Leantime\Core\Language;

/**
 * Class Format
 *
 * This class provides various formatting methods for different types of data.
 */
class Format
{
    private mixed $value;
    private mixed $value2;
    private DateTimeHelper $dateTimeHelper;

    private Language $language;

    /**
     * Constructs a new instance of the class.
     *
     * @param mixed      $value  The value to be assigned to the 'value' property.
     * @param mixed|null $value2 The value to be assigned to the 'value2' property. Defaults to null.
     *
     * @return void
     */
    public function __construct(mixed $value, mixed $value2 = null)
    {
        $this->value = $value;
        $this->value2 = $value2;
        $this->language = app()->make(Language::class);
        $this->dateTimeHelper = app()->make(DateTimeHelper::class);
    }

    //date formatters

    /**
     * Returns the formatted date string based on the 'value' property.
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
        $formattedDate = $this->dateTimeHelper->getFormattedDateStringFromISO($this->value);

        return $formattedDate !== "" ? $formattedDate : $emptyOutput;
    }

    /**
     * Returns the formatted date string based on the 'value' property.
     *
     * @param string $emptyOutput The output to be returned when the 'value' property is empty. Defaults to an empty string.
     *
     * @return string The formatted date string or the $emptyOutput if the 'value' property is empty or the formatted date string is empty.
     */
    public function dateUtc(string $emptyOutput = "", ): string
    {

        if (empty($this->value)) {
            return $emptyOutput;
        }
        $formattedDate = $this->dateTimeHelper->getFormattedUTCDateStringFromISO($this->value);

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
        return $this->dateTimeHelper->getFormattedTimeStringFromISO($this->value);
    }

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
     * Generates an ISO 8601 formatted date and time string.
     *
     * @return string The ISO 8601 formatted date and time string. Returns an empty string if the value is null.
     */
    public function isoDateTime(): string
    {
        if ($this->value == null) {
            return "";
        }
        return $this->dateTimeHelper->getISODateTimeString($this->value, $this->value2);
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
     * Generates an ISO 8601 formatted date string.
     *
     * @return string The ISO 8601 formatted date string. Returns an empty string if the value is null.
     */
    public function isoDate(): string
    {
        if ($this->value == null) {
            return "";
        }
        return $this->dateTimeHelper->getISODateString($this->value);
    }

    /**
     * Generates an ISO 8601 formatted date string representing the start of the day.
     *
     * @return string The ISO 8601 formatted date string representing the start of the day. Returns an empty string if the value is null.
     */
    public function isoDateStart(): string
    {
        if ($this->value == null) {
            return "";
        }
        return $this->dateTimeHelper->getISODateString($this->value, "b");
    }

    /**
     * Generates an ISO 8601 formatted date string.
     *
     * @return string The ISO 8601 formatted date string. Returns an empty string if the value is null.
     */
    public function isoDateEnd(): string
    {
        if ($this->value == null) {
            return "";
        }
        return $this->dateTimeHelper->getISODateString($this->value, "e");
    }

    /**
     * Generates an ISO 8601 formatted date string with only the month and day.
     *
     * @return string The ISO 8601 formatted date string with only the month and day. Returns an empty string if the value is null.
     */
    public function isoDateMid(): string
    {
        if ($this->value == null) {
            return "";
        }
        return $this->dateTimeHelper->getISODateString($this->value, "m");
    }

    /**
     * Generates an ISO 8601 formatted time string.
     *
     * @return string The ISO 8601 formatted time string. Returns an empty string if the value is null.
     */
    public function isoTime(): string
    {
        if ($this->value == null) {
            return "";
        }
        return $this->dateTimeHelper->getISOTimeString($this->value);
    }

    public function timestamp(): string
    {
        if ($this->value == null) {
            return "";
        }

        return $this->dateTimeHelper->getTimestamp($this->value);
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

    public function decimal() {
        return number_format($this->value, 2);
    }
}
