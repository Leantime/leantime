<?php

/**
 * Repository
 */

namespace Leantime\Domain\Sqcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Sqcanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'sq';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'extension';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'sq_qa' => ['icon' => 'counter_1', 'title' => 'box.sq.qa'],
        'sq_qb' => ['icon' => 'counter_2', 'title' => 'box.sq.qb'],
        'sq_qc' => ['icon' => 'counter_3', 'title' => 'box.sq.qc'],
        'sq_qd' => ['icon' => 'counter_4', 'title' => 'box.sq.qd'],
        'sq_qe' => ['icon' => 'counter_5', 'title' => 'box.sq.qe'],
    ];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.sq.description', 'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',           'field' => 'data',        'active' => false],
        3 => ['title' => 'label.assumptions',    'field' => 'assumptions', 'active' => false],
    ];

    /**
     * statusLabels - Status labels (may be extended)
     *
     * @acces protected
     */
    protected array $statusLabels = [
        'status_draft' => ['icon' => 'help',    'color' => 'blue',   'title' => 'status.draft',    'dropdown' => 'info',    'active' => true],
        'status_review' => ['icon' => 'error', 'color' => 'orange', 'title' => 'status.review',   'dropdown' => 'warning', 'active' => true],
        'status_accepted' => ['icon' => 'check_circle',       'color' => 'green',  'title' => 'status.accepted', 'dropdown' => 'success', 'active' => true],
        'status_rejected' => ['icon' => 'cancel',       'color' => 'red',    'title' => 'status.rejected', 'dropdown' => 'danger',  'active' => true],
    ];

    /**
     * relatesLabels - Relates to label (same structure as `statusLabels`)
     *
     * @acces public
     */
    protected array $relatesLabels = [];
}
