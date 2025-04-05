<?php

/**
 * Repository
 */

namespace Leantime\Domain\Swotcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Swotcanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'swot';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-chess-board';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'swot_strengths' => ['icon' => 'fa-dumbbell', 'title' => 'box.swot.strengths'],
        'swot_weaknesses' => ['icon' => 'fa-fire', 'title' => 'box.swot.weaknesses'],
        'swot_opportunities' => ['icon' => 'fa-clover', 'title' => 'box.swot.opportunities'],
        'swot_threats' => ['icon' => 'fa-bolt-lightning', 'title' => 'box.swot.threats'],
    ];

    /**
     * statusLabels - Status labels (may be extended)
     *
     * @acces protected
     */
    protected array $statusLabels = [];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.description', 'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',        'field' => 'data',        'active' => true],
        3 => ['title' => 'label.assumptions', 'field' => 'assumptions', 'active' => false],
    ];
}
