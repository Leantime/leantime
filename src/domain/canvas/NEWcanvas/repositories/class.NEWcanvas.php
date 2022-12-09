<?php
/**
 * Repository
 */
namespace leantime\domain\repositories {

    class NEWcanvas extends \leantime\domain\repositories\canvas
    {
        
        /**
         * Constant that must be redefined
         */
        protected const CANVAS_NAME = 'NEW';

		/***
		 * icon - Icon associated with canvas (must be extended)
		 *
		 * @access public
		 * @var    string Fontawesome icone
		 */
		protected string $icon = 'fa-XXX';
        
        /**
         * canvasTypes - Must be extended
         *
         * @acces protected
         * @var   array
         */
        protected array $canvasTypes = [ 'NEW_XXX' => [ 'icon' => 'fa-XXX', 'title' => 'box.NEW._XXX' ], 
										 ];
		
    }
}
