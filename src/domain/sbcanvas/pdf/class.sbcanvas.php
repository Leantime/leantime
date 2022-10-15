<?php
/**
 * Strategy Brief - HTML code for PDF report
 */
namespace leantime\domain\pdf {
  
	use leantime\domain\repositories;
	
    class sbcanvas extends \leantime\domain\pdf\canvas {

		protected const CANVAS_NAME = 'sb';
        
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
            $html .= '<div>'.$this->htmlListTitle("headline.".static::CANVAS_NAME.".board", $this->canvasRepo->getIcon()).'</div>';
			$html .= '<div style="margin-top: 5px; margin-bottom: 5px;">'.$_SESSION['currentProjectName'].'</div><hr class="hr-black"/>';
			foreach($this->canvasType as $key => $data) {
				$html .= '<div>'.$this->htmlListTitle($data['title'], $data['icon']).'</div>';
				$html .= '<div>'.$this->htmlListElementsShort($recordsAry, $key).'</div>';
			}
            $html .= '<div>'.$this->htmlListTitle('box.sb.risks', 'fa-person-falling').'</div>';
            $html .= '<div style="margin-top: 5px; margin-bottom: 5px;">'.
                sprintf($this->language->__('text.sb.risks_analysis'), $this->config->appUrl).'</div><hr class="hr-black"/>';
            return $html;
            
        }

        /***
         * reportGenerate - Generate report for module  / Porter's Startegy Questions
         *
         * @access public
         * @param  int    $id     Canvas identifier
         * @param  string $filter Filter value
         * @return string PDF filename
         */
        public function reportGenerate(int $id, array $filter = []): string
        {
            // Retrieve canvas data
            $sbCanvasRepo = new repositories\sbcanvas();
            $sbCanvasAry = $sbCanvasRepo->getSingleCanvas($id);
            !empty($sbCanvasAry) || die("Cannot find canvas with id '$id'");
            $projectId = $sbCanvasAry[0]['projectId'];
            $recordsAry = $sbCanvasRepo->getCanvasItemsById($id);
            $projectsRepo = new repositories\projects();
            $projectAry = $projectsRepo->getProject($projectId);
            !empty($projectAry) || die("Cannot retrieve project id '$projectId'");
            
            // Configuration
            $options = [ 'canvasShow' => false ];
            
            // Generate PDF content
            $pdf = new \YetiForcePDF\Document();
            $pdf->init();
            $pdf->loadHtml($this->htmlReport($projectAry['name'], $sbCanvasAry[0]['title'], $recordsAry, $filter, $options));
            return $pdf->render();
        }
    
    }
}
?>
