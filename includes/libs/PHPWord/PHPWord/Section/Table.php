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
 * PHPWord_Section_Table
 *
 * @category   PHPWord
 * @package    PHPWord_Section
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Section_Table {
	
	/**
	 * Table style
	 *
	 * @var PHPWord_Style_Table
	 */
	private $_style;
	
	/**
	 * Table rows
	 *
	 * @var array
	 */
	private $_rows = array();
	
	/**
	 * Row heights
	 *
	 * @var array
	 */
	private $_rowHeights = array();
	
	/**
	 * Table holder
	 *
	 * @var string
	 */
	private $_insideOf = null;
	
	/**
	 * Table holder count
	 *
	 * @var array
	 */
	private $_pCount;
	
	
	/**
	 * Create a new table
	 * 
	 * @param string $insideOf
	 * @param int $pCount
	 * @param mixed $style
	 */
	public function __construct($insideOf, $pCount, $style = null) {
		$this->_insideOf = $insideOf;
		$this->_pCount = $pCount;
		
		if(!is_null($style)) {
			if(is_array($style)) {
				$this->_style = new PHPWord_Style_Table();
				
				foreach($style as $key => $value) {
					if(substr($key, 0, 1) != '_') {
						$key = '_'.$key;
					}
					$this->_style->setStyleValue($key, $value);
				}
			} else {
				$this->_style = $style;
			}
		}
	}
	
	/**
	* Add a row
	*
	* @param int $height
	*/
	public function addRow($height = null) {
		$this->_rows[] = array();
		$this->_rowHeights[] = $height;
	}
	
	/**
	* Add a cell
	*
	* @param int $width
	* @param mixed $style
	* @return PHPWord_Section_Table_Cell
	*/
	public function addCell($width, $style = null) {
		$cell = new PHPWord_Section_Table_Cell($this->_insideOf, $this->_pCount, $width, $style);
		$i = count($this->_rows) - 1;
		$this->_rows[$i][] = $cell;
		return $cell;
	}
	
	/**
	 * Get all rows
	 * 
	 * @return array
	 */
	public function getRows() {
		return $this->_rows;
	}
	
	/**
	 * Get all row heights
	 * 
	 * @return array
	 */
	public function getRowHeights() {
		return $this->_rowHeights;
	}
	
	/**
	 * Get table style
	 * 
	 * @return PHPWord_Style_Table
	 */
	public function getStyle() {
		return $this->_style;
	}
}
?>