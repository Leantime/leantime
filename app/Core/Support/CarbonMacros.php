<?php

namespace Leantime\Core\Support;

use Carbon\Carbon;
use Carbon\CarbonImmutable;
use Leantime\Core\ApiRequest;
use Leantime\Core\Environment;
use Leantime\Core\Language;

class CarbonMacros
{
    public string $userTimezone;

    public string $userLanguage;

    public string $userDateFormat;

    public string $userTimeFormat;

    public string $dbTimezone;

    public string $dbFormat;


    public function __construct(
        $userTimezone,
        $userLanguage,
        $userDateFormat,
        $userTimeFormat,
        $dbFormat,
        $dbTimezone
    ) {

        $this->dbFormat = $dbFormat;
        $this->dbTimezone = $dbTimezone;
        $this->userTimezone = $userTimezone;
        $this->userLanguage = $userLanguage;
        $this->userDateFormat = $userDateFormat;
        $this->userTimeFormat = $userTimeFormat;
    }


    public function formatDateForUser(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin) {
            return self::this()
                ->setTimezone($mixin->userTimezone)
                ->locale($mixin->userLanguage)
                ->format($mixin->userDateFormat);
        };
    }

    public  function formatTimeForUser(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin) {
            return self::this()
                ->setTimezone($mixin->userTimezone)
                ->locale($mixin->userLanguage)
                ->format($mixin->userTimeFormat);
        };
    }

    public function formatDateTimeForDb(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin) {
            return self::this()
                ->setTimezone($mixin->dbTimezone)
                ->locale($mixin->userLanguage)
                ->format($mixin->dbFormat);
        };
    }

    public function setToUserTimezone(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin) {
            return self::this()->setTimezone($mixin->userTimezone)->locale($mixin->userLanguage);
        };
    }

    public function setToDbTimezone(): \Closure
    {
        $mixin = $this;
        return function () use ($mixin) {
            return self::this()->setTimezone($mixin->dbTimezone)->locale($mixin->userLanguage);
        };
    }
}
