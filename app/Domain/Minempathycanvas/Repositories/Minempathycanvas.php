<?php

/**
 * Repository
 */

namespace Leantime\Domain\Minempathycanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Minempathycanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'minempathy';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-solid fa-heart-circle-check';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'minempathy_who' => ['icon' => '', 'title' => 'box.minempathy.who'],
        'minempathy_struggles' => ['icon' => '', 'title' => 'box.minempathy.struggles'],
        'minempathy_where' => ['icon' => '', 'title' => 'box.minempathy.where'],
        'minempathy_why' => ['icon' => '', 'title' => 'box.minempathy.why'],
        'minempathy_how' => ['icon' => '', 'title' => 'box.minempathy.how'],
    ];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.description',  'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',               'field' => 'data',        'active' => true],
        3 => ['title' => 'label.assumptions',   'field' => 'assumptions', 'active' => true],
    ];

    /**
     * relatesLabels - Relates to label
     *
     * @acces public
     */
    protected array $relatesLabels = [];
}
