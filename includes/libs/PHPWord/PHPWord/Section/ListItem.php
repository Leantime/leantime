<?php
/**
 * PHPWord
 *
 * Copyright (c) 2011 PHPWord
 *
 * This library is free software; you can redistribute it and/or
 * modify it under the terms of the GNU Lesser General Public
 * License as published by the Free Software Foundation; either
 * version 2.1 of the License, or (at your option) any later version.
 *
 * This library is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the GNU
 * Lesser General Public License for more details.
 *
 * You should have received a copy of the GNU Lesser General Public
 * License along with this library; if not, write to the Free Software
 * Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA  02110-1301  USA
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 010 PHPWord
 * @license    http://www.gnu.org/licenses/old-licenses/lgpl-2.1.txt    LGPL
 * @version    Beta 0.6.3, 08.07.2011
 */


/**
 * PHPWord_Section_ListItem
 *
 * @category   PHPWord
 * @package    PHPWord_Section
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Section_ListItem {
	
	/**
	 * ListItem Style
	 * 
	 * @var PHPWord_Style_ListItem
	 */
	private $_style;
	
	/**
	 * Textrun
	 * 
	 * @var PHPWord_Section_Text
	 */
	private $_textObject;
	
	/**
	 * ListItem Depth
	 * 
	 * @var int
	 */
	private $_depth;
	
	
	/**
	 * Create a new ListItem
	 * 
	 * @param string $text
	 * @param int $depth
	 * @param mixed $styleText
	 * @param mixed $styleList
	 */
	public function __construct($text, $depth = 0, $styleFont = null, $styleList = null, $styleParagraph = null) {
		$this->_style = new PHPWord_Style_ListItem();
		$this->_textObject = new PHPWord_Section_Text($text, $styleFont, $styleParagraph);
		$this->_depth = $depth;
		
		if(!is_null($styleList) && is_array($styleList)) {
			foreach($styleList as $key => $value) {
				if(substr($key, 0, 1) != '_') {
					$key = '_'.$key;
				}
				$this->_style->setStyleValue($key, $value);
			}
		}
	}
	
	/**
	 * Get ListItem style
	 */
	public function getStyle() {
		return $this->_style;
	}
	
	/**
	 * Get ListItem TextRun
	 */
	public function getTextObject() {
		return $this->_textObject;
	}
	
	/**
	 * Get ListItem depth
	 */
	public function getDepth() {
		return $this->_depth;
	}
}
?>