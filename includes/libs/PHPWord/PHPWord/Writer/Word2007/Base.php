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


class PHPWord_Writer_Word2007_Base extends PHPWord_Writer_Word2007_WriterPart {
	
	protected function _writeText(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section_Text $text, $withoutP = false) {
		$styleFont = $text->getFontStyle();
		
		$SfIsObject = ($styleFont instanceof PHPWord_Style_Font) ? true : false;
		
		$strText = htmlspecialchars($text->getText());
		// create array of newlines
		$str_tmp=explode("\n",$strText);
	
		// cycle through the array and create new paragraph per new line
		for ($iii = 0; $iii < count($str_tmp); $iii++) {		
		
		if(!$withoutP) {
			$objWriter->startElement('w:p');
			
			$styleParagraph = $text->getParagraphStyle();
			$SpIsObject = ($styleParagraph instanceof PHPWord_Style_Paragraph) ? true : false;
			
			if($SpIsObject) {
				$this->_writeParagraphStyle($objWriter, $styleParagraph);
			} elseif(!$SpIsObject && !is_null($styleParagraph)) {
				$objWriter->startElement('w:pPr');
					$objWriter->startElement('w:pStyle');
						$objWriter->writeAttribute('w:val', $styleParagraph);
					$objWriter->endElement();
				$objWriter->endElement();
			}
		}
		
		
		$strText = $str_tmp[$iii];   
		$strText = PHPWord_Shared_String::ControlCharacterPHP2OOXML($strText); // this is where new line characters are striped.
		
		$objWriter->startElement('w:r');
		
			if($SfIsObject) {
				$this->_writeTextStyle($objWriter, $styleFont);
			} elseif(!$SfIsObject && !is_null($styleFont)) {
				$objWriter->startElement('w:rPr');
					$objWriter->startElement('w:rStyle');
						$objWriter->writeAttribute('w:val', $styleFont);
					$objWriter->endElement();
				$objWriter->endElement();
			}
		
			$objWriter->startElement('w:t');
				$objWriter->writeAttribute('xml:space', 'preserve'); // needed because of drawing spaces before and after text
				$objWriter->writeRaw($strText);
			$objWriter->endElement();
			
		$objWriter->endElement(); // w:r
		
		if(!$withoutP) {
			$objWriter->endElement(); // w:p
		}
	    } // end of for $iii 
	}
	
	protected function _writeTextRun(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section_TextRun $textrun) {
		$elements = $textrun->getElements();
		$styleParagraph = $textrun->getParagraphStyle();
		
		$SpIsObject = ($styleParagraph instanceof PHPWord_Style_Paragraph) ? true : false;
		
		$objWriter->startElement('w:p');
		
		if($SpIsObject) {
			$this->_writeParagraphStyle($objWriter, $styleParagraph);
		} elseif(!$SpIsObject && !is_null($styleParagraph)) {
			$objWriter->startElement('w:pPr');
				$objWriter->startElement('w:pStyle');
					$objWriter->writeAttribute('w:val', $styleParagraph);
				$objWriter->endElement();
			$objWriter->endElement();
		}
		
		if(count($elements) > 0) {
			foreach($elements as $element) {
				if($element instanceof PHPWord_Section_Text) {
					$this->_writeText($objWriter, $element, true);
				} elseif($element instanceof PHPWord_Section_Link) {
					$this->_writeLink($objWriter, $element, true);
				}
			}
		}
		
		$objWriter->endElement();
	}
	
