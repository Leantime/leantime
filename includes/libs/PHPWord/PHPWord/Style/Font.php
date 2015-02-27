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
 * PHPWord_Style_Font
 *
 * @category   PHPWord
 * @package    PHPWord_Style
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Style_Font {
	
	const UNDERLINE_NONE		    = 'none';
	const UNDERLINE_DASH		    = 'dash';
	const UNDERLINE_DASHHEAVY		= 'dashHeavy';
	const UNDERLINE_DASHLONG		= 'dashLong';
	const UNDERLINE_DASHLONGHEAVY	= 'dashLongHeavy';
	const UNDERLINE_DOUBLE          = 'dbl';
	const UNDERLINE_DOTHASH		    = 'dotDash';
	const UNDERLINE_DOTHASHHEAVY	= 'dotDashHeavy';
	const UNDERLINE_DOTDOTDASH		= 'dotDotDash';
	const UNDERLINE_DOTDOTDASHHEAVY	= 'dotDotDashHeavy';
	const UNDERLINE_DOTTED		    = 'dotted';
	const UNDERLINE_DOTTEDHEAVY		= 'dottedHeavy';
	const UNDERLINE_HEAVY		    = 'heavy';
	const UNDERLINE_SINGLE		    = 'single';
	const UNDERLINE_WAVY		    = 'wavy';
	const UNDERLINE_WAVYDOUBLE		= 'wavyDbl';
	const UNDERLINE_WAVYHEAVY		= 'wavyHeavy';
	const UNDERLINE_WORDS		    = 'words';
	
	const FGCOLOR_YELLOW            = 'yellow';
	const FGCOLOR_LIGHTGREEN        = 'green';
	const FGCOLOR_CYAN              = 'cyan';
	const FGCOLOR_MAGENTA           = 'magenta';
	const FGCOLOR_BLUE              = 'blue';
	const FGCOLOR_RED               = 'red';
	const FGCOLOR_DARKBLUE          = 'darkBlue';
	const FGCOLOR_DARKCYAN          = 'darkCyan';
	const FGCOLOR_DARKGREEN         = 'darkGreen';
	const FGCOLOR_DARKMAGENTA       = 'darkMagenta';
	const FGCOLOR_DARKRED           = 'darkRed';
	const FGCOLOR_DARKYELLOW        = 'darkYellow';
	const FGCOLOR_DARKGRAY          = 'darkGray';
	const FGCOLOR_LIGHTGRAY         = 'lightGray';
	const FGCOLOR_BLACK             = 'black';
	
	/**
	 * Font style type
	 * 
	 * @var string
	 */
	private $_type;
	
	/**
	 * Paragraph Style
	 * 
	 * @var PHPWord_Style_Paragraph
	 */
	private $_paragraphStyle;
	
	private $_size;
	private $_name;
	private $_bold;
	private $_italic;
	private $_superScript;
	private $_subScript;
	private $_underline;
	private $_strikethrough;
	private $_color;
	private $_fgColor;

	public function __construct($type = 'text', $styleParagraph = null) {
		$this->_type            = $type;
		$this->_name            = 'Arial';
		$this->_size            = 20;
		$this->_bold		    = false;
		$this->_italic		    = false;
		$this->_superScript	    = false;
		$this->_subScript	    = false;
		$this->_underline	    = PHPWord_Style_Font::UNDERLINE_NONE;
		$this->_strikethrough   = false;
		$this->_color           = '000000';
		$this->_fgColor         = null;
		
		if(!is_null($styleParagraph)) {
			$paragraph = new PHPWord_Style_Paragraph();
			foreach($styleParagraph as $key => $value) {
				if(substr($key, 0, 1) != '_') {
					$key = '_'.$key;
				}
				$paragraph->setStyleValue($key, $value);
			}
			$this->_paragraphStyle = $paragraph;
		} else {
			$this->_paragraphStyle = null;
		}
	}

	public function getName() {
		return $this->_name;
	}
	
	public function setStyleValue($key, $value) {
		if($key == '_size') {
			$value *= 2;
		}
		$this->$key = $value;
	}
	
	public function setName($pValue = 'Arial') {
		if($pValue == '') {
			$pValue = 'Arial';
		}
		$this->_name = $pValue;
		return $this;
	}

	public function getSize() {
		return $this->_size;
	}

	public function setSize($pValue = 20) {
		if($pValue == '') {
			$pValue = 20;
		}
		$this->_size = ($pValue*2);
		return $this;
	}

	public function getBold() {
		return $this->_bold;
	}

	public function setBold($pValue = false) {
		if($pValue == '') {
			$pValue = false;
		}
		$this->_bold = $pValue;
		return $this;
	}

	public function getItalic() {
		return $this->_italic;
	}

	public function setItalic($pValue = false) {
		if($pValue == '') {
			$pValue = false;
		}
		$this->_italic = $pValue;
		return $this;
	}

	public function getSuperScript() {
		return $this->_superScript;
	}

	public function setSuperScript($pValue = false) {
		if($pValue == '') {
			$pValue = false;
		}
		$this->_superScript = $pValue;
		$this->_subScript = !$pValue;
		return $this;
	}

	public function getSubScript() {
		return $this->_subScript;
	}

	public function setSubScript($pValue = false) {
		if($pValue == '') {
			$pValue = false;
		}
		$this->_subScript = $pValue;
		$this->_superScript = !$pValue;
		return $this;
	}

	public function getUnderline() {
		return $this->_underline;
	}

	public function setUnderline($pValue = PHPWord_Style_Font::UNDERLINE_NONE) {
		if ($pValue == '') {
			$pValue = PHPWord_Style_Font::UNDERLINE_NONE;
		}
		$this->_underline = $pValue;
		return $this;
	}

	public function getStrikethrough() {
		return $this->_strikethrough;
	}

	public function setStrikethrough($pValue = false) {
		if($pValue == '') {
			$pValue = false;
		}
		$this->_strikethrough = $pValue;
		return $this;
	}
	
	public function getColor() {
		return $this->_color;
	}

	public function setColor($pValue = '000000') {
	   $this->_color = $pValue;
	   return $this;
	}

	public function getFgColor() {
		return $this->_fgColor;
	}

	public function setFgColor($pValue = null) {
	   $this->_fgColor = $pValue;
	   return $this;
	}
	
	public function getStyleType() {
		return $this->_type;
	}
	
	/**
	 * Get Paragraph style
	 * 
	 * @return PHPWord_Style_Paragraph
	 */
	public function getParagraphStyle() {
		return $this->_paragraphStyle;
	}
}
?>
