<?php
/**
 * Strategy Brief - Repository
 */
namespace leantime\domain\repositories {

    class sbcanvas extends \leantime\library\canvas\repository
    {
		
	    /**
		 * Constant that must be redefined
		 */
	    protected const CANVAS_NAME = 'sb';
		
		/**
		 * canvasTypes - Must be extended
		 *
		 * @acces public
		 * @var   array
		 */
        public $canvasTypes = [ "sb_industry" => "box.sb.industry",
								"sb_description" => "box.sb.description",
								"sb_st_design" => "box.sb.st_design",
								"sb_st_decision" => "box.sb.st_decision",
								"sb_st_experts" => "box.sb.st_experts",
								"sb_st_support" => "box.sb.st_support",
								"sb_budget" => "box.sb.budget",
								"sb_time" => "box.sb.time",
								"sb_culture" => "box.sb.culture",
								"sb_change" => "box.sb.change",
								"sb_principles" => "box.sb.principles"
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
