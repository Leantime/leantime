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
 * PHPWord_Style_Cell
 *
 * @category   PHPWord
 * @package    PHPWord_Style
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Style_Cell {
	
	const TEXT_DIR_BTLR = 'btLr';
	const TEXT_DIR_TBRL = 'tbRl';
	
	/**
	 * Vertical align
	 * 
	 * @var string
	 */
	private $_valign;
	
	/**
	 * Text Direction
	 * 
	 * @var string
	 */
	private $_textDirection;
	
	/**
	 * Background-Color
	 * 
	 * @var string
	 */
	private $_bgColor;
	
	/**
	 * Border Top Size
	 * 
	 * @var int
	 */
	private $_borderTopSize;
	
	/**
	 * Border Top Color
	 * 
	 * @var string
	 */
	private $_borderTopColor;
	
	/**
	 * Border Left Size
	 * 
	 * @var int
	 */
	private $_borderLeftSize;
	
	/**
	 * Border Left Color
	 * 
	 * @var string
	 */
	private $_borderLeftColor;
	
	/**
	 * Border Right Size
	 * 
	 * @var int
	 */
	private $_borderRightSize;
	
	/**
	 * Border Right Color
	 * 
	 * @var string
	 */
	private $_borderRightColor;
	
	/**
	 * Border Bottom Size
	 * 
	 * @var int
	 */
	private $_borderBottomSize;
	
	/**
	 * Border Bottom Color
	 * 
	 * @var string
	 */
	private $_borderBottomColor;
	
	/**
	 * Border Default Color
	 * 
	 * @var string
	 */
	private $_defaultBorderColor;
	
	
	/**
	 * Create a new Cell Style
	 */
	public function __construct() {
		$this->_valign = null;
		$this->_textDirection = null;
		$this->_bgColor = null;
		$this->_borderTopSize = null;
		$this->_borderTopColor = null;
		$this->_borderLeftSize = null;
		$this->_borderLeftColor = null;
		$this->_borderRightSize = null;
		$this->_borderRightColor = null;
		$this->_borderBottomSize = null;
		$this->_borderBottomColor = null;
		$this->_defaultBorderColor = '000000';
	}
	
	/**
	 * Set style value
	 * 
	 * @var string $key
	 * @var mixed $value
	 */
	public function setStyleValue($key, $value) {
		if($key == '_borderSize') {
			$this->setBorderSize($value);
		} elseif($key == '_borderColor') {
			$this->setBorderColor($value);
		} else {
			$this->$key = $value;
		}
	}
	
	public function getVAlign() {
		return $this->_valign;
	}
	
	public function setVAlign($pValue = null) {
		$this->_valign = $pValue;
	}
	
	public function getTextDirection() {
		return $this->_textDirection;
	}
	
	public function setTextDirection($pValue = null) {
		$this->_textDirection = $pValue;
	}
	
	public function getBgColor() {
		return $this->_bgColor;
	}

	public function setBgColor($pValue = null) {
	   $this->_bgColor = $pValue;
	}

	public function setHeight($pValue = null) {
	   $this->_height = $pValue;
	}
	
	public function setBorderSize($pValue = null) {
		$this->_borderTopSize = $pValue;
		$this->_borderLeftSize = $pValue;
		$this->_borderRightSize = $pValue;
		$this->_borderBottomSize = $pValue;
	}
	
	public function getBorderSize() {
		$t = $this->getBorderTopSize();
		$l = $this->getBorderLeftSize();
		$r = $this->getBorderRightSize();
		$b = $this->getBorderBottomSize();
		
		return array($t, $l, $r, $b);
	}
	
	public function setBorderColor($pValue = null) {
		$this->_borderTopColor = $pValue;
		$this->_borderLeftColor = $pValue;
		$this->_borderRightColor = $pValue;
		$this->_borderBottomColor = $pValue;
	}
	
	public function getBorderColor() {
		$t = $this->getBorderTopColor();
		$l = $this->getBorderLeftColor();
		$r = $this->getBorderRightColor();
		$b = $this->getBorderBottomColor();
		
		return array($t, $l, $r, $b);
	}
	
	public function setBorderTopSize($pValue = null) {
		$this->_borderTopSize = $pValue;
	}
	
	public function getBorderTopSize() {
		return $this->_borderTopSize;
	}
	
	public function setBorderTopColor($pValue = null) {
		$this->_borderTopColor = $pValue;
	}
	
	public function getBorderTopColor() {
		return $this->_borderTopColor;
	}

	
	public function setBorderLeftSize($pValue = null) {
		$this->_borderLeftSize = $pValue;
	}
	
	public function getBorderLeftSize() {
		return $this->_borderLeftSize;
	}
	
	public function setBorderLeftColor($pValue = null) {
		$this->_borderLeftColor = $pValue;
	}
	
	public function getBorderLeftColor() {
		return $this->_borderLeftColor;
	}
	
	
	public function setBorderRightSize($pValue = null) {
		$this->_borderRightSize = $pValue;
	}
	
	public function getBorderRightSize() {
		return $this->_borderRightSize;
	}
	
	public function setBorderRightColor($pValue = null) {
		$this->_borderRightColor = $pValue;
	}
	
	public function getBorderRightColor() {
		return $this->_borderRightColor;
	}
	
	
	public function setBorderBottomSize($pValue = null) {
		$this->_borderBottomSize = $pValue;
	}
	
	public function getBorderBottomSize() {
		return $this->_borderBottomSize;
	}
	
	public function setBorderBottomColor($pValue = null) {
		$this->_borderBottomColor = $pValue;
	}
	
	public function getBorderBottomColor() {
		return $this->_borderBottomColor;
	}
	
	public function getDefaultBorderColor() {
		return $this->_defaultBorderColor;
	}
}
?>