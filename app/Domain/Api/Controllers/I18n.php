<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller;
use Symfony\Component\HttpFoundation\Response;

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
     * @todo refactor to remove user timezone and timeformat and move to user settings
     */
    public function get(array $params)
    {

        $languageIni = $this->language->readIni();

        $dateTimeIniSettings = [
            'language.dateformat',
            'language.timeformat',
        ];

        foreach ($dateTimeIniSettings as $index) {
            $languageIni[$index] = $this->language->__($index, true);
        }

        $decodedString = json_encode($languageIni);

        $result = $decodedString ? $decodedString : '{}';
        $response = new Response(
            <<<JS
            var leantime = leantime || {};
            var leantime = {
                i18n: {
                    dictionary: $result,
                    __: function(index){ return leantime.i18n.dictionary[index];  }
                }
            };
            JS,
            200
        );

        $response->headers->set('Content-Type', 'application/javascript');
        $response->headers->set("Pragma", 'public');
        $response->headers->set("Cache-Control", 'max-age=86400');

        return $response;
    }
}
