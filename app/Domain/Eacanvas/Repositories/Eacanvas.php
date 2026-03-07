<?php

/**
 * Repository
 */

namespace Leantime\Domain\Eacanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Eacanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'ea';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'spa';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'ea_political' => ['icon' => 'account_balance',       'title' => 'box.ea.political'],
        'ea_economic' => ['icon' => 'show_chart',     'title' => 'box.ea.economic'],
        'ea_societal' => ['icon' => 'connecting_airports',  'title' => 'box.ea.societal'],
        'ea_technological' => ['icon' => 'computer',       'title' => 'box.ea.technological'],
        'ea_legal' => ['icon' => 'balance', 'title' => 'box.ea.legal'],
        'ea_ecological' => ['icon' => 'partly_cloudy_day',      'title' => 'box.ea.ecological'],
    ];

    /**
     * statusLabels - Status labels (may be extended)
     *
     * @acces protected
     */
    protected array $statusLabels = [
        'status_observation' => ['icon' => 'cell_tower', 'color' => 'blue',       'title' => 'status.ea.observation', 'dropdown' => 'info',    'active' => true],
        'status_threat' => ['icon' => 'thunderstorm',        'color' => 'red',        'title' => 'status.ea.threat',      'dropdown' => 'danger', 'active' => true],
        'status_trend' => ['icon' => 'trending_up',    'color' => 'lightgreen', 'title' => 'status.ea.trend',      'dropdown' => 'success', 'active' => true],
    ];

    /**
     * relatesLabels - Relates to label (same structure as `statusLabels`)
     *
     * @acces public
     */
    protected array $relatesLabels = [
        'relates_none' => ['icon' => 'border_clear', 'color' => 'grey',   'title' => 'relates.none',         'dropdown' => 'default', 'active' => true],
        'relates_customers' => ['icon' => 'group',       'color' => 'green',  'title' => 'relates.customers',    'dropdown' => 'success', 'active' => true],
        'relates_offerings' => ['icon' => 'barcode',     'color' => 'red',    'title' => 'relates.offerings',    'dropdown' => 'danger',  'active' => true],
        'relates_markets' => ['icon' => 'storefront',        'color' => 'brown',  'title' => 'relates.markets',      'dropdown' => 'default', 'active' => true],
        'relates_stakeholders' => ['icon' => 'handshake',   'color' => 'orange', 'title' => 'relates.stakeholders', 'dropdown' => 'warning', 'active' => true],
    ];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.description', 'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',        'field' => 'data',        'active' => false],
        3 => ['title' => 'label.assumption',  'field' => 'assumptions', 'active' => false],
    ];
}
