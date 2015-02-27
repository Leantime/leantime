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
 * PHPWord_Style_TOC
 *
 * @category   PHPWord
 * @package    PHPWord_Style
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Style_TOC {
	
	const TABLEADER_DOT         = 'dot';
	const TABLEADER_UNDERSCORE  = 'underscore';
	const TABLEADER_LINE        = 'hyphen';
	const TABLEADER_NONE        = '';
	
	/**
	 * Tab Leader
	 * 
	 * @var string
	 */
	private $_tabLeader;
	
	/**
	 * Tab Position
	 * 
	 * @var int
	 */
	private $_tabPos;
	
	/**
	 * Indent
	 * 
	 * @var int
	 */
	private $_indent;
	
	
	/**
	 * Create a new TOC Style
	 */
	public function __construct() {
		$this->_tabPos      = 9062;
		$this->_tabLeader   = PHPWord_Style_TOC::TABLEADER_DOT;
		$this->_indent      = 200;
	}
	
	/**
	 * Get Tab Position
	 * 
	 * @return int
	 */
	public function getTabPos() {
		return $this->_tabPos;
	}
	
	/**
	 * Set Tab Position
	 * 
	 * @param int $pValue
	 */
	public function setTabPos($pValue) {
		$this->_tabLeader = $pValue;
	}
	
	/**
	 * Get Tab Leader
	 * 
	 * @return string
	 */
	public function getTabLeader() {
		return $this->_tabLeader;
	}
	
	/**
	 * Set Tab Leader
	 * 
	 * @param string $pValue
	 */
	public function setTabLeader($pValue = PHPWord_Style_TOC::TABLEADER_DOT) {
		$this->_tabLeader = $pValue;
	}
	
	/**
	 * Get Indent
	 * 
	 * @return int
	 */
	public function getIndent() {
		return $this->_indent;
	}
	
	/**
	 * Set Indent
	 * 
	 * @param string $pValue
	 */
	public function setIndent($pValue) {
		$this->_indent = $pValue;
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
}
?>
