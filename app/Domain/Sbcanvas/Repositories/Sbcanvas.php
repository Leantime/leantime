<?php

/**
 * Strategy Brief - Repository
 */

namespace Leantime\Domain\Sbcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Sbcanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'sb';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'work';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'sb_industry' => ['icon' => 'factory',        'title' => 'box.sb.industry'],
        'sb_description' => ['icon' => 'description',      'title' => 'box.sb.description'],
        'sb_st_design' => ['icon' => 'badge',    'title' => 'box.sb.st_design'],
        'sb_st_decision' => ['icon' => 'account_tree',         'title' => 'box.sb.st_decision'],
        'sb_st_experts' => ['icon' => 'co_present', 'title' => 'box.sb.st_experts'],
        'sb_st_support' => ['icon' => 'help',  'title' => 'box.sb.st_support'],
        'sb_budget' => ['icon' => 'payments',     'title' => 'box.sb.budget'],
        'sb_time' => ['icon' => 'business_center',   'title' => 'box.sb.time'],
        'sb_culture' => ['icon' => 'theater_comedy',   'title' => 'box.sb.culture'],
        'sb_change' => ['icon' => 'menu_book',      'title' => 'box.sb.change'],
        'sb_principles' => ['icon' => 'straighten',  'title' => 'box.sb.principles'],
    ];

    /**
     * statusLabels - Status labels
     *
     * @acces protected
     */
    protected array $statusLabels = [
        'status_pending' => ['icon' => 'help', 'color' => 'blue',   'title' => 'status.pending',  'dropdown' => 'info',    'active' => true],
        'status_accepted' => ['icon' => 'how_to_reg',    'color' => 'green',  'title' => 'status.accepted', 'dropdown' => 'success', 'active' => true],
        'status_rejected' => ['icon' => 'person_off',    'color' => 'red',    'title' => 'status.rejected', 'dropdown' => 'danger',  'active' => true],
    ];

    /**
     * relatesLabels - Relates to label
     *
     * @acces public
     */
    protected array $relatesLabels = [];

    /**
     * dataLabels - Data labels
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.description', 'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',        'field' => 'data',        'active' => false],
        3 => ['title' => 'label.assumptions', 'field' => 'assumptions', 'active' => false],
    ];
}
