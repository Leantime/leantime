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
 * PHPWord_Style_ListItem
 *
 * @category   PHPWord
 * @package    PHPWord_Style
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Style_ListItem {
	
	const TYPE_NUMBER           = 7;
	const TYPE_NUMBER_NESTED    = 8;
	const TYPE_BULLET_FILLED    = 3;
	const TYPE_BULLET_EMPTY     = 5;
	const TYPE_SQUARE_FILLED    = 1;
	
	/**
	 * List Type
	 */
	private $_listType;
	
	/**
	 * Create a new ListItem Style
	 */
	public function __construct() {
		$this->_listType = PHPWord_Style_ListItem::TYPE_BULLET_FILLED;
	}
	
	/**
	 * Set style value
	 * 
	 * @param string $key
	 * @param string $value
	 */
	public function setStyleValue($key, $value) {
		$this->$key = $value;
	}
	
	/**
	 * Set List Type
	 * 
	 * @param int $pValue
	 */
	public function setListType($pValue = PHPWord_Style_ListItem::TYPE_BULLET_FILLED) {
		$this->_listType = $pValue;
	}
	
	/**
	 * Get List Type
	 */
	public function getListType() {
		return $this->_listType;
	}
}
?>
