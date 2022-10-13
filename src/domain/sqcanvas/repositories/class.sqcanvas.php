<?php
/**
 * Porter's Five Strategy Questions - Repository
 */
namespace leantime\domain\repositories {

    class sqcanvas extends \leantime\library\canvas\repository
    {
		
	    /**
		 * Constant that must be redefined
		 */
	    protected const CANVAS_NAME = 'sq';
		
		/**
		 * canvasTypes - Must be extended
		 *
		 * @acces public
		 * @var   array
		 */
        public $canvasTypes = [ "sq_a" => "box.sq.a",
								 "sq_b" => "box.sq.b",
								 "sq_c" => "box.sq.c",
								 "sq_d" => "box.sq.d",
								 "sq_e" => "box.sq.e"
								];
		
		/**
		 * statusLabels - Must be extended
		 *
		 * @acces public
		 * @var   array
		 */
        public $statusLabels = [ "info" => "print.draft",
								 "warning" => "print.review",
								 "success" => "print.valid",
								 "danger" => "print.invalid"
								 ];
    }
}
