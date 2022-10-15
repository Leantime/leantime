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
		protected string $icon = 'fa-list-check';
        
        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         * @var   array
         */
        protected array $canvasTypes = [ 'sb_industry'    => [ 'icon' => 'fa-industry',        'title' => 'box.sb.industry' ], 
										 'sb_description' => [ 'icon' => 'fa-file-lines',      'title' => 'box.sb.description' ], 
										 'sb_st_design'   => [ 'icon' => 'fa-people-group',    'title' => 'box.sb.st_design' ], 
								         'sb_st_decision' => [ 'icon' => 'fa-sitemap',         'title' => 'box.sb.st_decision' ], 
										 'sb_st_experts'  => [ 'icon' => 'fa-chalkboard-user', 'title' => 'box.sb.st_experts' ], 
										 'sb_st_support'  => [ 'icon' => 'fa-person-digging',  'title' => 'box.sb.st_support' ], 
                                         'sb_budget'      => [ 'icon' => 'fa-money-bills',     'title' => 'box.sb.budget' ], 
										 'sb_time'        => [ 'icon' => 'fa-business-time',   'title' => 'box.sb.time' ], 
										 'sb_culture'     => [ 'icon' => 'fa-masks-theater',   'title' => 'box.sb.culture' ], 
                                         'sb_change'      => [ 'icon' => 'fa-book-skull',      'title' => 'box.sb.change' ], 
										 'sb_principles'  => [ 'icon' => 'fa-ruler-combined',  'title' => 'box.sb.principles' ]
										 ];
		
        /**
         * dataLabels - Data labels
         *
         * @acces protected
         * @var   array
         */
        protected array $dataLabels = [ 1 => [ 'title' => 'label.description', 'field' => 'conclusion',  'active' => true ],
										2 => [ 'title' => 'label.data',        'field' => 'data',        'active' => false ],
										3 => [ 'title' => 'label.assumptions', 'field' => 'assumptions', 'active' => false ]
										];
    }
}
