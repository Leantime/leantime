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


class PHPWord_Shared_File
{
	/**
	  * Verify if a file exists
	  *
	  * @param 	string	$pFilename	Filename
	  * @return bool
	  */
	public static function file_exists($pFilename) {
		// Sick construction, but it seems that
		// file_exists returns strange values when
		// doing the original file_exists on ZIP archives...
		if ( strtolower(substr($pFilename, 0, 3)) == 'zip' ) {
			// Open ZIP file and verify if the file exists
			$zipFile 		= substr($pFilename, 6, strpos($pFilename, '#') - 6);
			$archiveFile 	= substr($pFilename, strpos($pFilename, '#') + 1);

			$zip = new ZipArchive();
			if ($zip->open($zipFile) === true) {
				$returnValue = ($zip->getFromName($archiveFile) !== false);
				$zip->close();
				return $returnValue;
			} else {
				return false;
			}
		} else {
			// Regular file_exists
			return file_exists($pFilename);
		}
	}

	/**
	 * Returns canonicalized absolute pathname, also for ZIP archives
	 *
	 * @param string $pFilename
	 * @return string
	 */
	public static function realpath($pFilename) {
		// Returnvalue
		$returnValue = '';

		// Try using realpath()
		$returnValue = realpath($pFilename);

		// Found something?
		if ($returnValue == '' || is_null($returnValue)) {
			$pathArray = split('/' , $pFilename);
			while(in_array('..', $pathArray) && $pathArray[0] != '..') {
				for ($i = 0; $i < count($pathArray); ++$i) {
					if ($pathArray[$i] == '..' && $i > 0) {
						unset($pathArray[$i]);
						unset($pathArray[$i - 1]);
						break;
					}
				}
			}
			$returnValue = implode('/', $pathArray);
		}

		// Return
		return $returnValue;
	}
}
