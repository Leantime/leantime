<?php

/**
 * Repository.
 */

namespace Leantime\Domain\Eacanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Eacanvas extends Canvas
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'ea';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-seedling';

    /**
     * canvasTypes - Must be extended.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $canvasTypes = [
        'ea_political'     => ['icon' => 'fa-landmark',       'title' => 'box.ea.political'],
        'ea_economic'      => ['icon' => 'fa-chart-line',     'title' => 'box.ea.economic'],
        'ea_societal'      => ['icon' => 'fa-people-arrows',  'title' => 'box.ea.societal'],
        'ea_technological' => ['icon' => 'fa-computer',       'title' => 'box.ea.technological'],
        'ea_legal'         => ['icon' => 'fa-scale-balanced', 'title' => 'box.ea.legal'],
        'ea_ecological'    => ['icon' => 'fa-cloud-sun',      'title' => 'box.ea.ecological'],
    ];

    /**
     * statusLabels - Status labels (may be extended).
     *
     * @acces protected
     *
     * @var array
     */
    protected array $statusLabels = [
        'status_observation' => ['icon' => 'fa-tower-observation', 'color' => 'blue',       'title' => 'status.ea.observation', 'dropdown' => 'info',    'active' => true],
        'status_threat'      => ['icon' => 'fa-cloud-bolt',        'color' => 'red',        'title' => 'status.ea.threat',      'dropdown' => 'danger', 'active' => true],
        'status_trend'       => ['icon' => 'fa-arrow-trend-up',    'color' => 'lightgreen', 'title' => 'status.ea.trend',      'dropdown' => 'success', 'active' => true],
    ];

    /**
     * relatesLabels - Relates to label (same structure as `statusLabels`).
     *
     * @acces public
     *
     * @var array
     */
    protected array $relatesLabels = [
        'relates_none'         => ['icon' => 'fa-border-none', 'color' => 'grey',   'title' => 'relates.none',         'dropdown' => 'default', 'active' => true],
        'relates_customers'    => ['icon' => 'fa-users',       'color' => 'green',  'title' => 'relates.customers',    'dropdown' => 'success', 'active' => true],
        'relates_offerings'    => ['icon' => 'fa-barcode',     'color' => 'red',    'title' => 'relates.offerings',    'dropdown' => 'danger',  'active' => true],
        'relates_markets'      => ['icon' => 'fa-shop',        'color' => 'brown',  'title' => 'relates.markets',      'dropdown' => 'default', 'active' => true],
        'relates_stakeholders' => ['icon' => 'fa-handshake',   'color' => 'orange', 'title' => 'relates.stakeholders', 'dropdown' => 'warning', 'active' => true],
    ];

    /**
     * dataLabels - Data labels (may be extended).
     *
     * @acces protected
     *
     * @var array
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.description', 'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',        'field' => 'data',        'active' => false],
        3 => ['title' => 'label.assumption',  'field' => 'assumptions', 'active' => false],
    ];
}