	protected function _writeParagraphStyle(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Style_Paragraph $style, $withoutPPR = false) {
		$align = $style->getAlign();
		$spaceBefore = $style->getSpaceBefore();
		$spaceAfter = $style->getSpaceAfter();
		$spacing = $style->getSpacing();
		
        
		if(!is_null($align) || !is_null($spacing) || !is_null($spaceBefore) || !is_null($spaceAfter)) {
			
            if(!$withoutPPR) {
                $objWriter->startElement('w:pPr');
            }
			
			if(!is_null($align)) {
				$objWriter->startElement('w:jc');
					$objWriter->writeAttribute('w:val', $align);
				$objWriter->endElement();
			}
			
			if(!is_null($spaceBefore) || !is_null($spaceAfter) || !is_null($spacing)) {
				
				$objWriter->startElement('w:spacing');
				
					if(!is_null($spaceBefore)) {
						$objWriter->writeAttribute('w:before', $spaceBefore);
					}
					if(!is_null($spaceAfter)) {
						$objWriter->writeAttribute('w:after', $spaceAfter);
					}
					if(!is_null($spacing)) {
						$objWriter->writeAttribute('w:line', $spacing);
						$objWriter->writeAttribute('w:lineRule', 'auto');
					}

				$objWriter->endElement();
			}
			
            if(!$withoutPPR) {
			    $objWriter->endElement(); // w:pPr
            }
		}
	}
	
	protected function _writeLink(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section_Link $link, $withoutP = false) {
		$rID = $link->getRelationId();
		$linkName = $link->getLinkName();
		if(is_null($linkName)) {
			$linkName = $link->getLinkSrc();
		}
		
		$styleFont = $link->getFontStyle();
		$SfIsObject = ($styleFont instanceof PHPWord_Style_Font) ? true : false;
		
		if(!$withoutP) {
			$objWriter->startElement('w:p');
			
			$styleParagraph = $link->getParagraphStyle();
			$SpIsObject = ($styleParagraph instanceof PHPWord_Style_Paragraph) ? true : false;
			
			if($SpIsObject) {
				$this->_writeParagraphStyle($objWriter, $styleParagraph);
			} elseif(!$SpIsObject && !is_null($styleParagraph)) {
				$objWriter->startElement('w:pPr');
					$objWriter->startElement('w:pStyle');
						$objWriter->writeAttribute('w:val', $styleParagraph);
					$objWriter->endElement();
				$objWriter->endElement();
			}
		}
		
			$objWriter->startElement('w:hyperlink');
				$objWriter->writeAttribute('r:id', 'rId'.$rID);
				$objWriter->writeAttribute('w:history', '1');
				
				$objWriter->startElement('w:r');
					if($SfIsObject) {
						$this->_writeTextStyle($objWriter, $styleFont);
					} elseif(!$SfIsObject && !is_null($styleFont)) {
						$objWriter->startElement('w:rPr');
							$objWriter->startElement('w:rStyle');
								$objWriter->writeAttribute('w:val', $styleFont);
							$objWriter->endElement();
						$objWriter->endElement();
					}
				
					$objWriter->startElement('w:t');
						$objWriter->writeAttribute('xml:space', 'preserve'); // needed because of drawing spaces before and after text
						$objWriter->writeRaw($linkName);
					$objWriter->endElement();
				$objWriter->endElement();
		
			$objWriter->endElement();
		
		if(!$withoutP) {
			$objWriter->endElement(); // w:p
		}
	}
	
