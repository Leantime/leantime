<?php

/**
 * Controller / Delete Canvas Item
 */

namespace Leantime\Domain\Goalcanvas\Controllers {
    use Symfony\Component\HttpFoundation\Response;

    /**
     *
     */
    class DelCanvasItem extends \Leantime\Domain\Canvas\Controllers\DelCanvasItem
    {
        protected const CANVAS_NAME = 'goal';
        public function get($params):Response
        {
            $id = filter_var($params['id'] ?? '', FILTER_SANITIZE_NUMBER_INT);
            $this->tpl->assign('id', $id);

            return $this->tpl->displayPartial('goalcanvas.delCanvasItem');

        }

    }

}
