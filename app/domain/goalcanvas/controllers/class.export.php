<?php

/**
 * XML export
 */

namespace leantime\domain\controllers {

    use leantime\domain\repositories;

    class export extends \leantime\domain\controllers\canvas\export
    {
        protected const CANVAS_NAME = 'goal';
    }
}