	protected function _writePreserveText(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section_Footer_PreserveText $textrun) {
		$styleFont = $textrun->getFontStyle();
		$styleParagraph = $textrun->getParagraphStyle();
		
		$SfIsObject = ($styleFont instanceof PHPWord_Style_Font) ? true : false;
		$SpIsObject = ($styleParagraph instanceof PHPWord_Style_Paragraph) ? true : false;
		
		$arrText = $textrun->getText();
		
		$objWriter->startElement('w:p');
		
			if($SpIsObject) {
				$this->_writeParagraphStyle($objWriter, $styleParagraph);
			} elseif(!$SpIsObject && !is_null($styleParagraph)) {
				$objWriter->startElement('w:pPr');
					$objWriter->startElement('w:pStyle');
						$objWriter->writeAttribute('w:val', $styleParagraph);
					$objWriter->endElement();
				$objWriter->endElement();
			}
			
			foreach($arrText as $text) {
				
				if(substr($text, 0, 1) == '{') {
					$text = substr($text, 1, -1);
					
					$objWriter->startElement('w:r');
						$objWriter->startElement('w:fldChar');
							$objWriter->writeAttribute('w:fldCharType', 'begin');
						$objWriter->endElement();
					$objWriter->endElement();
					
					$objWriter->startElement('w:r');
					
						if($SfIsObject) {
							$this->_writeTextStyle($objWriter, $styleFont);
						} elseif(!$SfIsObject && !is_null($styleFont)) {
							$objWriter->startElement('w:rPr');
								$objWriter->startElement('w:rStyle');
									$objWriter->writeAttribute('w:val', $styleFont);
								$objWriter->endElement();
							$objWriter->endElement();
						}
						
						$objWriter->startElement('w:instrText');
							$objWriter->writeAttribute('xml:space', 'preserve');
							$objWriter->writeRaw($text);
						$objWriter->endElement();
					$objWriter->endElement();
					
					$objWriter->startElement('w:r');
						$objWriter->startElement('w:fldChar');
							$objWriter->writeAttribute('w:fldCharType', 'separate');
						$objWriter->endElement();
					$objWriter->endElement();
					
					$objWriter->startElement('w:r');
						$objWriter->startElement('w:fldChar');
							$objWriter->writeAttribute('w:fldCharType', 'end');
						$objWriter->endElement();
					$objWriter->endElement();
				} else {
					$text = htmlspecialchars($text);
					$text = PHPWord_Shared_String::ControlCharacterPHP2OOXML($text);
					
					$objWriter->startElement('w:r');
						
						if($SfIsObject) {
							$this->_writeTextStyle($objWriter, $styleFont);
						} elseif(!$SfIsObject && !is_null($styleFont)) {
							$objWriter->startElement('w:rPr');
								$objWriter->startElement('w:rStyle');
									$objWriter->writeAttribute('w:val', $styleFont);
								$objWriter->endElement();
							$objWriter->endElement();
						}
						
						$objWriter->startElement('w:t');
							$objWriter->writeAttribute('xml:space', 'preserve');
							$objWriter->writeRaw($text);
						$objWriter->endElement();
					$objWriter->endElement();
				}
			}
		
		$objWriter->endElement(); // p
	}
	
	protected function _writeTextStyle(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Style_Font $style) {
		$font = $style->getName();
		$bold = $style->getBold();
		$italic = $style->getItalic();
		$color = $style->getColor();
		$size = $style->getSize();
		$fgColor = $style->getFgColor();
		$striketrough = $style->getStrikethrough();
		$underline = $style->getUnderline();
		
		$objWriter->startElement('w:rPr');
		
		// Font
		if($font != 'Arial') {
			$objWriter->startElement('w:rFonts');
				$objWriter->writeAttribute('w:ascii', $font);
				$objWriter->writeAttribute('w:hAnsi', $font);
				$objWriter->writeAttribute('w:cs', $font);
			$objWriter->endElement();
		}
		
		// Color
		if($color != '000000') {
			$objWriter->startElement('w:color');
				$objWriter->writeAttribute('w:val', $color);
			$objWriter->endElement();
		}
		
		// Size
		if($size != 20) {
			$objWriter->startElement('w:sz');
				$objWriter->writeAttribute('w:val', $size);
			$objWriter->endElement();
			$objWriter->startElement('w:szCs');
				$objWriter->writeAttribute('w:val', $size);
			$objWriter->endElement();
		}
		
		// Bold
		if($bold) {
			$objWriter->writeElement('w:b', null);
		}
		
		// Italic
		if($italic) {
			$objWriter->writeElement('w:i', null);
			$objWriter->writeElement('w:iCs', null);
		}
		
		// Underline
		if(!is_null($underline) && $underline != 'none') {
			$objWriter->startElement('w:u');
				$objWriter->writeAttribute('w:val', $underline);
			$objWriter->endElement();
		}
		
		// Striketrough
		if($striketrough) {
			$objWriter->writeElement('w:strike', null);
		}
		
		// Foreground-Color
		if(!is_null($fgColor)) {
			$objWriter->startElement('w:highlight');
				$objWriter->writeAttribute('w:val', $fgColor);
			$objWriter->endElement();
		}
		
		$objWriter->endElement();
	}
	
