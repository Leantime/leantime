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
    protected string $icon = 'apartment';

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
        'dbm_cs' => ['icon' => 'group',               'color' => '#ccffcc', 'title' => 'box.dbm.cs'],
        'dbm_cj' => ['icon' => 'medical_services',         'color' => '#ccffcc', 'title' => 'box.dbm.cj'],
        'dbm_cr' => ['icon' => 'favorite',               'color' => '#ccffcc', 'title' => 'box.dbm.cr'],
        'dbm_cd' => ['icon' => 'local_shipping',               'color' => '#ccffcc', 'title' => 'box.dbm.cd'],
        'dbm_ovp' => ['icon' => 'currency_exchange', 'color' => '#ffcccc', 'title' => 'box.dbm.ovp'],
        'dbm_ops' => ['icon' => 'barcode',             'color' => '#ffcccc', 'title' => 'box.dbm.ops'],
        'dbm_kad' => ['icon' => 'extension',               'color' => '#ccecff', 'title' => 'box.dbm.kad'],
        'dbm_kac' => ['icon' => 'savings', 'color' => '#ccecff', 'title' => 'box.dbm.kac'],
        'dbm_kao' => ['icon' => 'handshake',           'color' => '#ccecff', 'title' => 'box.dbm.kao'],
        'dbm_krp' => ['icon' => 'nutrition',         'color' => '#ccecff', 'title' => 'box.dbm.krp'],
        'dbm_krc' => ['icon' => 'factory',            'color' => '#ccecff', 'title' => 'box.dbm.krc'],
        'dbm_krl' => ['icon' => 'engineering',      'color' => '#ccecff', 'title' => 'box.dbm.krl'],
        'dbm_krs' => ['icon' => 'lightbulb',           'color' => '#ccecff', 'title' => 'box.dbm.krs'],
        'dbm_fr' => ['icon' => 'savings',         'color' => '#ffffaa', 'title' => 'box.dbm.fr'],
        'dbm_fc' => ['icon' => 'sell',                'color' => '#ffffaa', 'title' => 'box.dbm.fc'],
    ];

    /**
     * relatesLabels - Relates to label
     *
     * @acces public
     */
    protected array $relatesLabels = [];
}
