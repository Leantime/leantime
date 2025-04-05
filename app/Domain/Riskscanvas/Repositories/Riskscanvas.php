<?php

/**
 * Repository
 */

namespace Leantime\Domain\Riskscanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Riskscanvas extends Canvas
{
    /**
     * Constant that must be redefined
     */
    protected const CANVAS_NAME = 'risks';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-person-falling';

    /**
     * canvasTypes - Must be extended
     *
     * @acces protected
     */
    protected array $canvasTypes = [
        'risks_imp_low_pro_low' => ['icon' => '', 'title' => 'box.risks.imp_low_pro_low'],
        'risks_imp_low_pro_high' => ['icon' => '', 'title' => 'box.risks.imp_low_pro_high'],
        'risks_imp_high_pro_low' => ['icon' => '', 'title' => 'box.risks.imp_high_pro_low'],
        'risks_imp_high_pro_high' => ['icon' => '', 'title' => 'box.risks.imp_high_pro_high'],
    ];

    /**
     * dataLabels - Data labels (may be extended)
     *
     * @acces protected
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.risks.description',  'field' => 'conclusion',  'active' => true],
        2 => ['title' => 'label.data',               'field' => 'data',        'active' => true],
        3 => ['title' => 'label.risks.mitigation',   'field' => 'assumptions', 'active' => true],
    ];
}
