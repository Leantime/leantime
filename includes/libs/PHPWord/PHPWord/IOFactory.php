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
 * PHPWord_IOFactory
 *
 * @category   PHPWord
 * @package    PHPWord
 * @copyright  Copyright (c) 2011 PHPWord
 */
class PHPWord_IOFactory {    
	
	/**
	 * Search locations
	 *
	 * @var array
	 */
	private static $_searchLocations = array(
		array('type' => 'IWriter', 'path' => 'PHPWord/Writer/{0}.php', 'class' => 'PHPWord_Writer_{0}')
	);
	
	/**
	 * Autoresolve classes
	 * 
	 * @var array
	 */
	private static $_autoResolveClasses = array(
		'Serialized'
	);
	
	/**
	 * Private constructor for PHPWord_IOFactory
	 */
	private function __construct() { }
	
	/**
	 * Get search locations
	 *
	 * @return array
	 */
	public static function getSearchLocations() {
		return self::$_searchLocations;
	}
	
	/**
	 * Set search locations
	 * 
	 * @param array $value
	 * @throws Exception
	 */
	public static function setSearchLocations($value) {
		if (is_array($value)) {
			self::$_searchLocations = $value;
		} else {
			throw new Exception('Invalid parameter passed.');
		}
	}
	
	/**
	 * Add search location
	 * 
	 * @param string $type            Example: IWriter
	 * @param string $location        Example: PHPWord/Writer/{0}.php
	 * @param string $classname     Example: PHPWord_Writer_{0}
	 */
	public static function addSearchLocation($type = '', $location = '', $classname = '') {
		self::$_searchLocations[] = array( 'type' => $type, 'path' => $location, 'class' => $classname );
	}
	
	/**
	 * Create PHPWord_Writer_IWriter
	 *
	 * @param PHPWord $PHPWord
	 * @param string  $writerType    Example: Word2007
	 * @return PHPWord_Writer_IWriter
	 */
	public static function createWriter(PHPWord $PHPWord, $writerType = '') {
		$searchType = 'IWriter';
		
		foreach (self::$_searchLocations as $searchLocation) {
			if ($searchLocation['type'] == $searchType) {
				$className = str_replace('{0}', $writerType, $searchLocation['class']);
				$classFile = str_replace('{0}', $writerType, $searchLocation['path']);
				
				$instance = new $className($PHPWord);
				if(!is_null($instance)) {
					return $instance;
				}
			}
		}
		
		throw new Exception("No $searchType found for type $writerType");
	}
}
?>