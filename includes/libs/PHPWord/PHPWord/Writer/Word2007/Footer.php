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


class PHPWord_Writer_Word2007_Footer extends PHPWord_Writer_Word2007_Base {
	
	public function writeFooter(PHPWord_Section_Footer $footer) {
		// Create XML writer
		$objWriter = null;
		if ($this->getParentWriter()->getUseDiskCaching()) {
			$objWriter = new PHPWord_Shared_XMLWriter(PHPWord_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
		} else {
			$objWriter = new PHPWord_Shared_XMLWriter(PHPWord_Shared_XMLWriter::STORAGE_MEMORY);
		}
		
		// XML header
		$objWriter->startDocument('1.0', 'UTF-8', 'yes');
		
		$objWriter->startElement('w:ftr');
			$objWriter->writeAttribute('xmlns:ve','http://schemas.openxmlformats.org/markup-compatibility/2006');
			$objWriter->writeAttribute('xmlns:o','urn:schemas-microsoft-com:office:office');
			$objWriter->writeAttribute('xmlns:r','http://schemas.openxmlformats.org/officeDocument/2006/relationships');
			$objWriter->writeAttribute('xmlns:m','http://schemas.openxmlformats.org/officeDocument/2006/math');
			$objWriter->writeAttribute('xmlns:v','urn:schemas-microsoft-com:vml');
			$objWriter->writeAttribute('xmlns:wp','http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing');
			$objWriter->writeAttribute('xmlns:w10','urn:schemas-microsoft-com:office:word');
			$objWriter->writeAttribute('xmlns:w','http://schemas.openxmlformats.org/wordprocessingml/2006/main');
			$objWriter->writeAttribute('xmlns:wne','http://schemas.microsoft.com/office/word/2006/wordml');
		
		$_elements = $footer->getElements();
		
		foreach($_elements as $element) {
			if($element instanceof PHPWord_Section_Text) {
				$this->_writeText($objWriter, $element);
			} elseif($element instanceof PHPWord_Section_TextRun) {
				$this->_writeTextRun($objWriter, $element);
			} elseif($element instanceof PHPWord_Section_TextBreak) {
				$this->_writeTextBreak($objWriter);
			} elseif($element instanceof PHPWord_Section_Table) {
				$this->_writeTable($objWriter, $element);
			} elseif($element instanceof PHPWord_Section_Image ||
					 $element instanceof PHPWord_Section_MemoryImage) {
				$this->_writeImage($objWriter, $element);
			} elseif($element instanceof PHPWord_Section_Footer_PreserveText) {
				$this->_writePreserveText($objWriter, $element);
			}
		}
		
		$objWriter->endElement();
		
		// Return
		return $objWriter->getData();
	}
}
?>
