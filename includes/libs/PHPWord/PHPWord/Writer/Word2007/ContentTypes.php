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


class PHPWord_Writer_Word2007_ContentTypes extends PHPWord_Writer_Word2007_WriterPart {
	
	public function writeContentTypes($_imageTypes, $_objectTypes, $_cHdrs, $_cFtrs) {
		// Create XML writer
		$objWriter = null;
		if ($this->getParentWriter()->getUseDiskCaching()) {
			$objWriter = new PHPWord_Shared_XMLWriter(PHPWord_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
		} else {
			$objWriter = new PHPWord_Shared_XMLWriter(PHPWord_Shared_XMLWriter::STORAGE_MEMORY);
		}

		// XML header
		$objWriter->startDocument('1.0', 'UTF-8', 'yes');

		// Types
		$objWriter->startElement('Types');
		$objWriter->writeAttribute('xmlns', 'http://schemas.openxmlformats.org/package/2006/content-types');

			// Rels
			$this->_writeDefaultContentType(
				$objWriter, 'rels', 'application/vnd.openxmlformats-package.relationships+xml'
			);

			// XML
			$this->_writeDefaultContentType(
				$objWriter, 'xml', 'application/xml'
			);
			
			// Add media content-types
			foreach($_imageTypes as $key => $value) {
				$this->_writeDefaultContentType($objWriter, $key, $value);
			}
			
			// Add embedding content-types
			if(count($_objectTypes) > 0) {
				$this->_writeDefaultContentType($objWriter, 'bin', 'application/vnd.openxmlformats-officedocument.oleObject');
			}
			
			// DocProps
			$this->_writeOverrideContentType(
				$objWriter, '/docProps/app.xml', 'application/vnd.openxmlformats-officedocument.extended-properties+xml'
			);

			$this->_writeOverrideContentType(
				$objWriter, '/docProps/core.xml', 'application/vnd.openxmlformats-package.core-properties+xml'
			);
			
			// Document
			$this->_writeOverrideContentType(
				$objWriter, '/word/document.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document.main+xml'
			);
			
			// Styles
			$this->_writeOverrideContentType(
				$objWriter, '/word/styles.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.styles+xml'
			);
			
			// Numbering
			$this->_writeOverrideContentType(
				$objWriter, '/word/numbering.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.numbering+xml'
			);
			
			// Settings
			$this->_writeOverrideContentType(
				$objWriter, '/word/settings.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.settings+xml'
			);
			
			// Theme1
			$this->_writeOverrideContentType(
				$objWriter, '/word/theme/theme1.xml', 'application/vnd.openxmlformats-officedocument.theme+xml'
			);
			
			// WebSettings
			$this->_writeOverrideContentType(
				$objWriter, '/word/webSettings.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.webSettings+xml'
			);
			
			// Font Table
			$this->_writeOverrideContentType(
				$objWriter, '/word/fontTable.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.fontTable+xml'
			);

			for($i=1; $i<=$_cHdrs; $i++) {
				$this->_writeOverrideContentType(
					$objWriter, '/word/header'.$i.'.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.header+xml'
				);
			}
			
			for($i=1; $i<=$_cFtrs; $i++) {
				$this->_writeOverrideContentType(
					$objWriter, '/word/footer'.$i.'.xml', 'application/vnd.openxmlformats-officedocument.wordprocessingml.footer+xml'
				);
			}
			
			
		$objWriter->endElement();

		// Return
		return $objWriter->getData();
	}

	/**
	 * Get image mime type
	 *
	 * @param 	string	$pFile	Filename
	 * @return 	string	Mime Type
	 * @throws 	Exception
	 */
	private function _getImageMimeType($pFile = '') {
		if(PHPWord_Shared_File::file_exists($pFile)) {
			$image = getimagesize($pFile);
			return image_type_to_mime_type($image[2]);
		} else {
			throw new Exception("File $pFile does not exist");
		}
	}

	/**
	 * Write Default content type
	 *
	 * @param 	PHPWord_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	string 						$pPartname 		Part name
	 * @param 	string 						$pContentType 	Content type
	 * @throws 	Exception
	 */
	private function _writeDefaultContentType(PHPWord_Shared_XMLWriter $objWriter = null, $pPartname = '', $pContentType = '') {
		if($pPartname != '' && $pContentType != '') {
			// Write content type
			$objWriter->startElement('Default');
			$objWriter->writeAttribute('Extension', 	$pPartname);
			$objWriter->writeAttribute('ContentType', 	$pContentType);
			$objWriter->endElement();
		} else {
			throw new Exception("Invalid parameters passed.");
		}
	}

	/**
	 * Write Override content type
	 *
	 * @param 	PHPPowerPoint_Shared_XMLWriter 	$objWriter 		XML Writer
	 * @param 	string 						$pPartname 		Part name
	 * @param 	string 						$pContentType 	Content type
	 * @throws 	Exception
	 */
	private function _writeOverrideContentType(PHPWord_Shared_XMLWriter $objWriter = null, $pPartname = '', $pContentType = '') {
		if($pPartname != '' && $pContentType != '') {
			// Write content type
			$objWriter->startElement('Override');
			$objWriter->writeAttribute('PartName', 		$pPartname);
			$objWriter->writeAttribute('ContentType', 	$pContentType);
			$objWriter->endElement();
		} else {
			throw new Exception("Invalid parameters passed.");
		}
	}
}
