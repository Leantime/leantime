<?php

/**
 * Repository.
 */

namespace Leantime\Domain\Cpcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Cpcanvas extends Canvas
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'cp';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-city';

    /***
     * disclaimer - Disclaimer
     *
     * @access protected
     * @var    string Disclaimer (including href)
     */
    protected string $disclaimer = 'text.cp.disclaimer';

    /**
     * canvasTypes - Must be extended.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $canvasTypes = [
        'cp_cj_rv' => ['icon' => 'fa-money-bills',         'title' => 'box.cp.cj_rv'],
        'cp_cj_rc' => ['icon' => 'fa-hand-holding-dollar', 'title' => 'box.cp.cj_rc'],
        'cp_cj_e'  => ['icon' => 'fa-thumbs-up',           'title' => 'box.cp.cj_e'],
        'cp_ou_rv' => ['icon' => 'fa-money-bills',         'title' => 'box.cp.ou_rv'],
        'cp_ou_rc' => ['icon' => 'fa-hand-holding-dollar', 'title' => 'box.cp.ou_rc'],
        'cp_ou_e'  => ['icon' => 'fa-thumbs-up',           'title' => 'box.cp.ou_e'],
        'cp_os_rv' => ['icon' => 'fa-money-bills',         'title' => 'box.cp.os_rv'],
        'cp_os_rc' => ['icon' => 'fa-hand-holding-dollar', 'title' => 'box.cp.os_rc'],
        'cp_os_e'  => ['icon' => 'fa-thumbs-up',           'title' => 'box.cp.os_e'],
        'cp_oi_rv' => ['icon' => 'fa-money-bills',         'title' => 'box.cp.oi_rv'],
        'cp_oi_rc' => ['icon' => 'fa-hand-holding-dollar', 'title' => 'box.cp.oi_rc'],
        'cp_oi_e'  => ['icon' => 'fa-thumbs-up',           'title' => 'box.cp.oi_e'],
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
