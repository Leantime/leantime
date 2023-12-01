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
     */
    public function get(array $params)
    {
        $decodedString = json_encode($this->language->readIni());

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

        return $response;
    }
}
