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


class PHPWord_Writer_Word2007_Document extends PHPWord_Writer_Word2007_Base {
	
	public function writeDocument(PHPWord $pPHPWord = null) {
		// Create XML writer
		
		if($this->getParentWriter()->getUseDiskCaching()) {
			$objWriter = new PHPWord_Shared_XMLWriter(PHPWord_Shared_XMLWriter::STORAGE_DISK, $this->getParentWriter()->getDiskCachingDirectory());
		} else {
			$objWriter = new PHPWord_Shared_XMLWriter(PHPWord_Shared_XMLWriter::STORAGE_MEMORY);
		}
		
		// XML header
		$objWriter->startDocument('1.0','UTF-8','yes');
		
		// w:document
		$objWriter->startElement('w:document');
		
		$objWriter->writeAttribute('xmlns:ve', 'http://schemas.openxmlformats.org/markup-compatibility/2006');
		$objWriter->writeAttribute('xmlns:o', 'urn:schemas-microsoft-com:office:office');
		$objWriter->writeAttribute('xmlns:r', 'http://schemas.openxmlformats.org/officeDocument/2006/relationships');
		$objWriter->writeAttribute('xmlns:m', 'http://schemas.openxmlformats.org/officeDocument/2006/math');
		$objWriter->writeAttribute('xmlns:v', 'urn:schemas-microsoft-com:vml');
		$objWriter->writeAttribute('xmlns:wp', 'http://schemas.openxmlformats.org/drawingml/2006/wordprocessingDrawing');
		$objWriter->writeAttribute('xmlns:w10', 'urn:schemas-microsoft-com:office:word');
		$objWriter->writeAttribute('xmlns:w', 'http://schemas.openxmlformats.org/wordprocessingml/2006/main');
		$objWriter->writeAttribute('xmlns:wne', 'http://schemas.microsoft.com/office/word/2006/wordml');
		
		$objWriter->startElement('w:body');
		
		$_sections = $pPHPWord->getSections();
		$countSections = count($_sections);
		$pSection = 0;
		
		if($countSections > 0) {
			foreach($_sections as $section) {
				$pSection++;
				
				$_elements = $section->getElements();
				
				foreach($_elements as $element) {
					if($element instanceof PHPWord_Section_Text) {
						$this->_writeText($objWriter, $element);
					} elseif($element instanceof PHPWord_Section_TextRun) {
						$this->_writeTextRun($objWriter, $element);
					} elseif($element instanceof PHPWord_Section_Link) {
						$this->_writeLink($objWriter, $element);
					} elseif($element instanceof PHPWord_Section_Title) {
						$this->_writeTitle($objWriter, $element);
					} elseif($element instanceof PHPWord_Section_TextBreak) {
						$this->_writeTextBreak($objWriter);
					} elseif($element instanceof PHPWord_Section_PageBreak) {
						$this->_writePageBreak($objWriter);
					} elseif($element instanceof PHPWord_Section_Table) {
						$this->_writeTable($objWriter, $element);
					} elseif($element instanceof PHPWord_Section_ListItem) {
						$this->_writeListItem($objWriter, $element);
					} elseif($element instanceof PHPWord_Section_Image ||
							 $element instanceof PHPWord_Section_MemoryImage) {
						$this->_writeImage($objWriter, $element);
					} elseif($element instanceof PHPWord_Section_Object) {
						$this->_writeObject($objWriter, $element);
					} elseif($element instanceof PHPWord_TOC) {
						$this->_writeTOC($objWriter);
					}
				}
				
				if($pSection == $countSections) {
					$this->_writeEndSection($objWriter, $section);
				} else {
					$this->_writeSection($objWriter, $section);
				}
			}
		}
		
		$objWriter->endElement(); // End w:body
		$objWriter->endElement(); // End w:document

		// Return
		return $objWriter->getData();
	}
	
	private function _writeSection(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section $section) {
		$objWriter->startElement('w:p');
			$objWriter->startElement('w:pPr');
				$this->_writeEndSection($objWriter, $section, 3);
			$objWriter->endElement();
		$objWriter->endElement();
	}
	