	protected function _writeTextBreak(PHPWord_Shared_XMLWriter $objWriter = null) {
		$objWriter->writeElement('w:p', null);
	}
	
	protected function _writeTable(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section_Table $table) {
		$_rows = $table->getRows();
		$_cRows = count($_rows);
		
		if($_cRows > 0) {
			$objWriter->startElement('w:tbl');
				$tblStyle = $table->getStyle();
				if($tblStyle instanceof PHPWord_Style_Table) {
					$this->_writeTableStyle($objWriter, $tblStyle);
				} else {
					if(!empty($tblStyle)) {
						$objWriter->startElement('w:tblPr');
							$objWriter->startElement('w:tblStyle');
								$objWriter->writeAttribute('w:val', $tblStyle);
							$objWriter->endElement();
						$objWriter->endElement();
					}
				}

				$_heights = $table->getRowHeights();
				for($i=0; $i<$_cRows; $i++) {
					$row = $_rows[$i];
					$height = $_heights[$i];
					
					$objWriter->startElement('w:tr');
					
						if(!is_null($height)) {
							$objWriter->startElement('w:trPr');
								$objWriter->startElement('w:trHeight');
									$objWriter->writeAttribute('w:val', $height);
								$objWriter->endElement();
							$objWriter->endElement();
						}
						
						foreach($row as $cell) {
							$objWriter->startElement('w:tc');
								
								$cellStyle = $cell->getStyle();
								$width = $cell->getWidth();
								
								$objWriter->startElement('w:tcPr');
									$objWriter->startElement('w:tcW');
										$objWriter->writeAttribute('w:w', $width);
										$objWriter->writeAttribute('w:type', 'dxa');
									$objWriter->endElement();
									
									if($cellStyle instanceof PHPWord_Style_Cell) {
										$this->_writeCellStyle($objWriter, $cellStyle);
									}
									
								$objWriter->endElement();
								
								$_elements = $cell->getElements();
								if(count($_elements) > 0) {
									foreach($_elements as $element) {
										if($element instanceof PHPWord_Section_Text) {
											$this->_writeText($objWriter, $element);
										} elseif($element instanceof PHPWord_Section_TextRun) {
											$this->_writeTextRun($objWriter, $element);
										} elseif($element instanceof PHPWord_Section_Link) {
											$this->_writeLink($objWriter, $element);
										} elseif($element instanceof PHPWord_Section_TextBreak) {
											$this->_writeTextBreak($objWriter);
										} elseif($element instanceof PHPWord_Section_ListItem) {
											$this->_writeListItem($objWriter, $element);
										} elseif($element instanceof PHPWord_Section_Image ||
												 $element instanceof PHPWord_Section_MemoryImage) {
											$this->_writeImage($objWriter, $element);
										} elseif($element instanceof PHPWord_Section_Object) {
											$this->_writeObject($objWriter, $element);
										} elseif($element instanceof PHPWord_Section_Footer_PreserveText) {
											$this->_writePreserveText($objWriter, $element);
										}
									}
								} else {
									$this->_writeTextBreak($objWriter);
								}
								
							$objWriter->endElement();
						}
					$objWriter->endElement();
				}
			$objWriter->endElement();
		}
	}
	
