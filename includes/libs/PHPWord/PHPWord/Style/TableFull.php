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
 * PHPWord_Style_TableFull
 *
 * @category   PHPWord
 * @package    PHPWord_Style
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Style_TableFull {
	
	/**
	 * Style for first row
	 * 
	 * @var PHPWord_Style_Table
	 */
	private $_firstRow = null;
	
	/**
	 * Cell Margin Top
	 * 
	 * @var int
	 */
	private $_cellMarginTop = null;
	
	/**
	 * Cell Margin Left
	 * 
	 * @var int
	 */
	private $_cellMarginLeft = null;
	
	/**
	 * Cell Margin Right
	 * 
	 * @var int
	 */
	private $_cellMarginRight = null;
	
	/**
	 * Cell Margin Bottom
	 * 
	 * @var int
	 */
	private $_cellMarginBottom = null;
	
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
	 * Border InsideH Size
	 * 
	 * @var int
	 */
	private $_borderInsideHSize;
	
	/**
	 * Border InsideH Color
	 * 
	 * @var string
	 */
	private $_borderInsideHColor;
	
	/**
	 * Border InsideV Size
	 * 
	 * @var int
	 */
	private $_borderInsideVSize;
	
	/**
	 * Border InsideV Color
	 * 
	 * @var string
	 */
	private $_borderInsideVColor;
	
	
	/**
	 * Create a new TableFull Font
	 */
	public function __construct($styleTable = null, $styleFirstRow = null, $styleLastRow = null) {
		
		if(!is_null($styleFirstRow) && is_array($styleFirstRow)) {
			$this->_firstRow = clone $this;
			
			unset($this->_firstRow->_firstRow);
			unset($this->_firstRow->_cellMarginBottom);
			unset($this->_firstRow->_cellMarginTop);
			unset($this->_firstRow->_cellMarginLeft);
			unset($this->_firstRow->_cellMarginRight);
			unset($this->_firstRow->_borderInsideVColor);
			unset($this->_firstRow->_borderInsideVSize);
			unset($this->_firstRow->_borderInsideHColor);
			unset($this->_firstRow->_borderInsideHSize);
			foreach($styleFirstRow as $key => $value) {
				if(substr($key, 0, 1) != '_') {
					$key = '_'.$key;
				}
				
				$this->_firstRow->setStyleValue($key, $value);
			}
		}
		
		if(!is_null($styleTable) && is_array($styleTable)) {
			foreach($styleTable as $key => $value) {
				if(substr($key, 0, 1) != '_') {
					$key = '_'.$key;
				}
				$this->setStyleValue($key, $value);
			}
		}
	}
	
	/**
	 * Set style value
	 * 
	 * @param string $key
	 * @param mixed $value
	 */
	public function setStyleValue($key, $value) {
		if($key == '_borderSize') {
			$this->setBorderSize($value);
		} elseif($key == '_borderColor') {
			$this->setBorderColor($value);
		} elseif($key == '_cellMargin') {
			$this->setCellMargin($value);
		} else {
			$this->$key = $value;
		}
	}
	
	/**
	 * Get First Row Style
	 * 
	 * @return PHPWord_Style_TableFull
	 */
	public function getFirstRow() {
		return $this->_firstRow;
	}
	
	/**
	 * Get Last Row Style
	 * 
	 * @return PHPWord_Style_TableFull
	 */
	public function getLastRow() {
		return $this->_lastRow;
	}
	
	public function getBgColor() {
		return $this->_bgColor;
	}

	public function setBgColor($pValue = null) {
	   $this->_bgColor = $pValue;
	}

	/**
	 * Set TLRBVH Border Size
	 * 
	 * @param int $pValue
	 */
	public function setBorderSize($pValue = null) {
		$this->_borderTopSize = $pValue;
		$this->_borderLeftSize = $pValue;
		$this->_borderRightSize = $pValue;
		$this->_borderBottomSize = $pValue;
		$this->_borderInsideHSize = $pValue;
		$this->_borderInsideVSize = $pValue;
	}
	
	/**
	 * Get TLRBVH Border Size
	 * 
	 * @return array
	 */
	public function getBorderSize() {
		$t = $this->getBorderTopSize();
		$l = $this->getBorderLeftSize();
		$r = $this->getBorderRightSize();
		$b = $this->getBorderBottomSize();
		$h = $this->getBorderInsideHSize();
		$v = $this->getBorderInsideVSize();
		
		return array($t, $l, $r, $b, $h, $v);
	}
	
	/**
	 * Set TLRBVH Border Color
	 */
	public function setBorderColor($pValue = null) {
		$this->_borderTopColor = $pValue;
		$this->_borderLeftColor = $pValue;
		$this->_borderRightColor = $pValue;
		$this->_borderBottomColor = $pValue;
		$this->_borderInsideHColor = $pValue;
		$this->_borderInsideVColor = $pValue;
	}
	
	/**
	 * Get TLRB Border Color
	 * 
	 * @return array
	 */
	public function getBorderColor() {
		$t = $this->getBorderTopColor();
		$l = $this->getBorderLeftColor();
		$r = $this->getBorderRightColor();
		$b = $this->getBorderBottomColor();
		$h = $this->getBorderInsideHColor();
		$v = $this->getBorderInsideVColor();
		
		return array($t, $l, $r, $b, $h, $v);
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
	
	public function setBorderInsideHColor($pValue = null) {
		$this->_borderInsideHColor = $pValue;
	}
	
	public function getBorderInsideHColor() {
		return (isset($this->_borderInsideHColor)) ? $this->_borderInsideHColor : null;
	}
	
	public function setBorderInsideVColor($pValue = null) {
		$this->_borderInsideVColor = $pValue;
	}
	
	public function getBorderInsideVColor() {
		return (isset($this->_borderInsideVColor)) ? $this->_borderInsideVColor : null;
	}
	
	public function setBorderInsideHSize($pValue = null) {
		$this->_borderInsideHSize = $pValue;
	}
	
	public function getBorderInsideHSize() {
		return (isset($this->_borderInsideHSize)) ? $this->_borderInsideHSize : null;
	}
	
	public function setBorderInsideVSize($pValue = null) {
		$this->_borderInsideVSize = $pValue;
	}
	
	public function getBorderInsideVSize() {
		return (isset($this->_borderInsideVSize)) ? $this->_borderInsideVSize : null;
	}
	
	public function setCellMarginTop($pValue = null) {
		$this->_cellMarginTop = $pValue;
	}
	
	public function getCellMarginTop() {
		return $this->_cellMarginTop;
	}
	
	public function setCellMarginLeft($pValue = null) {
		$this->_cellMarginLeft = $pValue;
	}
	
	public function getCellMarginLeft() {
		return $this->_cellMarginLeft;
	}
	
	public function setCellMarginRight($pValue = null) {
		$this->_cellMarginRight = $pValue;
	}
	
	public function getCellMarginRight() {
		return $this->_cellMarginRight;
	}
	
	public function setCellMarginBottom($pValue = null) {
		$this->_cellMarginBottom = $pValue;
	}
	
	public function getCellMarginBottom() {
		return $this->_cellMarginBottom;
	}
	
	public function setCellMargin($pValue = null) {
		$this->_cellMarginTop = $pValue;
		$this->_cellMarginLeft = $pValue;
		$this->_cellMarginRight = $pValue;
		$this->_cellMarginBottom = $pValue;
	}
	
	public function getCellMargin() {
		return array($this->_cellMarginTop, $this->_cellMarginLeft, $this->_cellMarginRight, $this->_cellMarginBottom);
	}
}
?>