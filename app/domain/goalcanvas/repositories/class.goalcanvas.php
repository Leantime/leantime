<?php

/**
 * Repository
 */

namespace leantime\domain\repositories {

    class goalcanvas extends \leantime\domain\repositories\canvas
    {
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'goal';

        /***
         * icon - Icon associated with canvas (must be extended)
         *
         * @access public
         * @var    string Fontawesome icone
         */
        protected string $icon = 'fa-bullseye';

        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         * @var   array
         */
        protected array $canvasTypes = [
            'goal' =>     [ 'icon' => 'fa-bullseye', 'title' => 'box.goal' ],
        ];

        /**
         * statusLabels - Status labels (may be extended)
         *
         * @acces protected
         * @var   array
         */
        protected array $statusLabels = [
            'status_ontrack' => [ 'icon' => 'fa-circle-check', 'color' => 'green',       'title' => 'status.goal.ontrack', 'dropdown' => 'success',    'active' => true],
            'status_atrisk' => [ 'icon' => 'fa-triangle-exclamation', 'color' => 'yellow',       'title' => 'status.goal.atrisk', 'dropdown' => 'warning',    'active' => true],
            'status_miss' => [ 'icon' => 'fa-circle-xmark', 'color' => 'red',       'title' => 'status.goal.miss', 'dropdown' => 'danger',    'active' => true],

        ];


        protected array $relatesLabels = [];


        /**
         * dataLabels - Data labels (may be extended)
         *
         * @acces protected
         * @var   array
         */
        protected array $dataLabels = [
                                        1 => [ 'title' => 'label.what_are_you_measuring', 'field' => 'assumptions',  'type' => 'string', 'active' => true ],
                                        2 => [ 'title' => 'label.current_value', 'field' => 'data', 'type' => 'int', 'active' => true ],
                                        3 => [ 'title' => 'label.goal_value', 'field' => 'conclusion', 'type' => 'int', 'active' => true ],

                                        ];
    }
}