	private function _writeEndSection(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section $section) {
		$_settings = $section->getSettings();
		$_header = $section->getHeader();
		$_footer = $section->getFooter();
		$pgSzW = $_settings->getPageSizeW();
		$pgSzH = $_settings->getPageSizeH();
		$orientation = $_settings->getOrientation();
		
		$marginTop = $_settings->getMarginTop();
		$marginLeft = $_settings->getMarginLeft();
		$marginRight = $_settings->getMarginRight();
		$marginBottom = $_settings->getMarginBottom();
		
		$borders = $_settings->getBorderSize();
		
		$objWriter->startElement('w:sectPr');
			
			if(!is_null($_header)) {
				$rId = $_header->getRelationId();
				$objWriter->startElement('w:headerReference');
					$objWriter->writeAttribute('w:type', 'default');
					$objWriter->writeAttribute('r:id', 'rId'.$rId);
				$objWriter->endElement();
			}
			
			if(!is_null($_footer)) {
				$rId = $_footer->getRelationId();
				$objWriter->startElement('w:footerReference');
					$objWriter->writeAttribute('w:type', 'default');
					$objWriter->writeAttribute('r:id', 'rId'.$rId);
				$objWriter->endElement();
			}
			
			$objWriter->startElement('w:pgSz');
				$objWriter->writeAttribute('w:w', $pgSzW);
				$objWriter->writeAttribute('w:h', $pgSzH);
				
				if(!is_null($orientation) && strtolower($orientation) != 'portrait') {
					$objWriter->writeAttribute('w:orient', $orientation);
				}
				
			$objWriter->endElement();
			
			$objWriter->startElement('w:pgMar');
				$objWriter->writeAttribute('w:top', $marginTop);
				$objWriter->writeAttribute('w:right', $marginRight);
				$objWriter->writeAttribute('w:bottom', $marginBottom);
				$objWriter->writeAttribute('w:left', $marginLeft);
				$objWriter->writeAttribute('w:header', '720');
				$objWriter->writeAttribute('w:footer', '720');
				$objWriter->writeAttribute('w:gutter', '0');
			$objWriter->endElement();
			
			
			if(!is_null($borders[0]) || !is_null($borders[1]) || !is_null($borders[2]) || !is_null($borders[3])) {
				$borderColor = $_settings->getBorderColor();
				
				$objWriter->startElement('w:pgBorders');
					$objWriter->writeAttribute('w:offsetFrom', 'page');
					
					if(!is_null($borders[0])) {
						$objWriter->startElement('w:top');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $borders[0]);
							$objWriter->writeAttribute('w:space', '24');
							$objWriter->writeAttribute('w:color', $borderColor[0]);
						$objWriter->endElement();
					}
					
					if(!is_null($borders[1])) {
						$objWriter->startElement('w:left');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $borders[1]);
							$objWriter->writeAttribute('w:space', '24');
							$objWriter->writeAttribute('w:color', $borderColor[1]);
						$objWriter->endElement();
					}
					
