<?php

/**
 * Repository.
 */

namespace Leantime\Domain\Obmcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Obmcanvas extends Canvas
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'obm';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-object-group';

    /***
     * disclaimer - Disclaimer
     *
     * @access protected
     * @var    string Disclaimer (including href)
     */
    protected string $disclaimer = 'text.obm.disclaimer';

    /**
     * canvasTypes - Must be extended.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $canvasTypes = [
        'obm_kp' => ['icon' => 'fa-ring',                'title' => 'box.obm.kp'],
        'obm_kr' => ['icon' => 'fa-hammer',              'title' => 'box.obm.kr'],
        'obm_ka' => ['icon' => 'fa-person-digging',      'title' => 'box.obm.ka'],
        'obm_vp' => ['icon' => 'fa-gift',                'title' => 'box.obm.vp'],
        'obm_ch' => ['icon' => 'fa-truck',               'title' => 'box.obm.ch'],
        'obm_cr' => ['icon' => 'fa-heart',               'title' => 'box.obm.cr'],
        'obm_cs' => ['icon' => 'fa-person',              'title' => 'box.obm.cs'],
        'obm_fc' => ['icon' => 'fa-file-invoice-dollar', 'title' => 'box.obm.fc'],
        'obm_fr' => ['icon' => 'fa-cash-register',       'title' => 'box.obm.fr'],
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
