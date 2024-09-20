<?php

/**
 * Repository.
 */

namespace Leantime\Domain\Leancanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Leancanvas extends Canvas
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'lean';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-flask';

    /***
     * disclaimer - Disclaimer
     *
     * @access protected
     * @var    string Disclaimer (including href)
     */
    protected string $disclaimer = 'text.lean.disclaimer';

    /**
     * canvasTypes - Must be extended.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $canvasTypes = [
        'problem'          => ['icon' => 'fa-lock', 'title' => 'box.lean.problem'],
        'alternatives'     => ['icon' => 'fa-arrow-down-up-across-line', 'title' => 'box.lean.alternatives'],
        'solution'         => ['icon' => 'fa-key', 'title' => 'box.lean.solution'],
        'keymetrics'       => ['icon' => 'fa-chart-column', 'title' => 'box.lean.keymetrics'],
        'uniquevalue'      => ['icon' => 'fa-gift', 'title' => 'box.lean.uniquevalue'],
        'highlevelconcept' => ['icon' => 'fa-wand-magic-sparkles', 'title' => 'box.lean.highlevelconcept'],
        'unfairadvantage'  => ['icon' => 'fa-person-running', 'title' => 'box.lean.unfairadvantage'],
        'channels'         => ['icon' => 'fa-truck', 'title' => 'box.lean.channels'],
        'customersegment'  => ['icon' => 'fa-user', 'title' => 'box.lean.customersegment'],
        'earlyadopters'    => ['icon' => 'fa-chart-pie', 'title' => 'box.lean.earlyadopters'],
        'cost'             => ['icon' => 'fa-file-invoice-dollar', 'title' => 'box.lean.cost'],
        'revenue'          => ['icon' => 'fa-sack-dollar', 'title' => 'box.lean.revenue'],
    ];

    /**
     * relatesLabels - Relates to label.
     *
     * @acces public
     *
     * @var array
     */
    protected array $relatesLabels = [];
}
