<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class I18n
 *
 * This class handles attaching the language file to JavaScript.
 */
class I18n extends Controller
{
    /**
     * Attach the language file to javascript
     *
     * @todo refactor to remove user timezone and timeformat and move to user settings
     *
     * @access public
     *
     * @param array $params or body of the request.
     *
     * @return Response
     *
     * @throws \Exception
     */
    public function get(array $params): Response
    {

        $languageIni = $this->language->readIni();

        $dateTimeIniSettings = [
            'language.dateformat',
            'language.timeformat',
        ];

        foreach ($dateTimeIniSettings as $index) {
            $languageIni[$index] = $this->language->__($index, true);
        }

        //Fullcalendar and other scripts can handle local to use the browser timezone
        $languageIni["usersettings.timezone"] = session("usersettings.timezone") ?? "local";

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

        //Disable cache for this file since datetime format settings is stored in here as well.
        //Need to find a better cache busting option for this.
        //$response->headers->set("Cache-Control", 'max-age=86400');

        return $response;
    }
}
