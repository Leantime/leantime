<?php
/**
 * Strategy Brief - Repository
 */
namespace leantime\domain\repositories {

    class bmcanvas extends \leantime\library\canvas\repository
    {
		
	    /**
		 * Constant that must be redefined
		 */
	    protected const CANVAS_NAME = 'bm';
		
		/**
		 * canvasTypes - Must be extended
		 *
		 * @acces public
		 * @var   array
		 */
        public $canvasTypes = [ // Lightweight business model
							   "bm_customers" => "box.bm.customers",
							   "bm_offerings" => "box.bm.offerings",
							   "bm_capabilities" => "box.bm.capabilities",
							   "bm_financials" => "box.bm.financials",
							   // Detailed and Osterwalder business models
							   "bm_cs" => "box.bm.cs",
							   "bm_cj" => "box.bm.cj",
							   "bm_cr" => "box.bm.cr",
							   "bm_cd" => "box.bm.cd",
							   "bm_ovp" => "box.bm.ovp",
							   "bm_ops" => "box.bm.ops",
							   "bm_kad" => "box.bm.kad",
							   "bm_kac" => "box.bm.kac",
							   "bm_kao" => "box.bm.kao",
							   "bm_krp" => "box.bm.krp",
							   "bm_krc" => "box.bm.krc",
							   "bm_krl" => "box.bm.krl",
							   "bm_krs" => "box.bm.krs",
							   "bm_fr" => "box.bm.fr",
							   "bm_fc" => "box.bm.fc",
							   // Osterwalder business model specific
							   "bm_kp" => "box.bm.kp",
							   "bm_ka" => "box.bm.ka",
							   "bm_kr" => "box.bm.kr"
								];
		
		
		/**
		 * statusLabels - Must be extended
		 *
		 * @acces public
		 * @var   array
		 */
        public $statusLabels = [ "info" => "print.not_validated",
								 "danger" => "print.validated_false",
								 "success" => "print.validated_true"
								 ];
    }
}
