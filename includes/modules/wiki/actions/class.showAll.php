<?php

/**
 * showAll Class - show all Articles (clients only related tickets)
 *
 * @author Marcel Folaron <marcel.folaron@gmail.com>
 * @version 1.0
 * @package modules
 * @subpackage tickets
 * @license	GNU/GPL, see license.txt
 *
 */

class showAll extends wiki{

	/**
	 * run - display template and edit data
	 *
	 * @access public
	 *
	 */
	public function run() {

		$tpl = new template();
		$helper = new helper();
		$searchTerm = '';
		$catId = '';
		if(isset($_POST['term']) === true && $_POST['term'] != ''){
			$searchTerm = ($_POST['term']);
		}
		
		if(isset($_GET['catId']) === true && $_GET['catId'] != ''){
			$catId = ($_GET['catId']);
		}
		
		$tpl->assign('categories', $this->getCategories());
		
		$tpl->assign('helper', $helper);
		$tpl->assign('allArticles', $this->getAll($searchTerm, $catId));
		$tpl->assign('tagCloud',$this->printTagCloud($this->getAllTags()));
		
		$tpl->display('wiki.showAll');

	}
	
	function printTagCloud($tagArray) {
	        // $tags is the array
			$tags = array();
			
			foreach($tagArray as $row){
				
				$temp = explode(' ', $row['tags']);
				
				for($i=0; $i<count($temp); $i++){
					
					$temp[$i] = strtolower($temp[$i]);
					
					if(array_key_exists($temp[$i], $tags)===true){
						
						$tags[$temp[$i]] = $tags[$temp[$i]] +1;
						
					}else{
						
						$tags[$temp[$i]] = 0;
						
					}
					
				}
				
			}
		
		
		
	       $tagcloudString = '';
			if(count($tags)>0){
	       arsort($tags);
	       
	        $max_size = 28; // max font size in pixels
	        $min_size = 10; // min font size in pixels
	       
	        // largest and smallest array values
	        $max_qty = max(array_values($tags));
	        $min_qty = min(array_values($tags));
	       
	        // find the range of values
	        $spread = $max_qty - $min_qty;
	        if ($spread == 0) { // we don't want to divide by zero
	                $spread = 1;
	        }
	       
	        // set the font-size increment
	        $step = ($max_size - $min_size) / ($spread);
	       
	        // loop through the tag array
	        foreach ($tags as $key => $value) {
	                // calculate font-size
	                // find the $value in excess of $min_qty
	                // multiply by the font-size increment ($size)
	                // and add the $min_size set above
	                $size = round($min_size + (($value - $min_qty) * $step));
	       
	                $tagcloudString .= '<a href="index.php?act=wiki.showAll&amp;tag='.$key.'" style="font-size: ' . $size . 'px; float:left;" 
	title="' . $value . ' things tagged with ' . $key . '">' . $key . '&nbsp;</a>&nbsp;&nbsp;';
	        }
			}
	        return $tagcloudString;
	}
	

}

?>

