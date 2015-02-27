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
 * PHPWord_Style
 *
 * @category   PHPWord
 * @package    PHPWord_Style
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_Style {
	
	/**
	 * Style Elements
	 *
	 * @var array
	 */
	private static $_styleElements = array();
	
	
	/**
	 * Add a paragraph style
	 * 
	 * @param string $styleName
	 * @param array $styles
	 */
	public static function addParagraphStyle($styleName, $styles) {
		if(!array_key_exists($styleName, self::$_styleElements)) {
			$style = new PHPWord_Style_Paragraph();
			foreach($styles as $key => $value) {
				if(substr($key, 0, 1) != '_') {
					$key = '_'.$key;
				}
				$style->setStyleValue($key, $value);
			}
			
			self::$_styleElements[$styleName] = $style;
		}
	}
	
	/**
	 * Add a font style
	 * 
	 * @param string $styleName
	 * @param array $styleFont
	 * @param array $styleParagraph
	 */
	public static function addFontStyle($styleName, $styleFont, $styleParagraph = null) {
		if(!array_key_exists($styleName, self::$_styleElements)) {
			$font = new PHPWord_Style_Font('text', $styleParagraph);
			foreach($styleFont as $key => $value) {
				if(substr($key, 0, 1) != '_') {
					$key = '_'.$key;
				}
				$font->setStyleValue($key, $value);
			}
			self::$_styleElements[$styleName] = $font;
		}
	}
	
	/**
	 * Add a link style
	 * 
	 * @param string $styleName
	 * @param array $styles
	 */
	public static function addLinkStyle($styleName, $styles) {
		if(!array_key_exists($styleName, self::$_styleElements)) {
			$style = new PHPWord_Style_Font('link');
			foreach($styles as $key => $value) {
				if(substr($key, 0, 1) != '_') {
					$key = '_'.$key;
				}
				$style->setStyleValue($key, $value);
			}
			
			self::$_styleElements[$styleName] = $style;
		}
	}
	
	/**
	 * Add a table style
	 * 
	 * @param string $styleName
	 * @param array $styles
	 */
	public static function addTableStyle($styleName, $styleTable, $styleFirstRow = null, $styleLastRow = null) {
		if(!array_key_exists($styleName, self::$_styleElements)) {
			$style = new PHPWord_Style_TableFull($styleTable, $styleFirstRow, $styleLastRow);
			
			self::$_styleElements[$styleName] = $style;
		}
	}
	
	/**
	 * Add a title style
	 * 
	 * @param string $styleName
	 * @param array $styleFont
	 * @param array $styleParagraph
	 */
	public static function addTitleStyle($titleCount, $styleFont, $styleParagraph = null) {
		$styleName = 'Heading_'.$titleCount;
		if(!array_key_exists($styleName, self::$_styleElements)) {
			$font = new PHPWord_Style_Font('title', $styleParagraph);
			foreach($styleFont as $key => $value) {
				if(substr($key, 0, 1) != '_') {
					$key = '_'.$key;
				}
				$font->setStyleValue($key, $value);
			}
			
			self::$_styleElements[$styleName] = $font;
		}
	}
	
	/**
	 * Get all styles
	 * 
	 * @return PHPWord_Style_Font[]
	 */
	public static function getStyles() {
		return self::$_styleElements;
	}
}
?>
