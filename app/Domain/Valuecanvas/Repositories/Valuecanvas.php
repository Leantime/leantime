<?php

/**
 * Repository
 */

namespace Leantime\Domain\Valuecanvas\Repositories {

    use Leantime\Domain\Canvas\Repositories\Canvas;

    class Valuecanvas extends Canvas
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

        protected array $dataLabels = [
            1 => ['title' => 'label.valueCanvas.assumptions', 'field' => 'assumptions', 'active' => true],
            2 => ['title' => 'label.valueCanvas.data',        'field' => 'data',        'active' => true],
            3 => ['title' => 'label.valueCanvas.conclusion',  'field' => 'conclusion',  'active' => true],
        ];

        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         */
        protected array $canvasTypes = [
            'customersegment' => ['icon' => 'fa-user', 'title' => 'box.lean.customersegment'],
            'problem' => ['icon' => 'fa-lock', 'title' => 'box.lean.problem'],
            'solution' => ['icon' => 'fa-key', 'title' => 'box.lean.solution'],
            'uniquevalue' => ['icon' => 'fa-gift', 'title' => 'box.value.benefit'],

        ];

        /**
         * relatesLabels - Relates to label
         *
         * @acces public
         */
        protected array $relatesLabels = [];
    }
}
