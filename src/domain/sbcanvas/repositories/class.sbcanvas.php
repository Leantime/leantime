<?php
/**
 * Strategy Brief - Repository
 */
namespace leantime\domain\repositories {

    class sbcanvas extends \leantime\domain\repositories\canvas
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
		 public string $icon = 'fa-list-check';
        
        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         * @var   array
         */
        protected $canvasTypes = [ 'sb_industry'    => [ 'icon' => 'fas fa-industry',        'title' => 'box.sb.industry' ], 
                                   'sb_description' => [ 'icon' => 'fas fa-file-lines',      'title' => 'box.sb.description' ], 
								   'sb_st_design'   => [ 'icon' => 'fas fa-people-group',    'title' => 'box.sb.st_design' ], 
								   'sb_st_decision' => [ 'icon' => 'fas fa-sitemap',         'title' => 'box.sb.st_decision' ], 
								   'sb_st_experts'  => [ 'icon' => 'fas fa-chalkboard-user', 'title' => 'box.sb.st_experts' ], 
								   'sb_st_support'  => [ 'icon' => 'fas fa-person-digging',  'title' => 'box.sb.st_support' ], 
								   'sb_budget'      => [ 'icon' => 'fas fa-money-bills',     'title' => 'box.sb.budget' ], 
                                   'sb_time'        => [ 'icon' => 'fas fa-business-time',   'title' => 'box.sb.time' ], 
								   'sb_culture'     => [ 'icon' => 'fas fa-masks-theater',   'title' => 'box.sb.culture' ], 
                                   'sb_change'      => [ 'icon' => 'fas fa-book-skull',      'title' => 'box.sb.change' ], 
                                   'sb_principles'  => [ 'icon' => 'fas fa-ruler-combined',  'title' => 'box.sb.principles' ]
        ];
        
        /**
         * dataLabels - Data labels
         *
         * @acces protected
         * @var   array
         */
        protected $dataLabels = [ 1 => [ 'title' => 'label.sb.description', 'field' => 'conclusion',  'active' => true ],
                                  2 => [ 'title' => 'label.data',           'field' => 'data',        'active' => false ],
                                  3 => [ 'title' => 'label.assumptions',    'field' => 'assumptions', 'active' => false ]
                                  ];
    }
}
