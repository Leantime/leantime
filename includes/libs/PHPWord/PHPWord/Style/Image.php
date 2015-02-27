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
 * PHPWord_Style_Image
 *
 * @category   PHPWord
 * @package    PHPWord_Section
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Style_Image {
	
	private $_width;
	private $_height;
	private $_align;
	
	/**
	 * Margin Top
	 * 
	 * @var int
	 */
	private $_marginTop;
	
	/**
	 * Margin Left
	 * 
	 * @var int
	 */
	private $_marginLeft;
	
	public function __construct() {
		$this->_width  = null;
		$this->_height = null;
		$this->_align = null;
		$this->_marginTop = null;
		$this->_marginLeft = null;
	}
	
	public function setStyleValue($key, $value) {
		$this->$key = $value;
	}
	
	public function getWidth() {
		return $this->_width;
	}
	
	public function setWidth($pValue = null) {
		$this->_width = $pValue;
	}
	
	public function getHeight() {
		return $this->_height;
	}
	
	public function setHeight($pValue = null) {
		$this->_height = $pValue;
	}
	
	public function getAlign() {
		return $this->_align;
	}
	
	public function setAlign($pValue = null) {
		$this->_align = $pValue;
	}
	
	/**
	 * Get Margin Top
	 * 
	 * @return int
	 */
	public function getMarginTop() {
		return $this->_marginTop;
	}

	/**
	 * Set Margin Top
	 * 
	 * @param int $pValue
	 */
	public function setMarginTop($pValue = null) {
		$this->_marginTop = $pValue;
		return $this;
	}

	/**
	 * Get Margin Left
	 * 
	 * @return int
	 */
	public function getMarginLeft() {
		return $this->_marginLeft;
	}

	/**
	 * Set Margin Left
	 * 
	 * @param int $pValue
	 */
	public function setMarginLeft($pValue = null) {
		$this->_marginLeft = $pValue;
		return $this;
	}
}
?>
