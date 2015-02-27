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
 * PHPWord_Section_Title
 *
 * @category   PHPWord
 * @package    PHPWord_Section
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Section_Title {
	
	/**
	 * Title Text content
	 * 
	 * @var string
	 */
	private $_text;
	
	/**
	 * Title depth
	 * 
	 * @var int
	 */
	private $_depth;
	
	/**
	 * Title anchor
	 * 
	 * @var int
	 */
	private $_anchor;
	
	/**
	 * Title Bookmark ID
	 * 
	 * @var int
	 */
	private $_bookmarkId;
	
	/**
	 * Title style
	 * 
	 * @var string
	 */
	private $_style;
	
	
	/**
	 * Create a new Title Element
	 * 
	 * @var string $text
	 * @var int $depth
	 */
	public function __construct($text, $depth = 1, $style = null) {
		if(!is_null($style)) {
			$this->_style = $style;
		}
		
		$this->_text = $text;
		$this->_depth = $depth;
		
		return $this;
	}
	
	/**
	 * Set Anchor
	 * 
	 * @var int $anchor
	 */
	public function setAnchor($anchor) {
		$this->_anchor = $anchor;
	}
	
	/**
	 * Get Anchor
	 * 
	 * @return int
	 */
	public function getAnchor() {
		return $this->_anchor;
	}
	
	/**
	 * Set Bookmark ID
	 * 
	 * @var int $bookmarkId
	 */
	public function setBookmarkId($bookmarkId) {
		$this->_bookmarkId = $bookmarkId;
	}
	
	/**
	 * Get Anchor
	 * 
	 * @return int
	 */
	public function getBookmarkId() {
		return $this->_bookmarkId;
	}
	
	/**
	 * Get Title Text content
	 * 
	 * @return string
	 */
	public function getText() {
		return $this->_text;
	}
	
	/**
	 * Get Title style
	 * 
	 * @return string
	 */
	public function getStyle() {
		return $this->_style;
	}
}
?>