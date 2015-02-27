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


class PHPWord_Shared_String
{
	/**
	 * Control characters array
	 *
	 * @var string[]
	 */
	private static $_controlCharacters = array();

	/**
	 * Is mbstring extension avalable?
	 *
	 * @var boolean
	 */
	private static $_isMbstringEnabled;

	/**
	 * Is iconv extension avalable?
	 *
	 * @var boolean
	 */
	private static $_isIconvEnabled;

	/**
	 * Build control characters array
	 */
	private static function _buildControlCharacters() {
		for ($i = 0; $i <= 19; ++$i) {
			if ($i != 9 && $i != 10 && $i != 13) {
				$find = '_x' . sprintf('%04s' , strtoupper(dechex($i))) . '_';
				$replace = chr($i);
				self::$_controlCharacters[$find] = $replace;
			}
		}
	}

	/**
	 * Get whether mbstring extension is available
	 *
	 * @return boolean
	 */
	public static function getIsMbstringEnabled()
	{
		if (isset(self::$_isMbstringEnabled)) {
			return self::$_isMbstringEnabled;
		}

		self::$_isMbstringEnabled = function_exists('mb_convert_encoding') ?
			true : false;

		return self::$_isMbstringEnabled;
	}

	/**
	 * Get whether iconv extension is available
	 *
	 * @return boolean
	 */
	public static function getIsIconvEnabled()
	{
		if (isset(self::$_isIconvEnabled)) {
			return self::$_isIconvEnabled;
		}

		self::$_isIconvEnabled = function_exists('iconv') ?
			true : false;

		return self::$_isIconvEnabled;
	}

	/**
	 * Convert from OpenXML escaped control character to PHP control character
	 *
	 * Excel 2007 team:
	 * ----------------
	 * That's correct, control characters are stored directly in the shared-strings table.
	 * We do encode characters that cannot be represented in XML using the following escape sequence:
	 * _xHHHH_ where H represents a hexadecimal character in the character's value...
	 * So you could end up with something like _x0008_ in a string (either in a cell value (<v>)
	 * element or in the shared string <t> element.
	 *
	 * @param 	string	$value	Value to unescape
	 * @return 	string
	 */
	public static function ControlCharacterOOXML2PHP($value = '') {
		if(empty(self::$_controlCharacters)) {
			self::_buildControlCharacters();
		}

		return str_replace( array_keys(self::$_controlCharacters), array_values(self::$_controlCharacters), $value );
	}

	/**
	 * Convert from PHP control character to OpenXML escaped control character
	 *
	 * Excel 2007 team:
	 * ----------------
	 * That's correct, control characters are stored directly in the shared-strings table.
	 * We do encode characters that cannot be represented in XML using the following escape sequence:
	 * _xHHHH_ where H represents a hexadecimal character in the character's value...
	 * So you could end up with something like _x0008_ in a string (either in a cell value (<v>)
	 * element or in the shared string <t> element.
	 *
	 * @param 	string	$value	Value to escape
	 * @return 	string
	 */
	public static function ControlCharacterPHP2OOXML($value = '') {
		if(empty(self::$_controlCharacters)) {
			self::_buildControlCharacters();
		}

		return str_replace( array_values(self::$_controlCharacters), array_keys(self::$_controlCharacters), $value );
	}

	/**
	 * Check if a string contains UTF8 data
	 *
	 * @param string $value
	 * @return boolean
	 */
	public static function IsUTF8($value = '') {
		return utf8_encode(utf8_decode($value)) === $value;
	}

	/**
	 * Formats a numeric value as a string for output in various output writers
	 *
	 * @param mixed $value
	 * @return string
	 */
	public static function FormatNumber($value) {
		return number_format($value, 2, '.', '');
	}

	/**
	 * Converts a UTF-8 string into BIFF8 Unicode string data (8-bit string length)
	 * Writes the string using uncompressed notation, no rich text, no Asian phonetics
	 * If mbstring extension is not available, ASCII is assumed, and compressed notation is used
	 * although this will give wrong results for non-ASCII strings
	 * see OpenOffice.org's Documentation of the Microsoft Excel File Format, sect. 2.5.3
	 *
	 * @param string $value UTF-8 encoded string
	 * @return string
	 */
	public static function UTF8toBIFF8UnicodeShort($value)
	{
		// character count
		$ln = self::CountCharacters($value, 'UTF-8');

		// option flags
		$opt = (self::getIsMbstringEnabled() || self::getIsIconvEnabled()) ? 
			0x0001 : 0x0000;

		// characters
		$chars = self::ConvertEncoding($value, 'UTF-16LE', 'UTF-8');

		$data = pack('CC', $ln, $opt) . $chars;
		return $data;
	}

	/**
	 * Converts a UTF-8 string into BIFF8 Unicode string data (16-bit string length)
	 * Writes the string using uncompressed notation, no rich text, no Asian phonetics
	 * If mbstring extension is not available, ASCII is assumed, and compressed notation is used
	 * although this will give wrong results for non-ASCII strings
	 * see OpenOffice.org's Documentation of the Microsoft Excel File Format, sect. 2.5.3
	 *
	 * @param string $value UTF-8 encoded string
	 * @return string
	 */
	public static function UTF8toBIFF8UnicodeLong($value)
	{
		// character count
		$ln = self::CountCharacters($value, 'UTF-8');

		// option flags
		$opt = (self::getIsMbstringEnabled() || self::getIsIconvEnabled()) ? 
			0x0001 : 0x0000;

		// characters
		$chars = self::ConvertEncoding($value, 'UTF-16LE', 'UTF-8');

		$data = pack('vC', $ln, $opt) . $chars;
		return $data;
	}

	/**
	 * Convert string from one encoding to another. First try mbstring, then iconv, or no convertion
	 *
	 * @param string $value
	 * @param string $to Encoding to convert to, e.g. 'UTF-8'
	 * @param string $from Encoding to convert from, e.g. 'UTF-16LE'
	 * @return string
	 */
	public static function ConvertEncoding($value, $to, $from)
	{
		if (self::getIsMbstringEnabled()) {
			$value = mb_convert_encoding($value, $to, $from);
			return $value;
		}

		if (self::getIsIconvEnabled()) {
			$value = iconv($from, $to, $value);
			return $value;
		}

		// else, no conversion
		return $value;
	}
	
	/**
	 * Get character count. First try mbstring, then iconv, finally strlen
	 *
	 * @param string $value
	 * @param string $enc Encoding
	 * @return int Character count
	 */
	public static function CountCharacters($value, $enc = 'UTF-8')
	{
		if (self::getIsMbstringEnabled()) {
			$count = mb_strlen($value, $enc);
			return $count;
		}

		if (self::getIsIconvEnabled()) {
			$count = iconv_strlen($value, $enc);
			return $count;
		}

		// else strlen
		$count = strlen($value);
		return $count;
	}

}
