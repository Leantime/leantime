<?php

/**
 * Application class - application handling. template routing and rendering
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @license	GNU/GPL, see license.txt
 *
 */
class application {
	
	/**
	 * @access private
	 * @var string - array of scripts to render (currently only CSS and Javascript)
	 */
	private static $sections = array();
	
	/**
	 * start - renders applicaiton and routes to correct template, writes content to output buffer
	 *
	 * @access public static
	 * @return void
	 */
	public function start(){
		
		$config = new config();
		$login = new login(session::getSID());
		$frontController = frontcontroller::getInstance(ROOT);
		
		if($login->logged_in()===false){
			
			if(isset($_GET['export']) === true){
					
				ob_start();	
				$frontController->run();
				$toRender = ob_get_clean();
				
			}else{
				ob_start();
				include('includes/templates/'.TEMPLATE.'/login.php');
				$toRender = ob_get_clean();
			}
		
		}else{
			
			ob_start();
			include('includes/templates/'.TEMPLATE.'/content.php');
			$toRender = ob_get_clean();
		
		}
		
		$this->render($toRender);
			
	}
	
	/**
	 * render - render sections before outputting content
	 *
	 * @access public static
	 * @param $content - string
	 * @return void
	 */
	public function render($content){
		 
		 $contentRendered = $this->renderSections($content);
		 
		 echo $contentRendered;
		 
	}
	
	/**
	 * render - render sections before outputting content
	 *
	 * @access public
	 * @param $content - string
	 * @return string
	 */
	public function renderSections($content){
		
		$renderedSections = array();
		
		foreach(self::$sections as $section){
			
			if(isset($renderedSections[$section["position"]]) === false){
				$renderedSections[$section["position"]] = "";
			}
			
			switch($section["type"]){
				case "CSS": $renderedSections[$section["position"]] .= $this->renderCSS($section["path"]); break;
				case "SCRIPT": $renderedSections[$section["position"]] .= $this->renderJavascript($section["path"]); break;
			}
		}
		
		foreach($renderedSections as $section => $html){
			
			$content = str_replace("<!--###".$section."###-->", $html, $content);
		
		}
		
		return $content;
	}
	
	/**
	 * addToSection - sets sections before outputting content
	 *
	 * @access public static
	 * @param $path - string the path to the file to include
	 * @param $position - string the position of the section (defined in the template)
	 * @param $type - string - the type to render CSS / Javascript Only
	 * @return string
	 */
	public static function addToSection($path, $position, $type){
		
		self::$sections[] = array("path" => $path, 
								"position" => $position,
								"type" => $type);
		
	}
	
	/**
	 * renderCSS - renders CSS path
	 *
	 * @access private
	 * @param $path - string
	 * @return string fornmatted HTML
	 */
	private function renderCSS($path) {
		return '<link rel="stylesheet" href="'.$path.'" type="text/css" />'."\n";
	}
	
	/**
	 * renderJavascript - renders JS path
	 *
	 * @access private
	 * @param $path - string
	 * @return string formatted HTML
	 */
	private function renderJavascript($path) {
		return '<script type="text/javascript" src="'.$path.'"></script>'."\n";
	}
	
}
