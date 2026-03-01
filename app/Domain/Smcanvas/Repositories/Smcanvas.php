<?php

/**
 * Repository
 */

namespace Leantime\Domain\Smcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Smcanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'sm';

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
        'sm_qa' => ['icon' => 'quiz', 'title' => 'box.sm.qa'],
        'sm_qb' => ['icon' => 'quiz', 'title' => 'box.sm.qb'],
        'sm_qc' => ['icon' => 'quiz', 'title' => 'box.sm.qc'],
        'sm_qd' => ['icon' => 'quiz', 'title' => 'box.sm.qd'],
        'sm_qe' => ['icon' => 'quiz', 'title' => 'box.sm.qe'],
        'sm_qf' => ['icon' => 'quiz', 'title' => 'box.sm.qf'],
        'sm_qg' => ['icon' => 'quiz', 'title' => 'box.sm.qg'],
    ];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.sm.description', 'field' => 'conclusion',  'active' => true],
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
