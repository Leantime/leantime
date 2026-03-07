<?php

/**
 * Repository
 */

namespace Leantime\Domain\Obmcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Obmcanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'obm';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'select_all';

    /***
     * disclaimer - Disclaimer
     *
     * @access protected
     * @var    string Disclaimer (including href)
     */
    protected string $disclaimer = 'text.obm.disclaimer';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'obm_kp' => ['icon' => 'ring_volume',                'title' => 'box.obm.kp'],
        'obm_kr' => ['icon' => 'handyman',              'title' => 'box.obm.kr'],
        'obm_ka' => ['icon' => 'engineering',      'title' => 'box.obm.ka'],
        'obm_vp' => ['icon' => 'redeem',                'title' => 'box.obm.vp'],
        'obm_ch' => ['icon' => 'local_shipping',               'title' => 'box.obm.ch'],
        'obm_cr' => ['icon' => 'favorite',               'title' => 'box.obm.cr'],
        'obm_cs' => ['icon' => 'person',              'title' => 'box.obm.cs'],
        'obm_fc' => ['icon' => 'receipt_long', 'title' => 'box.obm.fc'],
        'obm_fr' => ['icon' => 'point_of_sale',       'title' => 'box.obm.fr'],
    ];

    /**
     * relatesLabels - Relates to label
     *
     * @acces public
     */
    protected array $relatesLabels = [];
}
