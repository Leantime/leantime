<?php

/**
 * Repository
 */

namespace Leantime\Domain\Leancanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Leancanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'lean';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'science';

    /***
     * disclaimer - Disclaimer
     *
     * @access protected
     * @var    string Disclaimer (including href)
     */
    protected string $disclaimer = 'text.lean.disclaimer';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'problem' => ['icon' => 'lock', 'title' => 'box.lean.problem'],
        'alternatives' => ['icon' => 'swap_vert', 'title' => 'box.lean.alternatives'],
        'solution' => ['icon' => 'key', 'title' => 'box.lean.solution'],
        'keymetrics' => ['icon' => 'bar_chart', 'title' => 'box.lean.keymetrics'],
        'uniquevalue' => ['icon' => 'redeem', 'title' => 'box.lean.uniquevalue'],
        'highlevelconcept' => ['icon' => 'auto_fix_high', 'title' => 'box.lean.highlevelconcept'],
        'unfairadvantage' => ['icon' => 'directions_run', 'title' => 'box.lean.unfairadvantage'],
        'channels' => ['icon' => 'local_shipping', 'title' => 'box.lean.channels'],
        'customersegment' => ['icon' => 'person', 'title' => 'box.lean.customersegment'],
        'earlyadopters' => ['icon' => 'pie_chart', 'title' => 'box.lean.earlyadopters'],
        'cost' => ['icon' => 'receipt_long', 'title' => 'box.lean.cost'],
        'revenue' => ['icon' => 'savings', 'title' => 'box.lean.revenue'],
    ];

    /**
     * relatesLabels - Relates to label
     *
     * @acces public
     */
    protected array $relatesLabels = [];
}
