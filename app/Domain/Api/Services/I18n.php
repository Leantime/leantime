<?php

namespace Leantime\Domain\Api\Services;

use Leantime\Core\Language;

/**
 * Class I18n
 *
 * Assembles the i18n dictionary payload that is exposed to JavaScript.
 */
class I18n
{
    private Language $language;

    /**
     * @api
     */
    public function __construct(Language $language)
    {
        $this->language = $language;
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
            $languageIni[$index] = $this->language->__($index, true);
        }

        // Fullcalendar and other scripts can handle local to use the browser timezone
        $languageIni['usersettings.timezone'] = session('usersettings.timezone') ?? 'local';

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
