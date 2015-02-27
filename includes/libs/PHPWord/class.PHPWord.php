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

/** PHPWORD_BASE_PATH */
if(!defined('PHPWORD_BASE_PATH')) {
    define('PHPWORD_BASE_PATH', dirname(__FILE__) . '/');
    require PHPWORD_BASE_PATH . 'PHPWord/Autoloader.php';
    PHPWord_Autoloader::Register();
}


/**
 * PHPWord
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord {
	
	/**
	 * Document properties
	 *
	 * @var PHPWord_DocumentProperties
	 */
	private $_properties;
	
	/**
	 * Default Font Name
	 *
	 * @var string
	 */
	private $_defaultFontName;
	
	/**
	 * Default Font Size
	 *
	 * @var int
	 */
	private $_defaultFontSize;
	
	/**
	 * Collection of section elements
	 *
	 * @var array
	 */
	private $_sectionCollection = array();

	
	/**
	 * Create a new PHPWord Document
	 */
	public function __construct() {
		$this->_properties = new PHPWord_DocumentProperties();
		$this->_defaultFontName = 'Arial';
		$this->_defaultFontSize = 20;
	}

	/**
	 * Get properties
	 * @return PHPWord_DocumentProperties
	 */
	public function getProperties() {
		return $this->_properties;
	}
	
	/**
	 * Set properties
	 *
	 * @param PHPWord_DocumentProperties $value
	 * @return PHPWord
	 */
	public function setProperties(PHPWord_DocumentProperties $value) {
		$this->_properties = $value;
		return $this;
	}
	
	/**
	 * Create a new Section
	 * 
	 * @param PHPWord_Section_Settings $settings
	 * @return PHPWord_Section
	 */
	public function createSection($settings = null) {
		$sectionCount = $this->_countSections() + 1;
		
		$section = new PHPWord_Section($sectionCount, $settings);
		$this->_sectionCollection[] = $section;
		return $section;
	}
	
	/**
	 * Get default Font name
	 * @return string
	 */
	public function getDefaultFontName() {
		return $this->_defaultFontName;
	}
	
	/**
	 * Set default Font name
	 * @param string $pValue
	 */
	public function setDefaultFontName($pValue) {
		$this->_defaultFontName = $pValue;
	}
	
	/**
	 * Get default Font size
	 * @return string
	 */
	public function getDefaultFontSize() {
		return $this->_defaultFontSize;
	}
	
	/**
	 * Set default Font size
	 * @param int $pValue
	 */
	public function setDefaultFontSize($pValue) {
		$pValue = $pValue * 2;
		$this->_defaultFontSize = $pValue;
	}
	
	/**
	 * Adds a paragraph style definition to styles.xml
	 * 
	 * @param $styleName string
	 * @param $styles array
	 */
	public function addParagraphStyle($styleName, $styles) {
		PHPWord_Style::addParagraphStyle($styleName, $styles);
	}
	
	/**
	 * Adds a font style definition to styles.xml
	 * 
	 * @param $styleName string
	 * @param $styles array
	 */
	public function addFontStyle($styleName, $styleFont, $styleParagraph = null) {
		PHPWord_Style::addFontStyle($styleName, $styleFont, $styleParagraph);
	}
	
	/**
	 * Adds a table style definition to styles.xml
	 * 
	 * @param $styleName string
	 * @param $styles array
	 */
	public function addTableStyle($styleName, $styleTable, $styleFirstRow = null) {
		PHPWord_Style::addTableStyle($styleName, $styleTable, $styleFirstRow);
	}
	
	/**
	 * Adds a heading style definition to styles.xml
	 * 
	 * @param $titleCount int
	 * @param $styles array
	 */
	public function addTitleStyle($titleCount, $styleFont, $styleParagraph = null) {
		PHPWord_Style::addTitleStyle($titleCount, $styleFont, $styleParagraph);
	}
	
	/**
	 * Adds a hyperlink style to styles.xml
	 * 
	 * @param $styleName string
	 * @param $styles array
	 */
	public function addLinkStyle($styleName, $styles) {
		PHPWord_Style::addLinkStyle($styleName, $styles);
	}
	
	/**
	 * Get sections
	 * @return PHPWord_Section[]
	 */
	public function getSections() {
		return $this->_sectionCollection;
	}
	
	/**
	 * Get section count
	 * @return int
	 */
	private function _countSections() {
		return count($this->_sectionCollection);
	}
    
    /**
     * Load a Template File
     * 
     * @param string $strFilename
     * @return PHPWord_Template
     */
    public function loadTemplate($strFilename) {
        if(file_exists($strFilename)) {
            $template = new PHPWord_Template($strFilename);
            return $template;
        } else {
            trigger_error('Template file '.$strFilename.' not found.', E_ERROR);
        }
    }
}
?>