	protected function _writeTableStyle(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Style_Table $style = null) {
		$margins = $style->getCellMargin();
		$mTop = (!is_null($margins[0])) ? true : false;
		$mLeft = (!is_null($margins[1])) ? true : false;
		$mRight = (!is_null($margins[2])) ? true : false;
		$mBottom = (!is_null($margins[3])) ? true : false;
		
		if($mTop || $mLeft || $mRight || $mBottom) {
			$objWriter->startElement('w:tblPr');
				$objWriter->startElement('w:tblCellMar');
					
					if($mTop) {
						$objWriter->startElement('w:top');
							$objWriter->writeAttribute('w:w', $margins[0]);
							$objWriter->writeAttribute('w:type', 'dxa');
						$objWriter->endElement();
					}
					
					if($mLeft) {
						$objWriter->startElement('w:left');
							$objWriter->writeAttribute('w:w', $margins[1]);
							$objWriter->writeAttribute('w:type', 'dxa');
						$objWriter->endElement();
					}
					
					if($mRight) {
						$objWriter->startElement('w:right');
							$objWriter->writeAttribute('w:w', $margins[2]);
							$objWriter->writeAttribute('w:type', 'dxa');
						$objWriter->endElement();
					}
					
					if($mBottom) {
						$objWriter->startElement('w:bottom');
							$objWriter->writeAttribute('w:w', $margins[3]);
							$objWriter->writeAttribute('w:type', 'dxa');
						$objWriter->endElement();
					}
					
				$objWriter->endElement();
			$objWriter->endElement();
		}
	}
	
	protected function _writeCellStyle(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Style_Cell $style = null) {
		$bgColor = $style->getBgColor();
		$valign = $style->getVAlign();
		$textDir = $style->getTextDirection();
		$brdSz = $style->getBorderSize();
		$brdCol = $style->getBorderColor();
		
		$bTop = (!is_null($brdSz[0])) ? true : false;
		$bLeft = (!is_null($brdSz[1])) ? true : false;
		$bRight = (!is_null($brdSz[2])) ? true : false;
		$bBottom = (!is_null($brdSz[3])) ? true : false;
		$borders = ($bTop || $bLeft || $bRight || $bBottom) ? true : false;
		
		$styles = (!is_null($bgColor) || !is_null($valign) || !is_null($textDir) || $borders) ? true : false;
		
		if($styles) {
			if(!is_null($textDir)) {
				$objWriter->startElement('w:textDirection');
					$objWriter->writeAttribute('w:val', $textDir);
				$objWriter->endElement();
			}
			
			if(!is_null($bgColor)) {
				$objWriter->startElement('w:shd');
					$objWriter->writeAttribute('w:val', 'clear');
					$objWriter->writeAttribute('w:color', 'auto');
					$objWriter->writeAttribute('w:fill', $bgColor);
				$objWriter->endElement();
			}
			
			if(!is_null($valign)) {
				$objWriter->startElement('w:vAlign');
					$objWriter->writeAttribute('w:val', $valign);
				$objWriter->endElement();
			}
			
			if($borders) {
				$_defaultColor = $style->getDefaultBorderColor();
				
				$objWriter->startElement('w:tcBorders');
					if($bTop) {
						if(is_null($brdCol[0])) { $brdCol[0] = $_defaultColor; }
						$objWriter->startElement('w:top');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[0]);
							$objWriter->writeAttribute('w:color', $brdCol[0]);
						$objWriter->endElement();
					}
					
					if($bLeft) {
						if(is_null($brdCol[1])) { $brdCol[1] = $_defaultColor; }
						$objWriter->startElement('w:left');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[1]);
							$objWriter->writeAttribute('w:color', $brdCol[1]);
						$objWriter->endElement();
					}
					
					if($bRight) {
						if(is_null($brdCol[2])) { $brdCol[2] = $_defaultColor; }
						$objWriter->startElement('w:right');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[2]);
							$objWriter->writeAttribute('w:color', $brdCol[2]);
						$objWriter->endElement();
					}
					
					if($bBottom) {
						if(is_null($brdCol[3])) { $brdCol[3] = $_defaultColor; }
						$objWriter->startElement('w:bottom');
							$objWriter->writeAttribute('w:val', 'single');
							$objWriter->writeAttribute('w:sz', $brdSz[3]);
							$objWriter->writeAttribute('w:color', $brdCol[3]);
						$objWriter->endElement();
					}
					
