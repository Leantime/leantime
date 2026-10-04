<?php

namespace Leantime\Domain\Api\Services;

use Leantime\Core\Configuration\Environment;
use Leantime\Core\Language;

/**
 * Class I18n
 *
 * Assembles the i18n dictionary payload that is exposed to JavaScript.
 */
class I18n
{
    private Language $language;

    private Environment $config;

    /**
     * @api
     */
    public function __construct(Language $language, Environment $config)
    {
        $this->language = $language;
        $this->config = $config;
    }

    /**
     * Builds the JavaScript snippet that defines the global leantime.i18n object,
     * including the language dictionary, the resolved date/time format strings and
     * the user timezone.
     *
     * @return string The JavaScript payload
     *
     * @api
     */
    public function buildJsDictionary(): string
    {
        $languageIni = $this->language->ini_array;

        $dateTimeIniSettings = [
            'language.dateformat',
            'language.timeformat',
        ];

        foreach ($dateTimeIniSettings as $index) {
            $languageIni[$index] = $this->language->__($index);
        }

        // The timezone the server interprets the user's dates in. Calendars lay out their grid in this
        // zone, so it must match the server: never the browser's zone ("local"), or every time a user
        // picks is shifted by the difference between the two.
        $languageIni['usersettings.timezone'] = session('usersettings.timezone') ?: ($this->config->defaultTimezone ?: 'UTC');

        $decodedString = json_encode($languageIni);

        $result = $decodedString ? $decodedString : '{}';

        return <<<JS
        var leantime = leantime || {};
        var leantime = {
            i18n: {
                dictionary: $result,
                __: function(index){ return leantime.i18n.dictionary[index];  }
            }
        };
        JS;
    }
}
