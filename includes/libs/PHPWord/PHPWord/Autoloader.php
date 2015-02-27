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

class PHPWord_Autoloader
{
	public static function Register() {
		return spl_autoload_register(array('PHPWord_Autoloader', 'Load'));
	}

	public static function Load($strObjectName) {
		if((class_exists($strObjectName)) || (strpos($strObjectName, 'PHPWord') === false)) {
			return false;
		}

		$strObjectFilePath = PHPWORD_BASE_PATH . str_replace('_', '/', $strObjectName) . '.php';
		
		if((file_exists($strObjectFilePath) === false) || (is_readable($strObjectFilePath) === false)) {
			return false;
		}
		
		require($strObjectFilePath);
	}
}
?>