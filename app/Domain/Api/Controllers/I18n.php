<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller;

/**
 *
 */
class I18n extends Controller
{
    /**
     * Attach the language file to javascript
     *
     * @param array $params or body of the request.
     * @throws \Exception
     * @access public
     */
    public function get(array $params)
    {
        $languageIni = $this->language->readIni();

        $dateTimeIniSettings = [
            'language.jsdateformat',
            'language.jstimeformat',
            'language.momentJSDate',
        ];

        foreach ($dateTimeIniSettings as $index) {
            $languageIni[$index] = $this->language->__($index, true);
        }

        header('Content-Type: application/javascript');

        $decodedString = json_encode($languageIni);

        $result = $decodedString ? $decodedString : '{}';

        echo "var leantime = leantime || {};
            var leantime = {
                i18n: {
                    dictionary: " . $result . ",
                    __: function(index){ return leantime.i18n.dictionary[index];  }
                }
            };";
    }
}
