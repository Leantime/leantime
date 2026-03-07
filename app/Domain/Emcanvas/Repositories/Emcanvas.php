<?php

/**
 * Repository
 */

namespace Leantime\Domain\Emcanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Emcanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'em';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'favorite';

    /***
     * disclaimer - Disclaimer
     *
     * @access protected
     * @var    string Disclaimer (including href)
     */
    protected string $disclaimer = 'text.em.disclaimer';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'em_who' => ['icon' => 'counter_1', 'title' => 'box.em.who'],
        'em_what' => ['icon' => 'counter_2', 'title' => 'box.em.what'],
        'em_see' => ['icon' => 'counter_3', 'title' => 'box.em.see'],
        'em_say' => ['icon' => 'counter_4', 'title' => 'box.em.say'],
        'em_do' => ['icon' => 'counter_5', 'title' => 'box.em.do'],
        'em_hear' => ['icon' => 'counter_6', 'title' => 'box.em.hear'],
        'em_pains' => ['icon' => 'sentiment_dissatisfied', 'title' => 'box.em.pains'],
        'em_gains' => ['icon' => 'sentiment_satisfied', 'title' => 'box.em.gains'],
        'em_motives' => ['icon' => 'sentiment_dissatisfied', 'title' => 'box.em.motives'],
    ];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.em.description', 'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',           'field' => 'data',        'active' => false],
        3 => ['title' => 'label.conclusion',     'field' => 'assumptions', 'active' => false],
    ];

    /**
     * relatesLabels - Relates to label (same structure as `statusLabels`)
     *
     * @acces public
     */
    protected array $relatesLabels = [];
}
