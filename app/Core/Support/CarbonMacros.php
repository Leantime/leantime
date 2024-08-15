<?php

namespace Leantime\Core\Support;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;

/**
 * Class CarbonMacros
 *
 * This class provides macros for formatting date and time using the Carbon library.
 * Class is being loaded via mixins and then available to the Carbon object
 *
 * @property string $userTimezone The user's timezone
 * @property string $userLanguage The user's language
 * @property string $userDateFormat The user's date format
 * @property string $userTimeFormat The user's time format
 * @property string $dbTimezone The database timezone
 * @property string $dbFormat The database format
 *
 * @method static CarbonImmutable this()
 */
class CarbonMacros
{
    /**
     * Constructor method for creating a new instance of the class.
     *
     * @param string $userTimezone   The user's preferred timezone.
     * @param string $userLanguage   The user's preferred language.
     * @param string $userDateFormat The user's preferred date format.
     * @param string $userTimeFormat The user's preferred time format.
     * @param string $dbFormat       The format to be used for database storage.
     * @param string $dbTimezone     The timezone to be used for database storage.
     *
     * @return void
     */
    public function __construct(
        public string $userTimezone = "",
        public string $userLanguage = "",
        public string $userDateFormat = "",
        public string $userTimeFormat = "",
        public string $dbFormat = "Y-m-d H:i:s",
        public string $dbTimezone = "UTC"
    ) {
    }

    /**
     * Formats the current date for the user based on the user's timezone,
     * language, and date format.
     *
     * @return \Closure Returns a closure that accepts no arguments and returns
     *         the formatted date as per the user's settings.
     */
    public function formatDateForUser(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin): String {
            return self::this()
                ->locale($mixin->userLanguage)
                ->setTimezone($mixin->userTimezone)
                ->translatedFormat($mixin->userDateFormat);
        };
    }

    /**
     * Formats the current time for the user based on the user's timezone,
     * language, and time format.
     *
     * @return \Closure Returns a closure that accepts no arguments and returns
     *         the formatted time as per the user's settings.
     */
    public function formatTimeForUser(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin): String {
            return self::this()
                ->setTimezone($mixin->userTimezone)
                ->locale($mixin->userLanguage)
                ->translatedFormat($mixin->userTimeFormat);
        };
    }

    /**
     * Formats the current time for the user based on the user's timezone,
     * language, and time format.
     *
     * @return \Closure Returns a closure that accepts no arguments and returns
     *         the formatted time as per the user's settings.
     */
    public function format24HTimeForUser(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin): String {
            return self::this()
                ->setTimezone($mixin->userTimezone)
                ->locale($mixin->userLanguage)
                ->translatedFormat("H:i");
        };
    }

    /**
     * Formats the current date and time for storing in the database based on
     * the database timezone, user language, and database format.
     *
     * @return \Closure Returns a closure that accepts no arguments and returns
     *         the formatted date and time as per the database settings.
     */
    public function formatDateTimeForDb(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin): String {
            return self::this()
                ->setTimezone($mixin->dbTimezone)
                ->locale($mixin->userLanguage)
                ->format($mixin->dbFormat);
        };
    }

    /**
     * Sets the current timezone and locale to the user's timezone and language.
     *
     * @return \Closure Returns a closure that accepts no arguments and sets the
     *         timezone and locale to the user's settings.
     */
    public function setToUserTimezone(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin): CarbonInterface {
            return self::this()
                ->setTimezone($mixin->userTimezone)
                ->locale($mixin->userLanguage);
        };
    }

    /**
     * Sets the timezone of the current datetime object to the database timezone
     * and sets the locale to the user's language.
     *
     * @return \Closure Returns a closure that accepts no arguments and returns the
     *         current datetime object with the timezone and locale set.
     */
    public function setToDbTimezone(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin): CarbonInterface {
            return self::this()
                ->setTimezone($mixin->dbTimezone)
                ->locale($mixin->userLanguage);
        };
    }
}
