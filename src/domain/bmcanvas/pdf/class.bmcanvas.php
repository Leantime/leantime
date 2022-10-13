<?php
/**
 * Lightweight Business Moidel Canvas - HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class bmcanvas extends \leantime\library\pdf\template {
        
		private const DISCLAIMER_LBM = 'Lightweight Business Model Canvas is taken from "Design Thinking for Strategy", based on "Business Model Canvas" from Strategyzer, and licensed under the Creative Commons Attribution-Share Alike 3.0';
		private const DISCLAIMER_OBM = 'Business Model Canvas from Strategyzer licensed under the Creative Commons Attribution-Share Alike 3.0';
		private const DISCLAIMER_DBM = 'Detailed Business Model Canvas is taken from "Design Thinking for Strategy", based on "Business Model Canvas" from Strategyzer, and licensed under the Creative Commons Attribution-Share Alike 3.0';

		private string $template;

		/** 
         * filterToStatus - Convert a filter to a value of the status field
         *
         * @access protected
         * @param  string $filter Filter name
         * @return string Status value associated with filter
         */
        protected function filterToStatus(string $filter): string
        {
            
			return match($filter)
				{'validated_false' => 'danger', 'validated_true' => 'success', 'not_validated' => 'info', default => 'all' };

        }

		/**
		 * htmlCanvasStatus - Return HTML code showing status of canvas element
		 *
		 * @access protected
		 * @param  string $status Element status key
		 * @return string HTML code
		 */
		protected function htmlCanvasStatus(string $status): string
		{
			
			return ' &mdash; <span style="color: '.
				(match($status) { 'info' => 'grey', 'danger' => 'red', default => 'green' }).';">'.
				$this->htmlIcon(match($status)
								{ 'info' => 'fa-circle-question', 'danger' => 'fa-circle-xmark', default => 'fa-circle-check' }).
				'</span>';

		}
		
		/**
		 * htmlListStatus - Return HTML code showing status of list element
		 *
		 * @access protected
		 * @param  string $status Element status key
		 * @return string HTML code
		 */
		protected function htmlListStatus(string $status): string
		{
			
			return  '<span style="color: '.(match($status) { 'info' => 'grey', 'danger' => 'red', default => 'green' }).';">'.
				(match($status) { 
					'info' => $this->language->__("status.not_validated"), 
					'danger' => $this->language->__("status.validated_false"), 
					default => $this->language->__("status.validated_true") }).'</span>';

		}
		
		/**
		 * htmlCanvas -  Layout canvas / Lightweight Business Model
		 *
		 * @access protected
		 * @param  array  $recordsAry Array of canvas data records
		 * @return string HTML code
		 */
		protected function htmlCanvas(array $recordsAry): string
		{
			
			switch($this->template) {
			case 'l':
			    $html = '<table class="canvas" style="width: 100%"><tbody>'.
				    '<tr>'.
				    '  <td class="canvas-elt-title" style="width: 33%;">'.$this->htmlCanvasTitle('print.bm.customers','fa-users').'</td>'.
				    '  <td class="canvas-elt-title" style="width: 33%;">'.$this->htmlCanvasTitle('print.bm.offerings','fa-barcode').'</td>'.
				    '  <td class="canvas-elt-title" style="width: 33%;">'.
                    $this->htmlCanvasTitle('print.bm.capabilities', 'fa-pen-ruler').'</td>'.
				    '</tr>'.
				    '<tr>'.
				    '  <td class="canvas-elt-box" style="height: 420px;">'.$this->htmlCanvasElements($recordsAry, 'bm_customers').'</td>'.
					'  <td class="canvas-elt-box" style="height: 420px;">'.$this->htmlCanvasElements($recordsAry, 'bm_offerings').'</td>'.
					'  <td class="canvas-elt-box" style="height: 420px;">'.$this->htmlCanvasElements($recordsAry, 'bm_capabilities').'</td>'.
				    '</tr>'.
					'<tr>'.
				    '  <td class="canvas-elt-title" style="width: 100%;" colspan="3">'.
					$this->htmlCanvasTitle('print.bm.financials', 'fa-money-bill').'</td>'.
					'</tr>'.
                    '<tr>'.
					'  <td class="canvas-elt-box" style="height: 210px;" colspan="3">'.
                    $this->htmlCanvasElements($recordsAry, 'bm_financials').'</td>'.
                    '</tr>'.
					'</tbody></table>';
		        return $html;

			case 'o':
	    $html = '<table class="canvas" style="width: 100%;"><tbody>'.
          '  <tr>'.
          '    <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.$this->htmlCanvasTitle('print.bm.kp', 'fa-handshake').'</td>'.
          '    <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.$this->htmlCanvasTitle('print.bm.ka', 'fa-person-digging').'</td>'.
          '    <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.$this->htmlCanvasTitle('print.bm.ovp', 'fa-money-bill-transfer').'</td>'.
          '    <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.$this->htmlCanvasTitle('print.bm.cr', 'fa-heart').'</td>'.
          '    <td class="canvas-elt-title" style="width: 5%;" colspan="2">'.$this->htmlCanvasTitle('print.bm.cs', 'fa-users').'</td>'.
          '  </tr>'.
          '  <tr>'.
          '    <td class="canvas-elt-box" style="height: 420px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'bm_kp').'</td>'.
          '    <td style="height: 220px;" colspan="2" style="border: 0px">'.
          '      <table class="canvas" style="width: 100%"><tbody>'.
          '        <tr>'.
          '          <td class="canvas-elt-box" style="height: 197px; width: 100%;">'.$this->htmlCanvasElements($recordsAry, 'bm_ka').'</td>'.
          '        </tr>'.
          '        <tr>'.
          '          <td class="canvas-elt-title">'.$this->htmlCanvasTitle('print.bm.krl', 'fa-apple-whole').'</td>'.
          '        </tr>'.
          '        <tr>'.
          '          <td class="canvas-elt-box" style="height: 198px;">'.$this->htmlCanvasElements($recordsAry, 'bm_kr').'</td>'.
          '        </tr>'.
          '      </tbody></table>'.
          '    </td>'.
          '    <td class="canvas-elt-box" style="height: 420px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'bm_ovp').'</td>'.
          '    <td style="height: 220px;" colspan="2">'.
          '      <table class="canvas" style="width: 100%"><tbody>'.
          '        <tr>'.
          '          <td class="canvas-elt-box" style="height: 197px; width: 50%;">'.$this->htmlCanvasElements($recordsAry, 'bm_cr').'</td>'.
          '        </tr>'.
          '        <tr>'.
          '          <td class="canvas-elt-title">'.$this->htmlCanvasTitle('print.bm.cd', 'fa-truck').'</td>'.
          '        </tr>'.
          '        <tr>'.
          '          <td class="canvas-elt-box" style="height: 198px;">'.$this->htmlCanvasElements($recordsAry, 'bm_cd').'</td>'.
          '        </tr>'.
          '      </tbody></table>'.
          '    </td>'.
          '    <td class="canvas-elt-box" style="height: 420px;" colspan="2">'.$this->htmlCanvasElements($recordsAry, 'bm_cs').'</td>'.
          '  </tr>'.
          '  <tr>'.
          '    <td class="canvas-elt-title" colspan="5">'.$this->htmlCanvasTitle('print.bm.fr', 'fa-sack-dollar').'</td>'.
          '    <td class="canvas-elt-title" colspan="5">'.$this->htmlCanvasTitle('print.bm.fc', 'fa-tags').'</td>'.
          '  </tr>'.
          '  <tr>'.
          '    <td class="canvas-elt-box" style="height: 210px;" colspan="5">'.$this->htmlCanvasElements($recordsAry, 'bm_fr').'</td>'.
          '    <td class="canvas-elt-box" style="height: 210px;" colspan="5">'.$this->htmlCanvasElements($recordsAry, 'bm_fc').'</td>'.
          '  </tr>'.
		  '</tbody></table>';
		return $html;

			case 'd':
	    $html = '<table class="canvas" style="width: 100%;"><tbody>'.
          '  <tr>'.
          '    <td class="canvas-elt-title" style="width: 3.33%;" colspan="6">'.$this->htmlCanvasTitle('print.bm.cs', 'fa-users').'</td>'.
          '    <td class="canvas-elt-title" style="width: 3.33%;" colspan="6">'.$this->htmlCanvasTitle('print.bm.cr', 'fa-heart').'</td>'.
          '    <td class="canvas-elt-title" style="width: 3.33%;" colspan="6">'.$this->htmlCanvasTitle('print.bm.ovp', 'fa-money-bill-transfer').'</td>'.
          '    <td class="canvas-elt-title" style="width: 3.33%;" colspan="4">'.$this->htmlCanvasTitle('print.bm.kad', 'fa-chess').'</td>'.
          '    <td class="canvas-elt-title" style="width: 3.33%;" colspan="4">'.$this->htmlCanvasTitle('print.bm.kac', 'fa-hand-holding-dollar').'</td>'.
          '    <td class="canvas-elt-title" style="width: 3.33%;" colspan="4">'.$this->htmlCanvasTitle('print.bm.kao', 'fa-handshake').'</td>'.
          '  </tr>'.
          '  <tr>'.
          '    <td class="canvas-elt-box" style="height: 220px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'bm_cs').'</td>'.
          '    <td class="canvas-elt-box" style="height: 220px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'bm_cr').'</td>'.
          '    <td class="canvas-elt-box" style="height: 220px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'bm_ops').'</td>'.
          '    <td class="canvas-elt-box" style="height: 220px;" colspan="4">'.$this->htmlCanvasElements($recordsAry, 'bm_kad').'</td>'.
          '    <td class="canvas-elt-box" style="height: 220px;" colspan="4">'.$this->htmlCanvasElements($recordsAry, 'bm_kac').'</td>'.
          '    <td class="canvas-elt-box" style="height: 220px;" colspan="4">'.$this->htmlCanvasElements($recordsAry, 'bm_kao').'</td>'.
          '  </tr>'.
          '  <tr>'.
          '    <td class="canvas-elt-title" colspan="6">'.$this->htmlCanvasTitle('print.bm.cj', 'fa-user-doctor').'</td>'.
          '    <td class="canvas-elt-title" colspan="6">'.$this->htmlCanvasTitle('print.bm.cd', 'fa-truck').'</td>'.
          '    <td class="canvas-elt-title" colspan="6">'.$this->htmlCanvasTitle('print.bm.ops', 'fa-barcode').'</td>'.
          '    <td class="canvas-elt-title" colspan="6">'.$this->htmlCanvasTitle('print.bm.krp', 'fa-apple-whole').'</td>'.
          '    <td class="canvas-elt-title" colspan="6">'.$this->htmlCanvasTitle('print.bm.krc', 'fa-industry').'</td>'.
          '  </tr>'.
          '  <tr>'.
          '    <td class="canvas-elt-box" style="height: 220px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'bm_cj').'</td>'.
          '    <td class="canvas-elt-box" style="height: 220px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'bm_cd').'</td>'.
          '    <td class="canvas-elt-box" style="height: 220px;" colspan="6">'.$this->htmlCanvasElements($recordsAry, 'bm_ovp').'</td>'.
          '    <td style="height: 220px;" colspan="12">'.
          '      <table class="canvas" style="width: 100%"><tbody>'.
          '        <tr>'.
          '          <td class="canvas-elt-box" style="height: 97px; width: 50%;">'.$this->htmlCanvasElements($recordsAry, 'bm_krp').'</td>'.
          '          <td class="canvas-elt-box" style="height: 97px; width: 50%;">'.$this->htmlCanvasElements($recordsAry, 'bm_krc').'</td>'.
          '        </tr>'.
          '        <tr>'.
          '          <td class="canvas-elt-title">'.$this->htmlCanvasTitle('print.bm.krl', 'fa-person-digging').'</td>'.
          '          <td class="canvas-elt-title">'.$this->htmlCanvasTitle('print.bm.krs', 'fa-lightbulb').'</td>'.
          '        </tr>'.
          '        <tr>'.
          '          <td class="canvas-elt-box" style="height: 98px;">'.$this->htmlCanvasElements($recordsAry, 'bm_krl').'</td>'.
          '          <td class="canvas-elt-box" style="height: 98px;">'.$this->htmlCanvasElements($recordsAry, 'bm_krs').'</td>'.
          '        </tr>'.
          '      </tbody></table>'.
          '    </td>'.
          '  </tr>'.
          '  <tr>'.
          '    <td class="canvas-elt-title" colspan="15">'.$this->htmlCanvasTitle('print.bm.fr', 'fa-sack-dollar').'</td>'.
          '    <td class="canvas-elt-title" colspan="15">'.$this->htmlCanvasTitle('print.bm.fc', 'fa-tags').'</td>'.
          '  </tr>'.
          '  <tr>'.
          '    <td class="canvas-elt-box" style="height: 160px;" colspan="15">'.$this->htmlCanvasElements($recordsAry, 'bm_fr').'</td>'.
          '    <td class="canvas-elt-box" style="height: 160px;" colspan="15">'.$this->htmlCanvasElements($recordsAry, 'bm_fc').'</td>'.
          '  </tr>'.
		  '</tbody></table>';
		return $html;

			default:
				die("Invalid pdf template '".$this->template."'in class bmcanvas");
			}
			
	    }
	
		/**
		 * htmlList - Layout element list / Lightweight Business Model
		 *
		 * @access protected
		 * @param  array  $recordsAry Array of canvas data records
		 * @return string HTML code
		 */
		protected function htmlList(array $recordsAry): string
		{
			$html = '';
			switch($this->template) {
			case 'l':
				$html .= '<div>'.$this->htmlListTitle('print.bm.customers', 'fa-users').'</div>';
				$html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_customers').'</div>';
				$html .= '<div>'.$this->htmlListTitle('print.bm.offerings', 'fa-users').'</div>';
				$html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_offerings').'</div>';
				$html .= '<div>'.$this->htmlListTitle('print.bm.capabilities', 'fa-users').'</div>';
				$html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_capabilities').'</div>';
				$html .= '<div>'.$this->htmlListTitle('print.bm.financials', 'fa-users').'</div>';
				$html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_financials').'</div>';
				return $html;

			case 'o':
	            $html .= '<div>'.$this->htmlListTitle('print.bm.kp', 'fa-handshake').'</div>';
                $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_kp').'</div>';
	            $html .= '<div>'.$this->htmlListTitle('print.bm.ka', 'fa-person-digging').'</div>';
                $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_ka').'</div>';
	            $html .= '<div>'.$this->htmlListTitle('print.bm.kr', 'fa-apple-whole').'</div>';
                $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_kr').'</div>';

	            $html .= '<div>'.$this->htmlListTitle('print.bm.ovp', 'fa-money-bill-transfer').'</div>';
                $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_ovp').'</div>';

	            $html .= '<div>'.$this->htmlListTitle('print.bm.cr', 'fa-truck').'</div>';
                $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_cr').'</div>';
	            $html .= '<div>'.$this->htmlListTitle('print.bm.cd', 'fa-users').'</div>';
                $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_cd').'</div>';
	            $html .= '<div>'.$this->htmlListTitle('print.bm.cs', 'fa-user-doctor').'</div>';
                $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_cs').'</div>';
	            return $html;

			case 'd':
	  $html .= '<div>'.$this->htmlListTitle('print.bm.cs', 'fa-users').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_cs').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.cj', 'fa-user-doctor').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_cj').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.cr', 'fa-heart').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_cr').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.cd', 'fa-truck').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_cd').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.ovp', 'fa-money-bill-transfer').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_ovp').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.ops', 'fa-barcode').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_ops').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.kad', 'fa-chess').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_kad').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.kac', 'fa-hand-holding-dollar').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_kac').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.kao', 'fa-handshake').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_kao').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.krp', 'fa-apple-whole').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_krp').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.krc', 'fa-industry').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_krc').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.krl', 'fa-person-digging').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_krl').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.krc', 'fa-lightbulb').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_krc').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.fr', 'fa-sack-dollar').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_fr').'</div>';
	  $html .= '<div>'.$this->htmlListTitle('print.bm.fc', 'fa-tags').'</div>';
      $html .= '<div>'.$this->htmlListElements($recordsAry, 'bm_fc').'</div>';
	  return $html;

			default:
				die("Invalid pdf template '".$this->template."'in class bmcanvas");
			}

		}

        /***
         * reportGenerate - Generate report for module  / Porter's Startegy Questions
         *
         * @access public
         * @param  int    $id     Canvas identifier
         * @param  string $filter Filter value
         * @return string PDF filename
         */
        public function reportGenerate(int $id, string $filter, string $template = 'l'): string
        {
			
            // Retrieve canvas data
            $bmCanvasRepo = new repositories\bmcanvas();
            $bmCanvasAry = $bmCanvasRepo->getSingleCanvas($id);
            !empty($bmCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $bmCanvasAry[0]['projectId'];
            $recordsAry = $bmCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
			$this->template = $template;
			switch($this->template) {
			case 'l':
				$options = [ 'disclaimer' => self::DISCLAIMER_LBM ];
				break;

			case 'o':
				$options = [ 'disclaimer' => self::DISCLAIMER_OBM ];
				break;

			case 'd':
				$options = [ 'disclaimer' => self::DISCLAIMER_DBM ];
				break;

			default:
				die("Invalid pdf template '".$this->template."' in class bmcanvas");
			}
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport('print.bm.title', $projectAry['name'], $bmCanvasAry[0]['title'], $recordsAry, $filter, 
                           $options));
            return $pdf->render();
			
        }
    
    }
}
?>
