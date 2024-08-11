<?php

/**
 * Controller / Delete Canvas
 */


namespace Leantime\Domain\Goalcanvas\Controllers {

    // use AWS\CRT\HTTP\Response;
    use Symfony\Component\HttpFoundation\Response;


    /**
     *
     */
    class DelCanvas extends \Leantime\Domain\Canvas\Controllers\DelCanvas
    {
        protected const CANVAS_NAME = 'goal';

        public function get($params):Response
        {
            $id = filter_var($params['id'] ?? '', FILTER_SANITIZE_NUMBER_INT);
            $this->tpl->assign('id', $id);

            return $this->tpl->displayPartial('goalcanvas.delCanvas');

        }
    }
}
