<?php

/**
 * Strategy Brief - Repository.
 */

namespace Leantime\Domain\Sbcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Sbcanvas extends Canvas
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'sb';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-briefcase';

    /**
     * canvasTypes - Must be extended.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $canvasTypes = [
        'sb_industry'    => ['icon' => 'fa-industry',        'title' => 'box.sb.industry'],
        'sb_description' => ['icon' => 'fa-file-lines',      'title' => 'box.sb.description'],
        'sb_st_design'   => ['icon' => 'fa-user-tie',    'title' => 'box.sb.st_design'],
        'sb_st_decision' => ['icon' => 'fa-sitemap',         'title' => 'box.sb.st_decision'],
        'sb_st_experts'  => ['icon' => 'fa-chalkboard-user', 'title' => 'box.sb.st_experts'],
        'sb_st_support'  => ['icon' => 'fa-person-circle-question',  'title' => 'box.sb.st_support'],
        'sb_budget'      => ['icon' => 'fa-money-bills',     'title' => 'box.sb.budget'],
        'sb_time'        => ['icon' => 'fa-business-time',   'title' => 'box.sb.time'],
        'sb_culture'     => ['icon' => 'fa-masks-theater',   'title' => 'box.sb.culture'],
        'sb_change'      => ['icon' => 'fa-book-skull',      'title' => 'box.sb.change'],
        'sb_principles'  => ['icon' => 'fa-ruler-combined',  'title' => 'box.sb.principles'],
    ];

    /**
     * statusLabels - Status labels<i class="fa-solid "></i>.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $statusLabels = [
        'status_pending'  => ['icon' => 'fa-person-circle-question', 'color' => 'blue',   'title' => 'status.pending',  'dropdown' => 'info',    'active' => true],
        'status_accepted' => ['icon' => 'fa-person-circle-check',    'color' => 'green',  'title' => 'status.accepted', 'dropdown' => 'success', 'active' => true],
        'status_rejected' => ['icon' => 'fa-person-circle-xmark',    'color' => 'red',    'title' => 'status.rejected', 'dropdown' => 'danger',  'active' => true],
    ];

    /**
     * relatesLabels - Relates to label.
     *
     * @acces public
     *
     * @var array
     */
    protected array $relatesLabels = [];

    /**
     * dataLabels - Data labels.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.description', 'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',        'field' => 'data',        'active' => false],
        3 => ['title' => 'label.assumptions', 'field' => 'assumptions', 'active' => false],
    ];
}
