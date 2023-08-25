<?php

namespace leantime\domain\controllers;

use leantime\core;
use leantime\core\controller;
use leantime\domain\repositories;
use leantime\domain\services;
use leantime\domain\models;

class i18n extends controller
{
    /**
     * Attach the language file to javascript
     *
     * @param $params Parameters or body of the request.
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
