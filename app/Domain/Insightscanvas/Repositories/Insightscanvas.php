<?php

/**
 * Insights - Repository.
 */

namespace Leantime\Domain\Insightscanvas\Repositories;

use Leantime\Domain\Canvas\Repositories\Canvas;

class Insightscanvas extends Canvas
{
    /**
     * Constant that must be redefined.
     */
    protected const CANVAS_NAME = 'insights';

    /***
     * icon - Icon associated with canvas (must be extended)
     *
     * @access public
     * @var    string Fontawesome icone
     */
    protected string $icon = 'fa-note-sticky';

    /**
     * canvasTypes - Must be extended.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $canvasTypes = [
        'insights_oberve'             => ['icon' => 'fa-tower-observation', 'title' => 'box.insights.observe'],
        'insights_interview'          => ['icon' => 'fa-people-arrows',     'title' => 'box.insights.interview'],
        'insights_focus_groups'       => ['icon' => 'fa-people-line',       'title' => 'box.insights.focus_groups'],
        'insights_secondary_research' => ['icon' => 'fa-book',              'title' => 'box.insights.secondary_research'],
        'insights_knowledge'          => [
            'icon'  => 'fa-file-signature',    'title' => 'box.insights.knowledge',
            'color' => '#e3e3e3',
        ],
    ];

    /**
     * dataLabels - Data labels.
     *
     * @acces protected
     *
     * @var array
     */
    protected array $dataLabels = [
        1 => ['title' => 'label.insights.insight', 'field' => 'conclusion', 'active' => true],
        2 => ['title' => 'label.insights.data',    'field' => 'data',       'active' => true],
        3 => ['title' => '', 'field' => 'assumptions', 'active' => false],
    ];
}
