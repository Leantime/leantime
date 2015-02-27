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


class PHPWord_Writer_Word2007_Styles extends PHPWord_Writer_Word2007_Base {
	
	private $_document;
	
	public function writeStyles(PHPWord $pPHPWord = null) {
		// Create XML writer
		$objWriter = null;
		if($this->getParentWriter()->getUseDiskCaching()) {
			$objWriter = new PHPWord_Shared_XMLWriter(PHPWord_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
		} else {
			$objWriter = new PHPWord_Shared_XMLWriter(PHPWord_Shared_XMLWriter::STORAGE_MEMORY);
		}
		
		$this->_document = $pPHPWord;
		
		// XML header
		$objWriter->startDocument('1.0','UTF-8','yes');
		
		$objWriter->startElement('w:styles');
		
		$objWriter->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
		$objWriter->writeAttribute('xmlns:w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
		
		// Write DocDefaults
		$this->_writeDocDefaults($objWriter);

		
		// Write Style Definitions
		$styles = PHPWord_Style::getStyles();
		if(count($styles) > 0) {
			foreach($styles as $styleName => $style) {
				if($style instanceof PHPWord_Style_Font) {
					
					$paragraphStyle = $style->getParagraphStyle();
					$styleType = $style->getStyleType();
					
					$type = ($styleType == 'title') ? 'paragraph' : 'character';
					
					if(!is_null($paragraphStyle)) {
						$type = 'paragraph';
					}
					
					$objWriter->startElement('w:style');
						$objWriter->writeAttribute('w:type', $type);
						
						if($styleType == 'title') {
							$arrStyle = explode('_', $styleName);
							$styleId = 'Heading'.$arrStyle[1];
							$styleName = 'heading '.$arrStyle[1];
							$styleLink = 'Heading'.$arrStyle[1].'Char';
							$objWriter->writeAttribute('w:styleId', $styleId);
							
							$objWriter->startElement('w:link');
								$objWriter->writeAttribute('w:val', $styleLink);
							$objWriter->endElement();
						}
						
						$objWriter->startElement('w:name');
							$objWriter->writeAttribute('w:val', $styleName);
						$objWriter->endElement();
						
						if(!is_null($paragraphStyle)) {
							$this->_writeParagraphStyle($objWriter, $paragraphStyle);
						}
						
						$this->_writeTextStyle($objWriter, $style);
						
					$objWriter->endElement();
		
				} elseif($style instanceof PHPWord_Style_Paragraph) {
					$objWriter->startElement('w:style');
						$objWriter->writeAttribute('w:type', 'paragraph');
						$objWriter->writeAttribute('w:customStyle', '1');
						$objWriter->writeAttribute('w:styleId', $styleName);
						
						$objWriter->startElement('w:name');
							$objWriter->writeAttribute('w:val', $styleName);
						$objWriter->endElement();
						
						$this->_writeParagraphStyle($objWriter, $style);
					$objWriter->endElement();
					
				} elseif($style instanceof PHPWord_Style_TableFull) {
					$objWriter->startElement('w:style');
						$objWriter->writeAttribute('w:type', 'table');
						$objWriter->writeAttribute('w:customStyle', '1');
						$objWriter->writeAttribute('w:styleId', $styleName);
						
						$objWriter->startElement('w:name');
							$objWriter->writeAttribute('w:val', $styleName);
						$objWriter->endElement();
						
						$objWriter->startElement('w:uiPriority');
							$objWriter->writeAttribute('w:val', '99');
						$objWriter->endElement();
						
						$this->_writeFullTableStyle($objWriter, $style);
						
					$objWriter->endElement();
				}
			}
		}
		
		$objWriter->endElement(); // w:styles
		
		// Return
		return $objWriter->getData();
	}
	
	private function _writeFullTableStyle(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Style_TableFull $style) {

		$brdSz = $style->getBorderSize();
		$brdCol = $style->getBorderColor();
		$bgColor = $style->getBgColor();
		$cellMargin = $style->getCellMargin();
		
		$bTop = (!is_null($brdSz[0])) ? true : false;
		$bLeft = (!is_null($brdSz[1])) ? true : false;
		$bRight = (!is_null($brdSz[2])) ? true : false;
		$bBottom = (!is_null($brdSz[3])) ? true : false;
		$bInsH = (!is_null($brdSz[4])) ? true : false;
		$bInsV = (!is_null($brdSz[5])) ? true : false;
		$borders = ($bTop || $bLeft || $bRight || $bBottom || $bInsH || $bInsV) ? true : false;
		
		$mTop = (!is_null($cellMargin[0])) ? true : false;
		$mLeft = (!is_null($cellMargin[1])) ? true : false;
		$mRight = (!is_null($cellMargin[2])) ? true : false;
		$mBottom = (!is_null($cellMargin[3])) ? true : false;
		$margins = ($mTop || $mLeft || $mRight || $mBottom) ? true : false;
		
		$objWriter->startElement('w:tblPr');
			
			if($margins) {
				$objWriter->startElement('w:tblCellMar');
					if($mTop) {
						echo $margins[0];
						$objWriter->startElement('w:top');
							$objWriter->writeAttribute('w:w', $cellMargin[0]);
							$objWriter->writeAttribute('w:type', 'dxa');
						$objWriter->endElement();
					}
					if($mLeft) {
						$objWriter->startElement('w:left');
							$objWriter->writeAttribute('w:w', $cellMargin[1]);
							$objWriter->writeAttribute('w:type', 'dxa');
						$objWriter->endElement();
					}
					if($mRight) {
						$objWriter->startElement('w:right');
							$objWriter->writeAttribute('w:w', $cellMargin[2]);
							$objWriter->writeAttribute('w:type', 'dxa');
						$objWriter->endElement();
					}
					if($mBottom) {
						$objWriter->startElement('w:bottom');
							$objWriter->writeAttribute('w:w', $cellMargin[3]);
							$objWriter->writeAttribute('w:type', 'dxa');
						$objWriter->endElement();
					}
				$objWriter->endElement();
			}
			
			if($borders) {
				$objWriter->startElement('w:tblBorders');
					if($bTop) {
						$objWriter->startElement('w:top');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[0]);
							$objWriter->writeAttribute('w:color', $brdCol[0]);
						$objWriter->endElement();
					}
					if($bLeft) {
						$objWriter->startElement('w:left');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[1]);
							$objWriter->writeAttribute('w:color', $brdCol[1]);
						$objWriter->endElement();
					}
					if($bRight) {
						$objWriter->startElement('w:right');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[2]);
							$objWriter->writeAttribute('w:color', $brdCol[2]);
						$objWriter->endElement();
					}
					if($bBottom) {
						$objWriter->startElement('w:bottom');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[3]);
							$objWriter->writeAttribute('w:color', $brdCol[3]);
						$objWriter->endElement();
					}
					if($bInsH) {
						$objWriter->startElement('w:insideH');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[4]);
							$objWriter->writeAttribute('w:color', $brdCol[4]);
						$objWriter->endElement();
					}
					if($bInsV) {
						$objWriter->startElement('w:insideV');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[5]);
							$objWriter->writeAttribute('w:color', $brdCol[5]);
						$objWriter->endElement();
					}
				$objWriter->endElement();
			}
			
		$objWriter->endElement();
		
		if(!is_null($bgColor)) {
			$objWriter->startElement('w:tcPr');
				$objWriter->startElement('w:shd');
					$objWriter->writeAttribute('w:val', 'clear');
					$objWriter->writeAttribute('w:color', 'auto');
					$objWriter->writeAttribute('w:fill', $bgColor);
				$objWriter->endElement();
			$objWriter->endElement();
		}
		
		
		// First Row
		$firstRow = $style->getFirstRow();
		if(!is_null($firstRow)) {
			$this->_writeRowStyle($objWriter, 'firstRow', $firstRow);
		}
	}
	
	private function _writeRowStyle(PHPWord_Shared_XMLWriter $objWriter = null, $type, PHPWord_Style_TableFull $style) {
		$brdSz = $style->getBorderSize();
		$brdCol = $style->getBorderColor();
		$bgColor = $style->getBgColor();
		
		$bTop = (!is_null($brdSz[0])) ? true : false;
		$bLeft = (!is_null($brdSz[1])) ? true : false;
		$bRight = (!is_null($brdSz[2])) ? true : false;
		$bBottom = (!is_null($brdSz[3])) ? true : false;
		$borders = ($bTop || $bLeft || $bRight || $bBottom) ? true : false;
		
		$objWriter->startElement('w:tblStylePr');
			$objWriter->writeAttribute('w:type', $type);
			
			$objWriter->startElement('w:tcPr');
				if(!is_null($bgColor)) {
					$objWriter->startElement('w:shd');
						$objWriter->writeAttribute('w:val', 'clear');
						$objWriter->writeAttribute('w:color', 'auto');
						$objWriter->writeAttribute('w:fill', $bgColor);
					$objWriter->endElement();
				}
				
				$objWriter->startElement('w:tcBorders');
					if($bTop) {
						$objWriter->startElement('w:top');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[0]);
							$objWriter->writeAttribute('w:color', $brdCol[0]);
						$objWriter->endElement();
					}
					if($bLeft) {
						$objWriter->startElement('w:left');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[1]);
							$objWriter->writeAttribute('w:color', $brdCol[1]);
						$objWriter->endElement();
					}
					if($bRight) {
						$objWriter->startElement('w:right');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[2]);
							$objWriter->writeAttribute('w:color', $brdCol[2]);
						$objWriter->endElement();
					}
					if($bBottom) {
						$objWriter->startElement('w:bottom');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[3]);
							$objWriter->writeAttribute('w:color', $brdCol[3]);
						$objWriter->endElement();
					}
				$objWriter->endElement();
				
			$objWriter->endElement();
			
		$objWriter->endElement();
	}
	
	
	private function _writeDocDefaults(PHPWord_Shared_XMLWriter $objWriter = null) {
		$fontName = $this->_document->getDefaultFontName();
		$fontSize = $this->_document->getDefaultFontSize();
		
		$objWriter->startElement('w:docDefaults');
			$objWriter->startElement('w:rPrDefault');
				$objWriter->startElement('w:rPr');
				
					$objWriter->startElement('w:rFonts');
						$objWriter->writeAttribute('w:ascii', $fontName);
						$objWriter->writeAttribute('w:hAnsi', $fontName);
						$objWriter->writeAttribute('w:eastAsia', $fontName);
						$objWriter->writeAttribute('w:cs', $fontName);
					$objWriter->endElement();
					
					$objWriter->startElement('w:sz');
						$objWriter->writeAttribute('w:val', $fontSize);
					$objWriter->endElement();
					
					$objWriter->startElement('w:szCs');
						$objWriter->writeAttribute('w:val', $fontSize);
					$objWriter->endElement();
					
				$objWriter->endElement();
			$objWriter->endElement();
		$objWriter->endElement();
	}
}
?>