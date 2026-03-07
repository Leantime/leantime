<?php

/**
 * Repository
 */

namespace Leantime\Domain\Lbmcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Lbmcanvas extends Canvas
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
    protected string $icon = 'apartment';

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
     */
    protected array $canvasTypes = [
        'lbm_customers' => ['icon' => 'group',      'color' => '#ccffcc', 'title' => 'box.lbm.customers'],
        'lbm_offerings' => ['icon' => 'barcode',    'color' => '#ffcccc', 'title' => 'box.lbm.offerings'],
        'lbm_capabilities' => ['icon' => 'design_services',  'color' => '#ccecff', 'title' => 'box.lbm.capabilities'],
        'lbm_financials' => ['icon' => 'payments', 'color' => '#ffffaa', 'title' => 'box.lbm.financials'],
    ];

    /**
     * relatesLabels - Relates to label
     *
     * @acces public
     */
    protected array $relatesLabels = [];
}
