<?php

/**
 * Repository
 */

namespace Leantime\Domain\Retroscanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Retroscanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'retros';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'back_hand';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'well' => ['icon' => 'check_circle', 'title' => 'box.retros.continue'],
        'notwell' => ['icon' => 'cancel', 'title' => 'box.retros.stop_doing'],
        'startdoing' => ['icon' => 'add_circle',  'title' => 'box.retros.start_doing'],
    ];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.description', 'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',        'field' => 'data',        'active' => false],
        3 => ['title' => 'label.assumptions', 'field' => 'assumptions', 'active' => false],
    ];

    /**
     * statusLabels - Status labels (may be extended)
     *
     * @acces protected
     */
    protected array $statusLabels = [];

    protected array $relatesLabels = [];
}
