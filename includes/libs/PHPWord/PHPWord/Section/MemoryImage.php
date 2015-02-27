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
 * PHPWord_Section_MemoryImage
 *
 * @category   PHPWord
 * @package    PHPWord_Section
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Section_MemoryImage {
	
	/**
	 * Image Src
	 * 
	 * @var string
	 */
	private $_src;
	
	/**
	 * Image Style
	 * 
	 * @var PHPWord_Style_Image
	 */
	private $_style;
	
	/**
	 * Image Relation ID
	 * 
	 * @var string
	 */
	private $_rId;
	
	/**
	 * Image Type
	 * 
	 * @var string
	 */
	private $_imageType;
	
	/**
	 * Image Create function
	 * 
	 * @var string
	 */
	private $_imageCreateFunc;
	
	/**
	 * Image function
	 * 
	 * @var string
	 */
	private $_imageFunc;
	
	/**
	 * Image function
	 * 
	 * @var string
	 */
	private $_imageExtension;
	
	
	/**
	 * Create a new Image
	 * 
	 * @param string $src
	 * @param mixed style
	 */
	public function __construct($src, $style = null) {
		$imgData = getimagesize($src);
		$this->_imageType = $imgData['mime'];
		
		$_supportedImageTypes = array('image/jpeg', 'image/gif', 'image/png');
		
		if(in_array($this->_imageType, $_supportedImageTypes)) {
			$this->_src = $src;
			$this->_style = new PHPWord_Style_Image();
			
			if(!is_null($style) && is_array($style)) {
				foreach($style as $key => $value) {
					if(substr($key, 0, 1) != '_') {
						$key = '_'.$key;
					}
					$this->_style->setStyleValue($key, $value);
				}
			}
			
			if($this->_style->getWidth() == null && $this->_style->getHeight() == null) {
				$this->_style->setWidth($imgData[0]);
				$this->_style->setHeight($imgData[1]);
			}
			
			$this->_setFunctions();
			
			return $this;
		} else {
			return false;
		}
	}
	
	/**
	 * Set Functions
	 */
	private function _setFunctions() {
		switch($this->_imageType) {
			case 'image/png':
				$this->_imageCreateFunc = 'imagecreatefrompng';
				$this->_imageFunc = 'imagepng';
				$this->_imageExtension = 'png';
				break;
			case 'image/gif':
				$this->_imageCreateFunc = 'imagecreatefromgif';
				$this->_imageFunc = 'imagegif';
				$this->_imageExtension = 'gif';
				break;
			case 'image/jpeg': case 'image/jpg':
				$this->_imageCreateFunc = 'imagecreatefromjpeg';
				$this->_imageFunc = 'imagejpeg';
				$this->_imageExtension = 'jpg';
				break;
		}
	}
	
	
	/**
	 * Get Image style
	 * 
	 * @return PHPWord_Style_Image
	 */
	public function getStyle() {
		return $this->_style;
	}
	
	/**
	 * Get Image Relation ID
	 * 
	 * @return int
	 */
	public function getRelationId() {
		return $this->_rId;
	}
	
	/**
	 * Set Image Relation ID
	 * 
	 * @param int $rId
	 */
	public function setRelationId($rId) {
		$this->_rId = $rId;
	}
	
	/**
	 * Get Image Source
	 * 
	 * @return string
	 */
	public function getSource() {
		return $this->_src;
	}
	
	/**
	 * Get Image Media ID
	 * 
	 * @return string
	 */
	public function getMediaId() {
		return md5($this->_src);
	}
	
	/**
	 * Get Image Type
	 * 
	 * @return string
	 */
	public function getImageType() {
		return $this->_imageType;
	}
	
	/**
	 * Get Image Create Function
	 * 
	 * @return string
	 */
	public function getImageCreateFunction() {
		return $this->_imageCreateFunc;
	}
	
	/**
	 * Get Image Function
	 * 
	 * @return string
	 */
	public function getImageFunction() {
		return $this->_imageFunc;
	}
	
	/**
	 * Get Image Extension
	 * 
	 * @return string
	 */
	public function getImageExtension() {
		return $this->_imageExtension;
	}
}
?>