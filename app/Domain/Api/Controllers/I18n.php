<?php

namespace Leantime\Domain\Api\Controllers;

use Leantime\Core\Controller;

/**
 *
 */

/**
 *
 */
class I18n extends Controller
{
    /**
     * Attach the language file to javascript
     *
     * @param $params Parameters or body of the request.
     * @throws \Exception
     * @access public
     */
    public function get($params)
    {
        header('Content-Type: application/javascript');

        $decodedString = json_encode($this->language->readIni());

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
