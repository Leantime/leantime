<?php

/**
 * Repository
 */

namespace leantime\domain\repositories {

    class retroscanvas extends \leantime\domain\repositories\canvas
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'retros';

        /***
         * icon - Icon associated with canvas (must be extended)
         *
         * @access public
         * @var    string Fontawesome icone
         */
        protected string $icon = 'fa-hand-spock';

        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         * @var   array
         */
        protected array $canvasTypes = [
            'well'       => [ 'icon' => 'fa-circle-check', 'title' => 'box.retros.continue' ],
            'notwell'    => [ 'icon' => 'fa-circle-xmark', 'title' => 'box.retros.stop_doing' ],
            'startdoing' => [ 'icon' => 'fa-circle-plus',  'title' => 'box.retros.start_doing' ]
        ];

        /**
         * dataLabels - Data labels (may be extended)
         *
         * @acces protected
         * @var   array
         */
        protected array $dataLabels = [
            1 => [ 'title' => 'label.description', 'field' => 'conclusion',  'active' => true ],
            2 => [ 'title' => 'label.data',        'field' => 'data',        'active' => false ],
            3 => [ 'title' => 'label.assumptions', 'field' => 'assumptions', 'active' => false ]
        ];

        /**
         * statusLabels - Status labels (may be extended)
         *
         * @acces protected
         * @var   array
         */
        protected array $statusLabels = [ ];


        protected array $relatesLabels = [];
    }
}