				$objWriter->endElement();
			}
		}
	}
	
	protected function _writeImage(PHPWord_Shared_XMLWriter $objWriter = null, $image) {
		$rId = $image->getRelationId();
		
		$style = $image->getStyle();
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
			
				$objWriter->startElement('w:pict');
					
					$objWriter->startElement('v:shape');
						$objWriter->writeAttribute('type', '#_x0000_t75');
						$objWriter->writeAttribute('style', 'width:'.$width.'px;height:'.$height.'px');
						
						$objWriter->startElement('v:imagedata');
							$objWriter->writeAttribute('r:id', 'rId'.$rId);
							$objWriter->writeAttribute('o:title', '');
						$objWriter->endElement();
					$objWriter->endElement();
					
				$objWriter->endElement();
				
			$objWriter->endElement();
			
		$objWriter->endElement();
	}
	
	protected function _writeWatermark(PHPWord_Shared_XMLWriter $objWriter = null, $image) {
		$rId = $image->getRelationId();
		
		$style = $image->getStyle();
		$width = $style->getWidth();
		$height = $style->getHeight();
		$marginLeft = $style->getMarginLeft();
		$marginTop = $style->getMarginTop();
		
		$objWriter->startElement('w:p');
			
			$objWriter->startElement('w:r');
			
				$objWriter->startElement('w:pict');
					
					$objWriter->startElement('v:shape');
						$objWriter->writeAttribute('type', '#_x0000_t75');
						
						$strStyle = 'position:absolute;';
						$strStyle .= ' width:'.$width.'px;';
						$strStyle .= ' height:'.$height.'px;';
						if(!is_null($marginTop)) {
							$strStyle .= ' margin-top:'.$marginTop.'px;';
						}
						if(!is_null($marginLeft)) {
							$strStyle .= ' margin-left:'.$marginLeft.'px;';
						}
						
						$objWriter->writeAttribute('style', $strStyle);
						
						$objWriter->startElement('v:imagedata');
							$objWriter->writeAttribute('r:id', 'rId'.$rId);
							$objWriter->writeAttribute('o:title', '');
						$objWriter->endElement();
					$objWriter->endElement();
					
				$objWriter->endElement();
				
			$objWriter->endElement();
			
		$objWriter->endElement();
	}
	
	protected function _writeTitle(PHPWord_Shared_XMLWriter $objWriter = null, PHPWord_Section_Title $title) {
		$text = htmlspecialchars($title->getText());
		$text = PHPWord_Shared_String::ControlCharacterPHP2OOXML($text);
		$anchor = $title->getAnchor();
		$bookmarkId = $title->getBookmarkId();
		$style = $title->getStyle();
		
		$objWriter->startElement('w:p');
			
			if(!empty($style)) {
				$objWriter->startElement('w:pPr');
					$objWriter->startElement('w:pStyle');
						$objWriter->writeAttribute('w:val', $style);
					$objWriter->endElement();
				$objWriter->endElement();
			}
			
			$objWriter->startElement('w:r');
				$objWriter->startElement('w:fldChar');
					$objWriter->writeAttribute('w:fldCharType', 'end');
				$objWriter->endElement();
			$objWriter->endElement();
			
			$objWriter->startElement('w:bookmarkStart');
				$objWriter->writeAttribute('w:id', $bookmarkId);
				$objWriter->writeAttribute('w:name', $anchor);
			$objWriter->endElement();
			
			$objWriter->startElement('w:r');
				$objWriter->startElement('w:t');
					$objWriter->writeRaw($text);
				$objWriter->endElement();
			$objWriter->endElement();
			
			$objWriter->startElement('w:bookmarkEnd');
				$objWriter->writeAttribute('w:id', $bookmarkId);
			$objWriter->endElement();
			
		$objWriter->endElement();
	}
}
?>