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
 * PHPWord_DocumentProperties
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 2009 - 2011 PHPWord (http://www.codeplex.com/PHPWord)
 */
class PHPWord_DocumentProperties {

	/**
	 * Creator
	 *
	 * @var string
	 */
	private $_creator;
	
	/**
	 * LastModifiedBy
	 *
	 * @var string
	 */
	private $_lastModifiedBy;
	
	/**
	 * Created
	 *
	 * @var datetime
	 */
	private $_created;
	
	/**
	 * Modified
	 *
	 * @var datetime
	 */
	private $_modified;
	
	/**
	 * Title
	 *
	 * @var string
	 */
	private $_title;
	
	/**
	 * Description
	 *
	 * @var string
	 */
	private $_description;
	
	/**
	 * Subject
	 *
	 * @var string
	 */
	private $_subject;
	
	/**
	 * Keywords
	 *
	 * @var string
	 */
	private $_keywords;
	
	/**
	 * Category
	 *
	 * @var string
	 */
	private $_category;
	
	/**
	 * Company
	 * 
	 * @var string
	 */
	private $_company;

	/**
	 * Create new PHPWord_DocumentProperties
	 */
	public function __construct() {
		$this->_creator 		= '';
		$this->_lastModifiedBy  = $this->_creator;
		$this->_created 		= time();
		$this->_modified 		= time();
		$this->_title			= '';
		$this->_subject			= '';
		$this->_description		= '';
		$this->_keywords		= '';
		$this->_category		= '';
		$this->_company 		= '';
	}

	/**
	 * Get Creator
	 *
	 * @return string
	 */
	public function getCreator() {
		return $this->_creator;
	}
	
	/**
	 * Set Creator
	 *
	 * @param string $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setCreator($pValue = '') {
		$this->_creator = $pValue;
		return $this;
	}
	
	/**
	 * Get Last Modified By
	 *
	 * @return string
	 */
	public function getLastModifiedBy() {
		return $this->_lastModifiedBy;
	}
	
	/**
	 * Set Last Modified By
	 *
	 * @param string $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setLastModifiedBy($pValue = '') {
		$this->_lastModifiedBy = $pValue;
		return $this;
	}
	
	/**
	 * Get Created
	 *
	 * @return datetime
	 */
	public function getCreated() {
		return $this->_created;
	}
	
	/**
	 * Set Created
	 *
	 * @param datetime $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setCreated($pValue = null) {
		if (is_null($pValue)) {
			$pValue = time();
		}
		$this->_created = $pValue;
		return $this;
	}
	
	/**
	 * Get Modified
	 *
	 * @return datetime
	 */
	public function getModified() {
		return $this->_modified;
	}
	
	/**
	 * Set Modified
	 *
	 * @param datetime $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setModified($pValue = null) {
		if (is_null($pValue)) {
			$pValue = time();
		}
		$this->_modified = $pValue;
		return $this;
	}
	
	/**
	 * Get Title
	 *
	 * @return string
	 */
	public function getTitle() {
		return $this->_title;
	}
	
	/**
	 * Set Title
	 *
	 * @param string $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setTitle($pValue = '') {
		$this->_title = $pValue;
		return $this;
	}
	
	/**
	 * Get Description
	 *
	 * @return string
	 */
	public function getDescription() {
		return $this->_description;
	}
	
	/**
	 * Set Description
	 *
	 * @param string $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setDescription($pValue = '') {
		$this->_description = $pValue;
		return $this;
	}
	
	/**
	 * Get Subject
	 *
	 * @return string
	 */
	public function getSubject() {
		return $this->_subject;
	}
	
	/**
	 * Set Subject
	 *
	 * @param string $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setSubject($pValue = '') {
		$this->_subject = $pValue;
		return $this;
	}
	
	/**
	 * Get Keywords
	 *
	 * @return string
	 */
	public function getKeywords() {
		return $this->_keywords;
	}
	
	/**
	 * Set Keywords
	 *
	 * @param string $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setKeywords($pValue = '') {
		$this->_keywords = $pValue;
		return $this;
	}
	
	/**
	 * Get Category
	 *
	 * @return string
	 */
	public function getCategory() {
		return $this->_category;
	}
	
	/**
	 * Set Category
	 *
	 * @param string $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setCategory($pValue = '') {
		$this->_category = $pValue;
		return $this;
	}
	
	/**
	 * Get Company
	 *
	 * @return string
	 */
	public function getCompany() {
		return $this->_company;
	}
	
	/**
	 * Set Company
	 *
	 * @param string $pValue
	 * @return PHPWord_DocumentProperties
	 */
	public function setCompany($pValue = '') {
		$this->_company = $pValue;
		return $this;
	}
}
?>