					if(!is_null($borders[2])) {
						$objWriter->startElement('w:right');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $borders[2]);
							$objWriter->writeAttribute('w:space', '24');
							$objWriter->writeAttribute('w:color', $borderColor[2]);
						$objWriter->endElement();
					}
					
					if(!is_null($borders[3])) {
						$objWriter->startElement('w:bottom');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $borders[3]);
							$objWriter->writeAttribute('w:space', '24');
							$objWriter->writeAttribute('w:color', $borderColor[3]);
						$objWriter->endElement();
					}
				$objWriter->endElement();
			}

			
			$objWriter->startElement('w:cols');
				$objWriter->writeAttribute('w:space', '720');
			$objWriter->endElement();
			
			
		$objWriter->endElement();
	}
	
	private function _writePageBreak(PHPWord_Shared_XMLWriter $objWriter = null) {
		$objWriter->startElement('w:p');
			$objWriter->startElement('w:r');
				$objWriter->startElement('w:br');
					$objWriter->writeAttribute('w:type', 'page');
				$objWriter->endElement();
			$objWriter->endElement();
		$objWriter->endElement();
	}
	
	private function _writeListItem(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section_ListItem $listItem) {
		$textObject = $listItem->getTextObject();
		$text = $textObject->getText();
        $styleParagraph = $textObject->getParagraphStyle();
        $SpIsObject = ($styleParagraph instanceof PHPWord_Style_Paragraph) ? true : false;
        
		$depth = $listItem->getDepth();
		$listType = $listItem->getStyle()->getListType();
		
		$objWriter->startElement('w:p');
			$objWriter->startElement('w:pPr');
            
                if($SpIsObject) {
                    $this->_writeParagraphStyle($objWriter, $styleParagraph, true);
                } elseif(!$SpIsObject && !is_null($styleParagraph)) {
                    $objWriter->startElement('w:pStyle');
                        $objWriter->writeAttribute('w:val', $styleParagraph);
                    $objWriter->endElement();
                }
            
				$objWriter->startElement('w:numPr');
				
					$objWriter->startElement('w:ilvl');
						$objWriter->writeAttribute('w:val', $depth);
					$objWriter->endElement();
					
					$objWriter->startElement('w:numId');
						$objWriter->writeAttribute('w:val', $listType);
					$objWriter->endElement();
					
				$objWriter->endElement();
			$objWriter->endElement();
			
			$this->_writeText($objWriter, $textObject, true);
			
		$objWriter->endElement();
	}
	
	protected function _writeObject(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section_Object $object) {
		$rIdObject = $object->getRelationId();
		$rIdImage = $object->getImageRelationId();
		$shapeId = md5($rIdObject.'_'.$rIdImage);
		
		$objectId = $object->getObjectId();
		
		$style = $object->getStyle();
		$width = $style->getWidth();
		$height = $style->getHeight();
		$align = $style->getAlign();
		
		
		$objWriter->startElement('w:p');
		
			if(!is_null($align)) {
				$objWriter->startElement('w:pPr');
					$objWriter->startElement('w:jc');
						$objWriter->writeAttribute('w:val', $align);
					$objWriter->endElement();
				$objWriter->endElement();
			}
		
			$objWriter->startElement('w:r');
			
				$objWriter->startElement('w:object');
				$objWriter->writeAttribute('w:dxaOrig', '249');
				$objWriter->writeAttribute('w:dyaOrig', '160');
				
					$objWriter->startElement('v:shape');
					$objWriter->writeAttribute('id', $shapeId);
					$objWriter->writeAttribute('type', '#_x0000_t75');
					$objWriter->writeAttribute('style', 'width:104px;height:67px');
					$objWriter->writeAttribute('o:ole', '');
					
						$objWriter->startElement('v:imagedata');
						$objWriter->writeAttribute('r:id', 'rId'.$rIdImage);
						$objWriter->writeAttribute('o:title', '');
						$objWriter->endElement();
						
					$objWriter->endElement();
					
					$objWriter->startElement('o:OLEObject');
					$objWriter->writeAttribute('Type', 'Embed');
					$objWriter->writeAttribute('ProgID', 'Package');
					$objWriter->writeAttribute('ShapeID', $shapeId);
					$objWriter->writeAttribute('DrawAspect', 'Icon');
					$objWriter->writeAttribute('ObjectID', '_'.$objectId);
					$objWriter->writeAttribute('r:id', 'rId'.$rIdObject);
					$objWriter->endElement();
					
				$objWriter->endElement();
			
			$objWriter->endElement(); // w:r
			
		$objWriter->endElement(); // w:p
	}
	
	private function _writeTOC(PHPWord_Shared_XMLWriter $objWriter = null) {
		$titles = PHPWord_TOC::getTitles();
		$styleFont = PHPWord_TOC::getStyleFont();
		
		$styleTOC = PHPWord_TOC::getStyleTOC();
		$fIndent = $styleTOC->getIndent();
		$tabLeader = $styleTOC->getTabLeader();
		$tabPos = $styleTOC->getTabPos();
		
		$isObject = ($styleFont instanceof PHPWord_Style_Font) ? true : false;
		
		for($i=0; $i<count($titles); $i++) {
			$title = $titles[$i];
			$indent = ($title['depth'] - 1) * $fIndent;
			
			$objWriter->startElement('w:p');
			
				$objWriter->startElement('w:pPr');
					
                    if($isObject && !is_null($styleFont->getParagraphStyle())) {
                        $this->_writeParagraphStyle($objWriter, $styleFont->getParagraphStyle());
                    }
					
					if($indent > 0) {
						$objWriter->startElement('w:ind');
							$objWriter->writeAttribute('w:left', $indent);
						$objWriter->endElement();
					}
					
					if(!empty($styleFont) && !$isObject) {
						$objWriter->startElement('w:pPr');
							$objWriter->startElement('w:pStyle');
								$objWriter->writeAttribute('w:val', $styleFont);
							$objWriter->endElement();
						$objWriter->endElement();
					}
					
					$objWriter->startElement('w:tabs');
						$objWriter->startElement('w:tab');
							$objWriter->writeAttribute('w:val', 'right');
							if(!empty($tabLeader)) {
								$objWriter->writeAttribute('w:leader', $tabLeader);
							}
							$objWriter->writeAttribute('w:pos', $tabPos);
						$objWriter->endElement();
					$objWriter->endElement();
				
				$objWriter->endElement(); // w:pPr
				
				
				if($i == 0) {
					$objWriter->startElement('w:r');
						$objWriter->startElement('w:fldChar');
							$objWriter->writeAttribute('w:fldCharType', 'begin');
						$objWriter->endElement();
					$objWriter->endElement();
					
					$objWriter->startElement('w:r');
						$objWriter->startElement('w:instrText');
							$objWriter->writeAttribute('xml:space', 'preserve');
							$objWriter->writeRaw('TOC \o "1-9" \h \z \u');
						$objWriter->endElement();
					$objWriter->endElement();
					
					$objWriter->startElement('w:r');
						$objWriter->startElement('w:fldChar');
							$objWriter->writeAttribute('w:fldCharType', 'separate');
						$objWriter->endElement();
					$objWriter->endElement();
				}
				
				$objWriter->startElement('w:hyperlink');
					$objWriter->writeAttribute('w:anchor', $title['anchor']);
					$objWriter->writeAttribute('w:history', '1');
					
					$objWriter->startElement('w:r');
					
						if($isObject) {
							$this->_writeTextStyle($objWriter, $styleFont);
						}
						
						$objWriter->startElement('w:t');
							$objWriter->writeRaw($title['text']);
						$objWriter->endElement();
					$objWriter->endElement();
					
					$objWriter->startElement('w:r');
						$objWriter->writeElement('w:tab', null);
					$objWriter->endElement();
					
					$objWriter->startElement('w:r');
						$objWriter->startElement('w:fldChar');
							$objWriter->writeAttribute('w:fldCharType', 'begin');
						$objWriter->endElement();
					$objWriter->endElement();
					
					$objWriter->startElement('w:r');
						$objWriter->startElement('w:instrText');
							$objWriter->writeAttribute('xml:space', 'preserve');
							$objWriter->writeRaw('PAGEREF '.$title['anchor'].' \h');
						$objWriter->endElement();
					$objWriter->endElement();
					
					$objWriter->startElement('w:r');
						$objWriter->startElement('w:fldChar');
							$objWriter->writeAttribute('w:fldCharType', 'end');
						$objWriter->endElement();
					$objWriter->endElement();
					
				$objWriter->endElement(); // w:hyperlink
			
			$objWriter->endElement(); // w:p
		}
		
		$objWriter->startElement('w:p');
			$objWriter->startElement('w:r');
				$objWriter->startElement('w:fldChar');
					$objWriter->writeAttribute('w:fldCharType', 'end');
				$objWriter->endElement();
			$objWriter->endElement();
		$objWriter->endElement();
	}
}
