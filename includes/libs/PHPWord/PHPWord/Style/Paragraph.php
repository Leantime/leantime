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
 * PHPWord_Style_Paragraph
 *
 * @category   PHPWord
 * @package    PHPWord_Style
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Style_Paragraph {
	
	/**
	 * Paragraph alignment
	 * 
	 * @var string
	 */
	private $_align;
	
	/**
	 * Space before Paragraph
	 * 
	 * @var int
	 */
	private $_spaceBefore;
	
	/**
	 * Space after Paragraph
	 * 
	 * @var int
	 */
	private $_spaceAfter;
	
	/**
	 * Spacing between breaks
	 * 
	 * @var int
	 */
	private $_spacing;
	
	
	/**
	 * New Paragraph Style
	 */
	public function __construct() {
		$this->_align           = null;
		$this->_spaceBefore     = null;
		$this->_spaceAfter      = null;
		$this->_spacing         = null;
	}
	
	/**
	 * Set Style value
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function setStyleValue($key, $value) {
		if($key == '_spacing') {
			$value += 240; // because line height of 1 matches 240 twips
		}
		$this->$key = $value;
	}

	/**
	 * Get Paragraph Alignment
	 * 
	 * @return string
	 */
	public function getAlign() {
		return $this->_align;
	}

	/**
	 * Set Paragraph Alignment
	 * 
	 * @param string $pValue
	 * @return PHPWord_Style_Paragraph
	 */
	public function setAlign($pValue = null) {
		if(strtolower($pValue) == 'justify') {
			// justify becames both
			$pValue = 'both';
		}
		$this->_align = $pValue;
		return $this;
	}

	/**
	 * Get Space before Paragraph
	 * 
	 * @return string
	 */
	public function getSpaceBefore() {
		return $this->_spaceBefore;
	}

	/**
	 * Set Space before Paragraph
	 * 
	 * @param int $pValue
	 * @return PHPWord_Style_Paragraph
	 */
	public function setSpaceBefore($pValue = null) {
	   $this->_spaceBefore = $pValue;
	   return $this;
	}

	/**
	 * Get Space after Paragraph
	 * 
	 * @return string
	 */
	public function getSpaceAfter() {
		return $this->_spaceAfter;
	}

	/**
	 * Set Space after Paragraph
	 * 
	 * @param int $pValue
	 * @return PHPWord_Style_Paragraph
	 */
	public function setSpaceAfter($pValue = null) {
	   $this->_spaceAfter = $pValue;
	   return $this;
	}

	/**
	 * Get Spacing between breaks
	 * 
	 * @return int
	 */
	public function getSpacing() {
		return $this->_spacing;
	}

	/**
	 * Set Spacing between breaks
	 * 
	 * @param int $pValue
	 * @return PHPWord_Style_Paragraph
	 */
	public function setSpacing($pValue = null) {
	   $this->_spacing = $pValue;
	   return $this;
	}
}
?>