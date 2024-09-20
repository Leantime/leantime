<?php

/**
 * Repository.
 */

namespace Leantime\Domain\Smcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Smcanvas extends Canvas
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'sm';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-chess';

    /**
     * canvasTypes - Must be extended.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $canvasTypes = [
        'sm_qa' => ['icon' => 'fa-clipboard-question', 'title' => 'box.sm.qa'],
        'sm_qb' => ['icon' => 'fa-clipboard-question', 'title' => 'box.sm.qb'],
        'sm_qc' => ['icon' => 'fa-clipboard-question', 'title' => 'box.sm.qc'],
        'sm_qd' => ['icon' => 'fa-clipboard-question', 'title' => 'box.sm.qd'],
        'sm_qe' => ['icon' => 'fa-clipboard-question', 'title' => 'box.sm.qe'],
        'sm_qf' => ['icon' => 'fa-clipboard-question', 'title' => 'box.sm.qf'],
        'sm_qg' => ['icon' => 'fa-clipboard-question', 'title' => 'box.sm.qg'],
    ];

    /**
     * dataLabels - Data labels (may be extended).
     *
     * @acces protected
     *
     * @var array
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.sm.description', 'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',           'field' => 'data',        'active' => false],
        3 => ['title' => 'label.assumptions',    'field' => 'assumptions', 'active' => false],
    ];

    /**
     * statusLabels - Status labels (may be extended).
     *
     * @acces protected
     *
     * @var array
     */
    protected array $statusLabels = [
        'status_draft'    => ['icon' => 'fa-circle-question',    'color' => 'blue',   'title' => 'status.draft',    'dropdown' => 'info',    'active' => true],
        'status_review'   => ['icon' => 'fa-circle-exclamation', 'color' => 'orange', 'title' => 'status.review',   'dropdown' => 'warning', 'active' => true],
        'status_accepted' => ['icon' => 'fa-circle-check',       'color' => 'green',  'title' => 'status.accepted', 'dropdown' => 'success', 'active' => true],
        'status_rejected' => ['icon' => 'fa-circle-xmark',       'color' => 'red',    'title' => 'status.rejected', 'dropdown' => 'danger',  'active' => true],
    ];

    /**
     * relatesLabels - Relates to label (same structure as `statusLabels`).
     *
     * @acces public
     *
     * @var array
     */
    protected array $relatesLabels = [];
}
