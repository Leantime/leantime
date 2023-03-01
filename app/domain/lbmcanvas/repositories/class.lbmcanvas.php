<?php

/**
 * Repository
 */

namespace leantime\domain\repositories {

    class lbmcanvas extends \leantime\domain\repositories\canvas
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'lbm';

        /***
         * icon - Icon associated with canvas (must be extended)
         *
         * @access public
         * @var    string Fontawesome icone
         */
        protected string $icon = 'fa-building';

        /***
         * disclaimer - Disclaimer
         *
         * @access protected
         * @var    string Disclaimer (including href)
         */
        protected string $disclaimer = 'text.lbm.disclaimer';

        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         * @var   array
         */
        protected array $canvasTypes = [
            'lbm_customers'    => [ 'icon' => 'fa-users',      'color' => '#ccffcc', 'title' => 'box.lbm.customers' ],
            'lbm_offerings'    => [ 'icon' => 'fa-barcode',    'color' => '#ffcccc', 'title' => 'box.lbm.offerings' ],
            'lbm_capabilities' => [ 'icon' => 'fa-pen-ruler',  'color' => '#ccecff', 'title' => 'box.lbm.capabilities' ],
            'lbm_financials'   => [ 'icon' => 'fa-money-bill', 'color' => '#ffffaa', 'title' => 'box.lbm.financials' ]
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
