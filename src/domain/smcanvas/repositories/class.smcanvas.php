<?php
/**
 * Strategy Message - Repository
 */
namespace leantime\domain\repositories {

    class smcanvas extends \leantime\library\canvas\repository
    {
		
	    /**
		 * Constant that must be redefined
		 */
	    protected const CANVAS_NAME = 'sm';
		
		/**
		 * canvasTypes - Must be extended
		 *
		 * @acces public
		 * @var   array
		 */
        public $canvasTypes = [ "sm_a" => "box.sm.a",
								"sm_b" => "box.sm.b",
								"sm_c" => "box.sm.c",
								"sm_d" => "box.sm.d",
								"sm_e" => "box.sm.e",
								"sm_f" => "box.sm.f",
								"sm_g" => "box.sm.g"
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
