<?php

/**
 * Repository
 */

namespace leantime\domain\repositories {

    class valuecanvas extends \leantime\domain\repositories\canvas
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'value';

        /***
         * icon - Icon associated with canvas (must be extended)
         *
         * @access public
         * @var    string Fontawesome icone
         */
        protected string $icon = 'fa-ranking-star';

        /***
         * disclaimer - Disclaimer
         *
         * @access protected
         * @var    string Disclaimer (including href)
         */
        protected string $disclaimer = '';

        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         * @var   array
         */
        protected array $canvasTypes = [
            'customersegment'  => [ 'icon' => 'fa-user', 'title' => 'box.lean.customersegment' ],
            'problem'          => [ 'icon' => 'fa-lock', 'title' => 'box.lean.problem' ],
            'solution'         => [ 'icon' => 'fa-key', 'title' => 'box.lean.solution' ],
            'uniquevalue'      => [ 'icon' => 'fa-gift', 'title' => 'box.value.benefit' ],


        ];

        /**
         * relatesLabels - Relates to label
         *
         * @acces public
         * @var   array
         */
        protected array $relatesLabels = [ ];
    }
}
