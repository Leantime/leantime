<?php

/**
 * Repository
 */

namespace Leantime\Domain\Dbmcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Dbmcanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'dbm';

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
    protected string $disclaimer = 'text.dbm.disclaimer';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'dbm_cs' => ['icon' => 'fa-users',               'color' => '#ccffcc', 'title' => 'box.dbm.cs'],
        'dbm_cj' => ['icon' => 'fa-user-doctor',         'color' => '#ccffcc', 'title' => 'box.dbm.cj'],
        'dbm_cr' => ['icon' => 'fa-heart',               'color' => '#ccffcc', 'title' => 'box.dbm.cr'],
        'dbm_cd' => ['icon' => 'fa-truck',               'color' => '#ccffcc', 'title' => 'box.dbm.cd'],
        'dbm_ovp' => ['icon' => 'fa-money-bill-transfer', 'color' => '#ffcccc', 'title' => 'box.dbm.ovp'],
        'dbm_ops' => ['icon' => 'fa-barcode',             'color' => '#ffcccc', 'title' => 'box.dbm.ops'],
        'dbm_kad' => ['icon' => 'fa-chess',               'color' => '#ccecff', 'title' => 'box.dbm.kad'],
        'dbm_kac' => ['icon' => 'fa-hand-holding-dollar', 'color' => '#ccecff', 'title' => 'box.dbm.kac'],
        'dbm_kao' => ['icon' => 'fa-handshake',           'color' => '#ccecff', 'title' => 'box.dbm.kao'],
        'dbm_krp' => ['icon' => 'fa-apple-whole',         'color' => '#ccecff', 'title' => 'box.dbm.krp'],
        'dbm_krc' => ['icon' => 'fa-industry',            'color' => '#ccecff', 'title' => 'box.dbm.krc'],
        'dbm_krl' => ['icon' => 'fa-person-digging',      'color' => '#ccecff', 'title' => 'box.dbm.krl'],
        'dbm_krs' => ['icon' => 'fa-lightbulb',           'color' => '#ccecff', 'title' => 'box.dbm.krs'],
        'dbm_fr' => ['icon' => 'fa-sack-dollar',         'color' => '#ffffaa', 'title' => 'box.dbm.fr'],
        'dbm_fc' => ['icon' => 'fa-tags',                'color' => '#ffffaa', 'title' => 'box.dbm.fc'],
    ];

    /**
     * relatesLabels - Relates to label
     *
     * @acces public
     */
    protected array $relatesLabels = [];
}
