<?php
/*******************************************************************************
* FPDF                                                                         *
*                                                                              *
* Version: 1.6                                                                 *
* Date:    2008-08-03                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

define('FPDF_VERSION','1.6');

class fpdf
{
var $page;               //current page number
var $n;                  //current object number
var $offsets;            //array of object offsets
var $buffer;             //buffer holding in-memory PDF
var $pages;              //array containing pages
var $state;              //current document state
var $compress;           //compression flag
var $k;                  //scale factor (number of points in user unit)
var $DefOrientation;     //default orientation
var $CurOrientation;     //current orientation
var $PageFormats;        //available page formats
var $DefPageFormat;      //default page format
var $CurPageFormat;      //current page format
var $PageSizes;          //array storing non-default page sizes
var $wPt,$hPt;           //dimensions of current page in points
var $w,$h;               //dimensions of current page in user unit
var $lMargin;            //left margin
var $tMargin;            //top margin
var $rMargin;            //right margin
var $bMargin;            //page break margin
var $cMargin;            //cell margin
var $x,$y;               //current position in user unit
var $lasth;              //height of last printed cell
var $LineWidth;          //line width in user unit
var $CoreFonts;          //array of standard font names
var $fonts;              //array of used fonts
var $FontFiles;          //array of font files
var $diffs;              //array of encoding differences
var $FontFamily;         //current font family
var $FontStyle;          //current font style
var $underline;          //underlining flag
var $CurrentFont;        //current font info
var $FontSizePt;         //current font size in points
var $FontSize;           //current font size in user unit
var $DrawColor;          //commands for drawing color
var $FillColor;          //commands for filling color
var $TextColor;          //commands for text color
var $ColorFlag;          //indicates whether fill and text colors are different
var $ws;                 //word spacing
var $images;             //array of used images
var $PageLinks;          //array of links in pages
var $links;              //array of internal links
var $AutoPageBreak;      //automatic page breaking
var $PageBreakTrigger;   //threshold used to trigger page breaks
var $InHeader;           //flag set when processing header
var $InFooter;           //flag set when processing footer
var $ZoomMode;           //zoom display mode
var $LayoutMode;         //layout display mode
var $title;              //title
var $subject;            //subject
var $author;             //author
var $keywords;           //keywords
var $creator;            //creator
var $AliasNbPages;       //alias for total number of pages
var $PDFVersion;         //PDF version number

/*******************************************************************************
*                                                                              *
*                               Public methods                                 *
*                                                                              *
*******************************************************************************/
function fpdf($orientation='P', $unit='mm', $format='A4')
{
	//Some checks
	$this->_dochecks();
	//Initialization of properties
	$this->page=0;
	$this->n=2;
	$this->buffer='';
	$this->pages=array();
	$this->PageSizes=array();
	$this->state=0;
	$this->fonts=array();
	$this->FontFiles=array();
	$this->diffs=array();
	$this->images=array();
	$this->links=array();
	$this->InHeader=false;
	$this->InFooter=false;
	$this->lasth=0;
	$this->FontFamily='';
	$this->FontStyle='';
	$this->FontSizePt=12;
	$this->underline=false;
	$this->DrawColor='0 G';
	$this->FillColor='0 g';
	$this->TextColor='0 g';
	$this->ColorFlag=false;
	$this->ws=0;
	//Standard fonts
	$this->CoreFonts=array('courier'=>'Courier', 'courierB'=>'Courier-Bold', 'courierI'=>'Courier-Oblique', 'courierBI'=>'Courier-BoldOblique',
		'helvetica'=>'Helvetica', 'helveticaB'=>'Helvetica-Bold', 'helveticaI'=>'Helvetica-Oblique', 'helveticaBI'=>'Helvetica-BoldOblique',
		'times'=>'Times-Roman', 'timesB'=>'Times-Bold', 'timesI'=>'Times-Italic', 'timesBI'=>'Times-BoldItalic',
		'symbol'=>'Symbol', 'zapfdingbats'=>'ZapfDingbats');
	//Scale factor
	if($unit=='pt')
		$this->k=1;
	elseif($unit=='mm')
		$this->k=72/25.4;
	elseif($unit=='cm')
		$this->k=72/2.54;
	elseif($unit=='in')
		$this->k=72;
	else
		$this->Error('Incorrect unit: '.$unit);
	//Page format
	$this->PageFormats=array('a3'=>array(841.89,1190.55), 'a4'=>array(595.28,841.89), 'a5'=>array(420.94,595.28),
		'letter'=>array(612,792), 'legal'=>array(612,1008));
	if(is_string($format))
		$format=$this->_getpageformat($format);
	$this->DefPageFormat=$format;
	$this->CurPageFormat=$format;
	//Page orientation
	$orientation=strtolower($orientation);
	if($orientation=='p' || $orientation=='portrait')
	{
		$this->DefOrientation='P';
		$this->w=$this->DefPageFormat[0];
		$this->h=$this->DefPageFormat[1];
	}
	elseif($orientation=='l' || $orientation=='landscape')
	{
		$this->DefOrientation='L';
		$this->w=$this->DefPageFormat[1];
		$this->h=$this->DefPageFormat[0];
	}
	else
		$this->Error('Incorrect orientation: '.$orientation);
	$this->CurOrientation=$this->DefOrientation;
	$this->wPt=$this->w*$this->k;
	$this->hPt=$this->h*$this->k;
	//Page margins (1 cm)
	$margin=28.35/$this->k;
	$this->SetMargins($margin,$margin);
	//Interior cell margin (1 mm)
	$this->cMargin=$margin/10;
	//Line width (0.2 mm)
	$this->LineWidth=.567/$this->k;
	//Automatic page break
	$this->SetAutoPageBreak(true,10);
	//Full width display mode
	$this->SetDisplayMode('fullwidth');
	//Enable compression
	$this->SetCompression(true);
	//Set default PDF version number
	$this->PDFVersion='1.3';
}

function SetMargins($left, $top, $right=null)
{
	//Set left, top and right margins
	$this->lMargin=$left;
	$this->tMargin=$top;
	if($right===null)
		$right=$left;
	$this->rMargin=$right;
}

function SetLeftMargin($margin)
{
	//Set left margin
	$this->lMargin=$margin;
	if($this->page>0 && $this->x<$margin)
		$this->x=$margin;
}

function SetTopMargin($margin)
{
	//Set top margin
	$this->tMargin=$margin;
}

function SetRightMargin($margin)
{
	//Set right margin
	$this->rMargin=$margin;
}

function SetAutoPageBreak($auto, $margin=0)
{
	//Set auto page break mode and triggering margin
	$this->AutoPageBreak=$auto;
	$this->bMargin=$margin;
	$this->PageBreakTrigger=$this->h-$margin;
}

function SetDisplayMode($zoom, $layout='continuous')
{
	//Set display mode in viewer
	if($zoom=='fullpage' || $zoom=='fullwidth' || $zoom=='real' || $zoom=='default' || !is_string($zoom))
		$this->ZoomMode=$zoom;
	else
		$this->Error('Incorrect zoom display mode: '.$zoom);
	if($layout=='single' || $layout=='continuous' || $layout=='two' || $layout=='default')
		$this->LayoutMode=$layout;
	else
		$this->Error('Incorrect layout display mode: '.$layout);
}

function SetCompression($compress)
{
	//Set page compression
	if(function_exists('gzcompress'))
		$this->compress=$compress;
	else
		$this->compress=false;
}

function SetTitle($title, $isUTF8=false)
{
	//Title of document
	if($isUTF8)
		$title=$this->_UTF8toUTF16($title);
	$this->title=$title;
}

function SetSubject($subject, $isUTF8=false)
{
	//Subject of document
	if($isUTF8)
		$subject=$this->_UTF8toUTF16($subject);
	$this->subject=$subject;
}

function SetAuthor($author, $isUTF8=false)
{
	//Author of document
	if($isUTF8)
		$author=$this->_UTF8toUTF16($author);
	$this->author=$author;
}

function SetKeywords($keywords, $isUTF8=false)
{
	//Keywords of document
	if($isUTF8)
		$keywords=$this->_UTF8toUTF16($keywords);
	$this->keywords=$keywords;
}

function SetCreator($creator, $isUTF8=false)
{
	//Creator of document
	if($isUTF8)
		$creator=$this->_UTF8toUTF16($creator);
	$this->creator=$creator;
}

function AliasNbPages($alias='{nb}')
{
	//Define an alias for total number of pages
	$this->AliasNbPages=$alias;
}

function Error($msg)
{
	//Fatal error
	die('<b>fpdf error:</b> '.$msg);
}

function Open()
{
	//Begin document
	$this->state=1;
}

function Close()
{
	//Terminate document
	if($this->state==3)
		return;
	if($this->page==0)
		$this->AddPage();
	//Page footer
	$this->InFooter=true;
	$this->Footer();
	$this->InFooter=false;
	//Close page
	$this->_endpage();
	//Close document
	$this->_enddoc();
}

function AddPage($orientation='', $format='')
{
	//Start a new page
	if($this->state==0)
		$this->Open();
	$family=$this->FontFamily;
	$style=$this->FontStyle.($this->underline ? 'U' : '');
	$size=$this->FontSizePt;
	$lw=$this->LineWidth;
	$dc=$this->DrawColor;
	$fc=$this->FillColor;
	$tc=$this->TextColor;
	$cf=$this->ColorFlag;
	if($this->page>0)
	{
		//Page footer
		$this->InFooter=true;
		$this->Footer();
		$this->InFooter=false;
		//Close page
		$this->_endpage();
	}
	//Start new page
	$this->_beginpage($orientation,$format);
	//Set line cap style to square
	$this->_out('2 J');
	//Set line width
	$this->LineWidth=$lw;
	$this->_out(sprintf('%.2F w',$lw*$this->k));
	//Set font
	if($family)
		$this->SetFont($family,$style,$size);
	//Set colors
	$this->DrawColor=$dc;
	if($dc!='0 G')
		$this->_out($dc);
	$this->FillColor=$fc;
	if($fc!='0 g')
		$this->_out($fc);
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
	//Page header
	$this->InHeader=true;
	$this->Header();
	$this->InHeader=false;
	//Restore line width
	if($this->LineWidth!=$lw)
	{
		$this->LineWidth=$lw;
		$this->_out(sprintf('%.2F w',$lw*$this->k));
	}
	//Restore font
	if($family)
		$this->SetFont($family,$style,$size);
	//Restore colors
	if($this->DrawColor!=$dc)
	{
		$this->DrawColor=$dc;
		$this->_out($dc);
	}
	if($this->FillColor!=$fc)
	{
		$this->FillColor=$fc;
		$this->_out($fc);
	}
	$this->TextColor=$tc;
	$this->ColorFlag=$cf;
}

function Header()
{
	//To be implemented in your own inherited class
}

function Footer()
{
	//To be implemented in your own inherited class
}

function PageNo()
{
	//Get current page number
	return $this->page;
}

function SetDrawColor($r, $g=null, $b=null)
{
	//Set color for all stroking operations
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->DrawColor=sprintf('%.3F G',$r/255);
	else
		$this->DrawColor=sprintf('%.3F %.3F %.3F RG',$r/255,$g/255,$b/255);
	if($this->page>0)
		$this->_out($this->DrawColor);
}

function SetFillColor($r, $g=null, $b=null)
{
	//Set color for all filling operations
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->FillColor=sprintf('%.3F g',$r/255);
	else
		$this->FillColor=sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->ColorFlag=($this->FillColor!=$this->TextColor);
	if($this->page>0)
		$this->_out($this->FillColor);
}

function SetTextColor($r, $g=null, $b=null)
{
	//Set color for text
	if(($r==0 && $g==0 && $b==0) || $g===null)
		$this->TextColor=sprintf('%.3F g',$r/255);
	else
		$this->TextColor=sprintf('%.3F %.3F %.3F rg',$r/255,$g/255,$b/255);
	$this->ColorFlag=($this->FillColor!=$this->TextColor);
}

function GetStringWidth($s)
{
	//Get width of a string in the current font
	$s=(string)$s;
	$cw=&$this->CurrentFont['cw'];
	$w=0;
	$l=strlen($s);
	for($i=0;$i<$l;$i++)
		$w+=$cw[$s[$i]];
	return $w*$this->FontSize/1000;
}

function SetLineWidth($width)
{
	//Set line width
	$this->LineWidth=$width;
	if($this->page>0)
		$this->_out(sprintf('%.2F w',$width*$this->k));
}

function Line($x1, $y1, $x2, $y2)
{
	//Draw a line
	$this->_out(sprintf('%.2F %.2F m %.2F %.2F l S',$x1*$this->k,($this->h-$y1)*$this->k,$x2*$this->k,($this->h-$y2)*$this->k));
}

function Rect($x, $y, $w, $h, $style='')
{
	//Draw a rectangle
	if($style=='F')
		$op='f';
	elseif($style=='FD' || $style=='DF')
		$op='B';
	else
		$op='S';
	$this->_out(sprintf('%.2F %.2F %.2F %.2F re %s',$x*$this->k,($this->h-$y)*$this->k,$w*$this->k,-$h*$this->k,$op));
}

function AddFont($family, $style='', $file='')
{
	//Add a TrueType or Type1 font
	$family=strtolower($family);
	if($file=='')
		$file=str_replace(' ','',$family).strtolower($style).'.php';
	if($family=='arial')
		$family='helvetica';
	$style=strtoupper($style);
	if($style=='IB')
		$style='BI';
	$fontkey=$family.$style;
	if(isset($this->fonts[$fontkey]))
		return;
	include($this->_getfontpath().$file);
	if(!isset($name))
		$this->Error('Could not include font definition file');
	$i=count($this->fonts)+1;
	$this->fonts[$fontkey]=array('i'=>$i, 'type'=>$type, 'name'=>$name, 'desc'=>$desc, 'up'=>$up, 'ut'=>$ut, 'cw'=>$cw, 'enc'=>$enc, 'file'=>$file);
	if($diff)
	{
		//Search existing encodings
		$d=0;
		$nb=count($this->diffs);
		for($i=1;$i<=$nb;$i++)
		{
			if($this->diffs[$i]==$diff)
			{
				$d=$i;
				break;
			}
		}
		if($d==0)
		{
			$d=$nb+1;
			$this->diffs[$d]=$diff;
		}
		$this->fonts[$fontkey]['diff']=$d;
	}
	if($file)
	{
		if($type=='TrueType')
			$this->FontFiles[$file]=array('length1'=>$originalsize);
		else
			$this->FontFiles[$file]=array('length1'=>$size1, 'length2'=>$size2);
	}
}

function SetFont($family, $style='', $size=0)
{
	//Select a font; size given in points
	global $fpdf_charwidths;

	$family=strtolower($family);
	if($family=='')
		$family=$this->FontFamily;
	if($family=='arial')
		$family='helvetica';
	elseif($family=='symbol' || $family=='zapfdingbats')
		$style='';
	$style=strtoupper($style);
	if(strpos($style,'U')!==false)
	{
		$this->underline=true;
		$style=str_replace('U','',$style);
	}
	else
		$this->underline=false;
	if($style=='IB')
		$style='BI';
	if($size==0)
		$size=$this->FontSizePt;
	//Test if font is already selected
	if($this->FontFamily==$family && $this->FontStyle==$style && $this->FontSizePt==$size)
		return;
	//Test if used for the first time
	$fontkey=$family.$style;
	if(!isset($this->fonts[$fontkey]))
	{
		//Check if one of the standard fonts
		if(isset($this->CoreFonts[$fontkey]))
		{
			if(!isset($fpdf_charwidths[$fontkey]))
			{
				//Load metric file
				$file=$family;
				if($family=='times' || $family=='helvetica')
					$file.=strtolower($style);
				include($this->_getfontpath().$file.'.php');
				if(!isset($fpdf_charwidths[$fontkey]))
					$this->Error('Could not include font metric file');
			}
			$i=count($this->fonts)+1;
			$name=$this->CoreFonts[$fontkey];
			$cw=$fpdf_charwidths[$fontkey];
			$this->fonts[$fontkey]=array('i'=>$i, 'type'=>'core', 'name'=>$name, 'up'=>-100, 'ut'=>50, 'cw'=>$cw);
		}
		else
			$this->Error('Undefined font: '.$family.' '.$style);
	}
	//Select it
	$this->FontFamily=$family;
	$this->FontStyle=$style;
	$this->FontSizePt=$size;
	$this->FontSize=$size/$this->k;
	$this->CurrentFont=&$this->fonts[$fontkey];
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

function SetFontSize($size)
{
	//Set font size in points
	if($this->FontSizePt==$size)
		return;
	$this->FontSizePt=$size;
	$this->FontSize=$size/$this->k;
	if($this->page>0)
		$this->_out(sprintf('BT /F%d %.2F Tf ET',$this->CurrentFont['i'],$this->FontSizePt));
}

function AddLink()
{
	//Create a new internal link
	$n=count($this->links)+1;
	$this->links[$n]=array(0, 0);
	return $n;
}

function SetLink($link, $y=0, $page=-1)
{
	//Set destination of internal link
	if($y==-1)
		$y=$this->y;
	if($page==-1)
		$page=$this->page;
	$this->links[$link]=array($page, $y);
}

function Link($x, $y, $w, $h, $link)
{
	//Put a link on the page
	$this->PageLinks[$this->page][]=array($x*$this->k, $this->hPt-$y*$this->k, $w*$this->k, $h*$this->k, $link);
}

function Text($x, $y, $txt)
{
	//Output a string
	$s=sprintf('BT %.2F %.2F Td (%s) Tj ET',$x*$this->k,($this->h-$y)*$this->k,$this->_escape($txt));
	if($this->underline && $txt!='')
		$s.=' '.$this->_dounderline($x,$y,$txt);
	if($this->ColorFlag)
		$s='q '.$this->TextColor.' '.$s.' Q';
	$this->_out($s);
}

function AcceptPageBreak()
{
	//Accept automatic page break or not
	return $this->AutoPageBreak;
}

function Cell($w, $h=0, $txt='', $border=0, $ln=0, $align='', $fill=false, $link='')
{
	//Output a cell
	$k=$this->k;
	if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
	{
		//Automatic page break
		$x=$this->x;
		$ws=$this->ws;
		if($ws>0)
		{
			$this->ws=0;
			$this->_out('0 Tw');
		}
		$this->AddPage($this->CurOrientation,$this->CurPageFormat);
		$this->x=$x;
		if($ws>0)
		{
			$this->ws=$ws;
			$this->_out(sprintf('%.3F Tw',$ws*$k));
		}
	}
	if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
	$s='';
	if($fill || $border==1)
	{
		if($fill)
			$op=($border==1) ? 'B' : 'f';
		else
			$op='S';
		$s=sprintf('%.2F %.2F %.2F %.2F re %s ',$this->x*$k,($this->h-$this->y)*$k,$w*$k,-$h*$k,$op);
	}
	if(is_string($border))
	{
		$x=$this->x;
		$y=$this->y;
		if(strpos($border,'L')!==false)
			$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,$x*$k,($this->h-($y+$h))*$k);
		if(strpos($border,'T')!==false)
			$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-$y)*$k);
		if(strpos($border,'R')!==false)
			$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',($x+$w)*$k,($this->h-$y)*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
		if(strpos($border,'B')!==false)
			$s.=sprintf('%.2F %.2F m %.2F %.2F l S ',$x*$k,($this->h-($y+$h))*$k,($x+$w)*$k,($this->h-($y+$h))*$k);
	}
	if($txt!=='')
	{
		if($align=='R')
			$dx=$w-$this->cMargin-$this->GetStringWidth($txt);
		elseif($align=='C')
			$dx=($w-$this->GetStringWidth($txt))/2;
		else
			$dx=$this->cMargin;
		if($this->ColorFlag)
			$s.='q '.$this->TextColor.' ';
		$txt2=str_replace(')','\\)',str_replace('(','\\(',str_replace('\\','\\\\',$txt)));
		$s.=sprintf('BT %.2F %.2F Td (%s) Tj ET',($this->x+$dx)*$k,($this->h-($this->y+.5*$h+.3*$this->FontSize))*$k,$txt2);
		if($this->underline)
			$s.=' '.$this->_dounderline($this->x+$dx,$this->y+.5*$h+.3*$this->FontSize,$txt);
		if($this->ColorFlag)
			$s.=' Q';
		if($link)
			$this->Link($this->x+$dx,$this->y+.5*$h-.5*$this->FontSize,$this->GetStringWidth($txt),$this->FontSize,$link);
	}
	if($s)
		$this->_out($s);
	$this->lasth=$h;
	if($ln>0)
	{
		//Go to next line
		$this->y+=$h;
		if($ln==1)
			$this->x=$this->lMargin;
	}
	else
		$this->x+=$w;
}

function MultiCell($w, $h, $txt, $border=0, $align='J', $fill=false)
{
	//Output text with automatic or explicit line breaks
	$cw=&$this->CurrentFont['cw'];
	if($w==0)
		$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	if($nb>0 && $s[$nb-1]=="\n")
		$nb--;
	$b=0;
	if($border)
	{
		if($border==1)
		{
			$border='LTRB';
			$b='LRT';
			$b2='LR';
		}
		else
		{
			$b2='';
			if(strpos($border,'L')!==false)
				$b2.='L';
			if(strpos($border,'R')!==false)
				$b2.='R';
			$b=(strpos($border,'T')!==false) ? $b2.'T' : $b2;
		}
	}
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$ns=0;
	$nl=1;
	while($i<$nb)
	{
		//Get next character
		$c=$s[$i];
		if($c=="\n")
		{
			//Explicit line break
			if($this->ws>0)
			{
				$this->ws=0;
				$this->_out('0 Tw');
			}
			$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border && $nl==2)
				$b=$b2;
			continue;
		}
		if($c==' ')
		{
			$sep=$i;
			$ls=$l;
			$ns++;
		}
		$l+=$cw[$c];
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1)
			{
				if($i==$j)
					$i++;
				if($this->ws>0)
				{
					$this->ws=0;
					$this->_out('0 Tw');
				}
				$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
			}
			else
			{
				if($align=='J')
				{
					$this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
					$this->_out(sprintf('%.3F Tw',$this->ws*$this->k));
				}
				$this->Cell($w,$h,substr($s,$j,$sep-$j),$b,2,$align,$fill);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			$ns=0;
			$nl++;
			if($border && $nl==2)
				$b=$b2;
		}
		else
			$i++;
	}
	//Last chunk
	if($this->ws>0)
	{
		$this->ws=0;
		$this->_out('0 Tw');
	}
	if($border && strpos($border,'B')!==false)
		$b.='B';
	$this->Cell($w,$h,substr($s,$j,$i-$j),$b,2,$align,$fill);
	$this->x=$this->lMargin;
}

function Write($h, $txt, $link='')
{
	//Output text in flowing mode
	$cw=&$this->CurrentFont['cw'];
	$w=$this->w-$this->rMargin-$this->x;
	$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
	$s=str_replace("\r",'',$txt);
	$nb=strlen($s);
	$sep=-1;
	$i=0;
	$j=0;
	$l=0;
	$nl=1;
	while($i<$nb)
	{
		//Get next character
		$c=$s[$i];
		if($c=="\n")
		{
			//Explicit line break
			$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
			$i++;
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
			continue;
		}
		if($c==' ')
			$sep=$i;
		$l+=$cw[$c];
		if($l>$wmax)
		{
			//Automatic line break
			if($sep==-1)
			{
				if($this->x>$this->lMargin)
				{
					//Move to next line
					$this->x=$this->lMargin;
					$this->y+=$h;
					$w=$this->w-$this->rMargin-$this->x;
					$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
					$i++;
					$nl++;
					continue;
				}
				if($i==$j)
					$i++;
				$this->Cell($w,$h,substr($s,$j,$i-$j),0,2,'',0,$link);
			}
			else
			{
				$this->Cell($w,$h,substr($s,$j,$sep-$j),0,2,'',0,$link);
				$i=$sep+1;
			}
			$sep=-1;
			$j=$i;
			$l=0;
			if($nl==1)
			{
				$this->x=$this->lMargin;
				$w=$this->w-$this->rMargin-$this->x;
				$wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
			}
			$nl++;
		}
		else
			$i++;
	}
	//Last chunk
	if($i!=$j)
		$this->Cell($l/1000*$this->FontSize,$h,substr($s,$j),0,0,'',0,$link);
}

function Ln($h=null)
{
	//Line feed; default value is last cell height
	$this->x=$this->lMargin;
	if($h===null)
		$this->y+=$this->lasth;
	else
		$this->y+=$h;
}

function Image($file, $x=null, $y=null, $w=0, $h=0, $type='', $link='')
{
	//Put an image on the page
	if(!isset($this->images[$file]))
	{
		//First use of this image, get info
		if($type=='')
		{
			$pos=strrpos($file,'.');
			if(!$pos)
				$this->Error('Image file has no extension and no type was specified: '.$file);
			$type=substr($file,$pos+1);
		}
		$type=strtolower($type);
		if($type=='jpeg')
			$type='jpg';
		$mtd='_parse'.$type;
		if(!method_exists($this,$mtd))
			$this->Error('Unsupported image type: '.$type);
		$info=$this->$mtd($file);
		$info['i']=count($this->images)+1;
		$this->images[$file]=$info;
	}
	else
		$info=$this->images[$file];
	//Automatic width and height calculation if needed
	if($w==0 && $h==0)
	{
		//Put image at 72 dpi
		$w=$info['w']/$this->k;
		$h=$info['h']/$this->k;
	}
	elseif($w==0)
		$w=$h*$info['w']/$info['h'];
	elseif($h==0)
		$h=$w*$info['h']/$info['w'];
	//Flowing mode
	if($y===null)
	{
		if($this->y+$h>$this->PageBreakTrigger && !$this->InHeader && !$this->InFooter && $this->AcceptPageBreak())
		{
			//Automatic page break
			$x2=$this->x;
			$this->AddPage($this->CurOrientation,$this->CurPageFormat);
			$this->x=$x2;
		}
		$y=$this->y;
		$this->y+=$h;
	}
	if($x===null)
		$x=$this->x;
	$this->_out(sprintf('q %.2F 0 0 %.2F %.2F %.2F cm /I%d Do Q',$w*$this->k,$h*$this->k,$x*$this->k,($this->h-($y+$h))*$this->k,$info['i']));
	if($link)
		$this->Link($x,$y,$w,$h,$link);
}

function GetX()
{
	//Get x position
	return $this->x;
}

function SetX($x)
{
	//Set x position
	if($x>=0)
		$this->x=$x;
	else
		$this->x=$this->w+$x;
}

function GetY()
{
	//Get y position
	return $this->y;
}

function SetY($y)
{
	//Set y position and reset x
	$this->x=$this->lMargin;
	if($y>=0)
		$this->y=$y;
	else
		$this->y=$this->h+$y;
}

function SetXY($x, $y)
{
	//Set x and y positions
	$this->SetY($y);
	$this->SetX($x);
}

function Output($name='', $dest='')
{
	//Output PDF to some destination
	if($this->state<3)
		$this->Close();
	$dest=strtoupper($dest);
	if($dest=='')
	{
		if($name=='')
		{
			$name='doc.pdf';
			$dest='I';
		}
		else
			$dest='F';
	}
	switch($dest)
	{
		case 'I':
			//Send to standard output
			if(ob_get_length())
				$this->Error('Some data has already been output, can\'t send PDF file');
			if(php_sapi_name()!='cli')
			{
				//We send to a browser
				header('Content-Type: application/pdf');
				if(headers_sent())
					$this->Error('Some data has already been output, can\'t send PDF file');
				header('Content-Length: '.strlen($this->buffer));
				header('Content-Disposition: inline; filename="'.$name.'"');
				header('Cache-Control: private, max-age=0, must-revalidate');
				header('Pragma: public');
				ini_set('zlib.output_compression','0');
			}
			echo $this->buffer;
			break;
		case 'D':
			//Download file
			if(ob_get_length())
				$this->Error('Some data has already been output, can\'t send PDF file');
			header('Content-Type: application/x-download');
			if(headers_sent())
				$this->Error('Some data has already been output, can\'t send PDF file');
			header('Content-Length: '.strlen($this->buffer));
			header('Content-Disposition: attachment; filename="'.$name.'"');
			header('Cache-Control: private, max-age=0, must-revalidate');
			header('Pragma: public');
			ini_set('zlib.output_compression','0');
			echo $this->buffer;
			break;
		case 'F':
			//Save to local file
			$f=fopen($name,'wb');
			if(!$f)
				$this->Error('Unable to create output file: '.$name);
			fwrite($f,$this->buffer,strlen($this->buffer));
			fclose($f);
			break;
		case 'S':
			//Return as a string
			return $this->buffer;
		default:
			$this->Error('Incorrect output destination: '.$dest);
	}
	return '';
}

/*******************************************************************************
*                                                                              *
*                              Protected methods                               *
*                                                                              *
*******************************************************************************/
function _dochecks()
{
	//Check availability of %F
	if(sprintf('%.1F',1.0)!='1.0')
		$this->Error('This version of PHP is not supported');
	//Check mbstring overloading
	if(ini_get('mbstring.func_overload') & 2)
		$this->Error('mbstring overloading must be disabled');
	//Disable runtime magic quotes
	if(get_magic_quotes_runtime())
		@set_magic_quotes_runtime(0);
}

function _getpageformat($format)
{
	$format=strtolower($format);
	if(!isset($this->PageFormats[$format]))
		$this->Error('Unknown page format: '.$format);
	$a=$this->PageFormats[$format];
	return array($a[0]/$this->k, $a[1]/$this->k);
}

function _getfontpath()
{
	if(!defined('fpdf_FONTPATH') && is_dir(dirname(__FILE__).'/font'))
		define('fpdf_FONTPATH',dirname(__FILE__).'/font/');
	return defined('fpdf_FONTPATH') ? fpdf_FONTPATH : '';
}

function _beginpage($orientation, $format)
{
	$this->page++;
	$this->pages[$this->page]='';
	$this->state=2;
	$this->x=$this->lMargin;
	$this->y=$this->tMargin;
	$this->FontFamily='';
	//Check page size
	if($orientation=='')
		$orientation=$this->DefOrientation;
	else
		$orientation=strtoupper($orientation[0]);
	if($format=='')
		$format=$this->DefPageFormat;
	else
	{
		if(is_string($format))
			$format=$this->_getpageformat($format);
	}
	if($orientation!=$this->CurOrientation || $format[0]!=$this->CurPageFormat[0] || $format[1]!=$this->CurPageFormat[1])
	{
		//New size
		if($orientation=='P')
		{
			$this->w=$format[0];
			$this->h=$format[1];
		}
		else
		{
			$this->w=$format[1];
			$this->h=$format[0];
		}
		$this->wPt=$this->w*$this->k;
		$this->hPt=$this->h*$this->k;
		$this->PageBreakTrigger=$this->h-$this->bMargin;
		$this->CurOrientation=$orientation;
		$this->CurPageFormat=$format;
	}
	if($orientation!=$this->DefOrientation || $format[0]!=$this->DefPageFormat[0] || $format[1]!=$this->DefPageFormat[1])
		$this->PageSizes[$this->page]=array($this->wPt, $this->hPt);
}

function _endpage()
{
	$this->state=1;
}

function _escape($s)
{
	//Escape special characters in strings
	$s=str_replace('\\','\\\\',$s);
	$s=str_replace('(','\\(',$s);
	$s=str_replace(')','\\)',$s);
	$s=str_replace("\r",'\\r',$s);
	return $s;
}

function _textstring($s)
{
	//Format a text string
	return '('.$this->_escape($s).')';
}

function _UTF8toUTF16($s)
{
	//Convert UTF-8 to UTF-16BE with BOM
	$res="\xFE\xFF";
	$nb=strlen($s);
	$i=0;
	while($i<$nb)
	{
		$c1=ord($s[$i++]);
		if($c1>=224)
		{
			//3-byte character
			$c2=ord($s[$i++]);
			$c3=ord($s[$i++]);
			$res.=chr((($c1 & 0x0F)<<4) + (($c2 & 0x3C)>>2));
			$res.=chr((($c2 & 0x03)<<6) + ($c3 & 0x3F));
		}
		elseif($c1>=192)
		{
			//2-byte character
			$c2=ord($s[$i++]);
			$res.=chr(($c1 & 0x1C)>>2);
			$res.=chr((($c1 & 0x03)<<6) + ($c2 & 0x3F));
		}
		else
		{
			//Single-byte character
			$res.="\0".chr($c1);
		}
	}
	return $res;
}

function _dounderline($x, $y, $txt)
{
	//Underline text
	$up=$this->CurrentFont['up'];
	$ut=$this->CurrentFont['ut'];
	$w=$this->GetStringWidth($txt)+$this->ws*substr_count($txt,' ');
	return sprintf('%.2F %.2F %.2F %.2F re f',$x*$this->k,($this->h-($y-$up/1000*$this->FontSize))*$this->k,$w*$this->k,-$ut/1000*$this->FontSizePt);
}

function _parsejpg($file)
{
	//Extract info from a JPEG file
	$a=GetImageSize($file);
	if(!$a)
		$this->Error('Missing or incorrect image file: '.$file);
	if($a[2]!=2)
		$this->Error('Not a JPEG file: '.$file);
	if(!isset($a['channels']) || $a['channels']==3)
		$colspace='DeviceRGB';
	elseif($a['channels']==4)
		$colspace='DeviceCMYK';
	else
		$colspace='DeviceGray';
	$bpc=isset($a['bits']) ? $a['bits'] : 8;
	//Read whole file
	$f=fopen($file,'rb');
	$data='';
	while(!feof($f))
		$data.=fread($f,8192);
	fclose($f);
	return array('w'=>$a[0], 'h'=>$a[1], 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'DCTDecode', 'data'=>$data);
}

function _parsepng($file)
{
	//Extract info from a PNG file
	$f=fopen($file,'rb');
	if(!$f)
		$this->Error('Can\'t open image file: '.$file);
	//Check signature
	if($this->_readstream($f,8)!=chr(137).'PNG'.chr(13).chr(10).chr(26).chr(10))
		$this->Error('Not a PNG file: '.$file);
	//Read header chunk
	$this->_readstream($f,4);
	if($this->_readstream($f,4)!='IHDR')
		$this->Error('Incorrect PNG file: '.$file);
	$w=$this->_readint($f);
	$h=$this->_readint($f);
	$bpc=ord($this->_readstream($f,1));
	if($bpc>8)
		$this->Error('16-bit depth not supported: '.$file);
	$ct=ord($this->_readstream($f,1));
	if($ct==0)
		$colspace='DeviceGray';
	elseif($ct==2)
		$colspace='DeviceRGB';
	elseif($ct==3)
		$colspace='Indexed';
	else
		$this->Error('Alpha channel not supported: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Unknown compression method: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Unknown filter method: '.$file);
	if(ord($this->_readstream($f,1))!=0)
		$this->Error('Interlacing not supported: '.$file);
	$this->_readstream($f,4);
	$parms='/DecodeParms <</Predictor 15 /Colors '.($ct==2 ? 3 : 1).' /BitsPerComponent '.$bpc.' /Columns '.$w.'>>';
	//Scan chunks looking for palette, transparency and image data
	$pal='';
	$trns='';
	$data='';
	do
	{
		$n=$this->_readint($f);
		$type=$this->_readstream($f,4);
		if($type=='PLTE')
		{
			//Read palette
			$pal=$this->_readstream($f,$n);
			$this->_readstream($f,4);
		}
		elseif($type=='tRNS')
		{
			//Read transparency info
			$t=$this->_readstream($f,$n);
			if($ct==0)
				$trns=array(ord(substr($t,1,1)));
			elseif($ct==2)
				$trns=array(ord(substr($t,1,1)), ord(substr($t,3,1)), ord(substr($t,5,1)));
			else
			{
				$pos=strpos($t,chr(0));
				if($pos!==false)
					$trns=array($pos);
			}
			$this->_readstream($f,4);
		}
		elseif($type=='IDAT')
		{
			//Read image data block
			$data.=$this->_readstream($f,$n);
			$this->_readstream($f,4);
		}
		elseif($type=='IEND')
			break;
		else
			$this->_readstream($f,$n+4);
	}
	while($n);
	if($colspace=='Indexed' && empty($pal))
		$this->Error('Missing palette in '.$file);
	fclose($f);
	return array('w'=>$w, 'h'=>$h, 'cs'=>$colspace, 'bpc'=>$bpc, 'f'=>'FlateDecode', 'parms'=>$parms, 'pal'=>$pal, 'trns'=>$trns, 'data'=>$data);
}

function _readstream($f, $n)
{
	//Read n bytes from stream
	$res='';
	while($n>0 && !feof($f))
	{
		$s=fread($f,$n);
		if($s===false)
			$this->Error('Error while reading stream');
		$n-=strlen($s);
		$res.=$s;
	}
	if($n>0)
		$this->Error('Unexpected end of stream');
	return $res;
}

function _readint($f)
{
	//Read a 4-byte integer from stream
	$a=unpack('Ni',$this->_readstream($f,4));
	return $a['i'];
}

function _parsegif($file)
{
	//Extract info from a GIF file (via PNG conversion)
	if(!function_exists('imagepng'))
		$this->Error('GD extension is required for GIF support');
	if(!function_exists('imagecreatefromgif'))
		$this->Error('GD has no GIF read support');
	$im=imagecreatefromgif($file);
	if(!$im)
		$this->Error('Missing or incorrect image file: '.$file);
	imageinterlace($im,0);
	$tmp=tempnam('.','gif');
	if(!$tmp)
		$this->Error('Unable to create a temporary file');
	if(!imagepng($im,$tmp))
		$this->Error('Error while saving to temporary file');
	imagedestroy($im);
	$info=$this->_parsepng($tmp);
	unlink($tmp);
	return $info;
}

function _newobj()
{
	//Begin a new object
	$this->n++;
	$this->offsets[$this->n]=strlen($this->buffer);
	$this->_out($this->n.' 0 obj');
}

function _putstream($s)
{
	$this->_out('stream');
	$this->_out($s);
	$this->_out('endstream');
}

function _out($s)
{
	//Add a line to the document
	if($this->state==2)
		$this->pages[$this->page].=$s."\n";
	else
		$this->buffer.=$s."\n";
}

function _putpages()
{
	$nb=$this->page;
	if(!empty($this->AliasNbPages))
	{
		//Replace number of pages
		for($n=1;$n<=$nb;$n++)
			$this->pages[$n]=str_replace($this->AliasNbPages,$nb,$this->pages[$n]);
	}
	if($this->DefOrientation=='P')
	{
		$wPt=$this->DefPageFormat[0]*$this->k;
		$hPt=$this->DefPageFormat[1]*$this->k;
	}
	else
	{
		$wPt=$this->DefPageFormat[1]*$this->k;
		$hPt=$this->DefPageFormat[0]*$this->k;
	}
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	for($n=1;$n<=$nb;$n++)
	{
		//Page
		$this->_newobj();
		$this->_out('<</Type /Page');
		$this->_out('/Parent 1 0 R');
		if(isset($this->PageSizes[$n]))
			$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$this->PageSizes[$n][0],$this->PageSizes[$n][1]));
		$this->_out('/Resources 2 0 R');
		if(isset($this->PageLinks[$n]))
		{
			//Links
			$annots='/Annots [';
			foreach($this->PageLinks[$n] as $pl)
			{
				$rect=sprintf('%.2F %.2F %.2F %.2F',$pl[0],$pl[1],$pl[0]+$pl[2],$pl[1]-$pl[3]);
				$annots.='<</Type /Annot /Subtype /Link /Rect ['.$rect.'] /Border [0 0 0] ';
				if(is_string($pl[4]))
					$annots.='/A <</S /URI /URI '.$this->_textstring($pl[4]).'>>>>';
				else
				{
					$l=$this->links[$pl[4]];
					$h=isset($this->PageSizes[$l[0]]) ? $this->PageSizes[$l[0]][1] : $hPt;
					$annots.=sprintf('/Dest [%d 0 R /XYZ 0 %.2F null]>>',1+2*$l[0],$h-$l[1]*$this->k);
				}
			}
			$this->_out($annots.']');
		}
		$this->_out('/Contents '.($this->n+1).' 0 R>>');
		$this->_out('endobj');
		//Page content
		$p=($this->compress) ? gzcompress($this->pages[$n]) : $this->pages[$n];
		$this->_newobj();
		$this->_out('<<'.$filter.'/Length '.strlen($p).'>>');
		$this->_putstream($p);
		$this->_out('endobj');
	}
	//Pages root
	$this->offsets[1]=strlen($this->buffer);
	$this->_out('1 0 obj');
	$this->_out('<</Type /Pages');
	$kids='/Kids [';
	for($i=0;$i<$nb;$i++)
		$kids.=(3+2*$i).' 0 R ';
	$this->_out($kids.']');
	$this->_out('/Count '.$nb);
	$this->_out(sprintf('/MediaBox [0 0 %.2F %.2F]',$wPt,$hPt));
	$this->_out('>>');
	$this->_out('endobj');
}

function _putfonts()
{
	$nf=$this->n;
	foreach($this->diffs as $diff)
	{
		//Encodings
		$this->_newobj();
		$this->_out('<</Type /Encoding /BaseEncoding /WinAnsiEncoding /Differences ['.$diff.']>>');
		$this->_out('endobj');
	}
	foreach($this->FontFiles as $file=>$info)
	{
		//Font file embedding
		$this->_newobj();
		$this->FontFiles[$file]['n']=$this->n;
		$font='';
		$f=fopen($this->_getfontpath().$file,'rb',1);
		if(!$f)
			$this->Error('Font file not found');
		while(!feof($f))
			$font.=fread($f,8192);
		fclose($f);
		$compressed=(substr($file,-2)=='.z');
		if(!$compressed && isset($info['length2']))
		{
			$header=(ord($font[0])==128);
			if($header)
			{
				//Strip first binary header
				$font=substr($font,6);
			}
			if($header && ord($font[$info['length1']])==128)
			{
				//Strip second binary header
				$font=substr($font,0,$info['length1']).substr($font,$info['length1']+6);
			}
		}
		$this->_out('<</Length '.strlen($font));
		if($compressed)
			$this->_out('/Filter /FlateDecode');
		$this->_out('/Length1 '.$info['length1']);
		if(isset($info['length2']))
			$this->_out('/Length2 '.$info['length2'].' /Length3 0');
		$this->_out('>>');
		$this->_putstream($font);
		$this->_out('endobj');
	}
	foreach($this->fonts as $k=>$font)
	{
		//Font objects
		$this->fonts[$k]['n']=$this->n+1;
		$type=$font['type'];
		$name=$font['name'];
		if($type=='core')
		{
			//Standard font
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/BaseFont /'.$name);
			$this->_out('/Subtype /Type1');
			if($name!='Symbol' && $name!='ZapfDingbats')
				$this->_out('/Encoding /WinAnsiEncoding');
			$this->_out('>>');
			$this->_out('endobj');
		}
		elseif($type=='Type1' || $type=='TrueType')
		{
			//Additional Type1 or TrueType font
			$this->_newobj();
			$this->_out('<</Type /Font');
			$this->_out('/BaseFont /'.$name);
			$this->_out('/Subtype /'.$type);
			$this->_out('/FirstChar 32 /LastChar 255');
			$this->_out('/Widths '.($this->n+1).' 0 R');
			$this->_out('/FontDescriptor '.($this->n+2).' 0 R');
			if($font['enc'])
			{
				if(isset($font['diff']))
					$this->_out('/Encoding '.($nf+$font['diff']).' 0 R');
				else
					$this->_out('/Encoding /WinAnsiEncoding');
			}
			$this->_out('>>');
			$this->_out('endobj');
			//Widths
			$this->_newobj();
			$cw=&$font['cw'];
			$s='[';
			for($i=32;$i<=255;$i++)
				$s.=$cw[chr($i)].' ';
			$this->_out($s.']');
			$this->_out('endobj');
			//Descriptor
			$this->_newobj();
			$s='<</Type /FontDescriptor /FontName /'.$name;
			foreach($font['desc'] as $k=>$v)
				$s.=' /'.$k.' '.$v;
			$file=$font['file'];
			if($file)
				$s.=' /FontFile'.($type=='Type1' ? '' : '2').' '.$this->FontFiles[$file]['n'].' 0 R';
			$this->_out($s.'>>');
			$this->_out('endobj');
		}
		else
		{
			//Allow for additional types
			$mtd='_put'.strtolower($type);
			if(!method_exists($this,$mtd))
				$this->Error('Unsupported font type: '.$type);
			$this->$mtd($font);
		}
	}
}

function _putimages()
{
	$filter=($this->compress) ? '/Filter /FlateDecode ' : '';
	reset($this->images);
	while(list($file,$info)=each($this->images))
	{
		$this->_newobj();
		$this->images[$file]['n']=$this->n;
		$this->_out('<</Type /XObject');
		$this->_out('/Subtype /Image');
		$this->_out('/Width '.$info['w']);
		$this->_out('/Height '.$info['h']);
		if($info['cs']=='Indexed')
			$this->_out('/ColorSpace [/Indexed /DeviceRGB '.(strlen($info['pal'])/3-1).' '.($this->n+1).' 0 R]');
		else
		{
			$this->_out('/ColorSpace /'.$info['cs']);
			if($info['cs']=='DeviceCMYK')
				$this->_out('/Decode [1 0 1 0 1 0 1 0]');
		}
		$this->_out('/BitsPerComponent '.$info['bpc']);
		if(isset($info['f']))
			$this->_out('/Filter /'.$info['f']);
		if(isset($info['parms']))
			$this->_out($info['parms']);
		if(isset($info['trns']) && is_array($info['trns']))
		{
			$trns='';
			for($i=0;$i<count($info['trns']);$i++)
				$trns.=$info['trns'][$i].' '.$info['trns'][$i].' ';
			$this->_out('/Mask ['.$trns.']');
		}
		$this->_out('/Length '.strlen($info['data']).'>>');
		$this->_putstream($info['data']);
		unset($this->images[$file]['data']);
		$this->_out('endobj');
		//Palette
		if($info['cs']=='Indexed')
		{
			$this->_newobj();
			$pal=($this->compress) ? gzcompress($info['pal']) : $info['pal'];
			$this->_out('<<'.$filter.'/Length '.strlen($pal).'>>');
			$this->_putstream($pal);
			$this->_out('endobj');
		}
	}
}

function _putxobjectdict()
{
	foreach($this->images as $image)
		$this->_out('/I'.$image['i'].' '.$image['n'].' 0 R');
}

function _putresourcedict()
{
	$this->_out('/ProcSet [/PDF /Text /ImageB /ImageC /ImageI]');
	$this->_out('/Font <<');
	foreach($this->fonts as $font)
		$this->_out('/F'.$font['i'].' '.$font['n'].' 0 R');
	$this->_out('>>');
	$this->_out('/XObject <<');
	$this->_putxobjectdict();
	$this->_out('>>');
}

function _putresources()
{
	$this->_putfonts();
	$this->_putimages();
	//Resource dictionary
	$this->offsets[2]=strlen($this->buffer);
	$this->_out('2 0 obj');
	$this->_out('<<');
	$this->_putresourcedict();
	$this->_out('>>');
	$this->_out('endobj');
}

function _putinfo()
{
	$this->_out('/Producer '.$this->_textstring('fpdf '.FPDF_VERSION));
	if(!empty($this->title))
		$this->_out('/Title '.$this->_textstring($this->title));
	if(!empty($this->subject))
		$this->_out('/Subject '.$this->_textstring($this->subject));
	if(!empty($this->author))
		$this->_out('/Author '.$this->_textstring($this->author));
	if(!empty($this->keywords))
		$this->_out('/Keywords '.$this->_textstring($this->keywords));
	if(!empty($this->creator))
		$this->_out('/Creator '.$this->_textstring($this->creator));
	$this->_out('/CreationDate '.$this->_textstring('D:'.@date('YmdHis')));
}

function _putcatalog()
{
	$this->_out('/Type /Catalog');
	$this->_out('/Pages 1 0 R');
	if($this->ZoomMode=='fullpage')
		$this->_out('/OpenAction [3 0 R /Fit]');
	elseif($this->ZoomMode=='fullwidth')
		$this->_out('/OpenAction [3 0 R /FitH null]');
	elseif($this->ZoomMode=='real')
		$this->_out('/OpenAction [3 0 R /XYZ null null 1]');
	elseif(!is_string($this->ZoomMode))
		$this->_out('/OpenAction [3 0 R /XYZ null null '.($this->ZoomMode/100).']');
	if($this->LayoutMode=='single')
		$this->_out('/PageLayout /SinglePage');
	elseif($this->LayoutMode=='continuous')
		$this->_out('/PageLayout /OneColumn');
	elseif($this->LayoutMode=='two')
		$this->_out('/PageLayout /TwoColumnLeft');
}

function _putheader()
{
	$this->_out('%PDF-'.$this->PDFVersion);
}

function _puttrailer()
{
	$this->_out('/Size '.($this->n+1));
	$this->_out('/Root '.$this->n.' 0 R');
	$this->_out('/Info '.($this->n-1).' 0 R');
}

function _enddoc()
{
	$this->_putheader();
	$this->_putpages();
	$this->_putresources();
	//Info
	$this->_newobj();
	$this->_out('<<');
	$this->_putinfo();
	$this->_out('>>');
	$this->_out('endobj');
	//Catalog
	$this->_newobj();
	$this->_out('<<');
	$this->_putcatalog();
	$this->_out('>>');
	$this->_out('endobj');
	//Cross-ref
	$o=strlen($this->buffer);
	$this->_out('xref');
	$this->_out('0 '.($this->n+1));
	$this->_out('0000000000 65535 f ');
	for($i=1;$i<=$this->n;$i++)
		$this->_out(sprintf('%010d 00000 n ',$this->offsets[$i]));
	//Trailer
	$this->_out('trailer');
	$this->_out('<<');
	$this->_puttrailer();
	$this->_out('>>');
	$this->_out('startxref');
	$this->_out($o);
	$this->_out('%%EOF');
	$this->state=3;
}

function drawTextBox($strText, $w, $h, $align='L', $valign='T', $border=1)
{
    $xi=$this->GetX();
    $yi=$this->GetY();
    
    $hrow=$this->FontSize;
    $textrows=$this->drawRows($w, $hrow, $strText, 0, $align, 0, 0, 0);
    $maxrows=floor($h/$this->FontSize);
    $rows=min($textrows, $maxrows);

    $dy=0;
    if (strtoupper($valign)=='M')
        $dy=($h-$rows*$this->FontSize)/2;
    if (strtoupper($valign)=='B')
        $dy=$h-$rows*$this->FontSize;

    $this->SetY($yi+$dy);
    $this->SetX($xi);

    $this->drawRows($w, $hrow, $strText, 0, $align, 0, $rows, 1);

    if ($border==1)
        $this->Rect($xi, $yi, $w, $h);
}

function drawRows($w, $h, $txt, $border=0, $align='J', $fill=0, $maxline=0, $prn=0)
{
    $cw=&$this->CurrentFont['cw'];
    if($w==0)
        $w=$this->w-$this->rMargin-$this->x;
    $wmax=($w-2*$this->cMargin)*1000/$this->FontSize;
    $s=str_replace("\r", '', $txt);
    $nb=strlen($s);
    if($nb>0 and $s[$nb-1]=="\n")
        $nb--;
    $b=0;
    if($border)
    {
        if($border==1)
        {
            $border='LTRB';
            $b='LRT';
            $b2='LR';
        }
        else
        {
            $b2='';
            if(is_int(strpos($border, 'L')))
                $b2.='L';
            if(is_int(strpos($border, 'R')))
                $b2.='R';
            $b=is_int(strpos($border, 'T')) ? $b2.'T' : $b2;
        }
    }
    $sep=-1;
    $i=0;
    $j=0;
    $l=0;
    $ns=0;
    $nl=1;
    while($i<$nb)
    {
        //Get next character
        $c=$s[$i];
        if($c=="\n")
        {
            //Explicit line break
            if($this->ws>0)
            {
                $this->ws=0;
                if ($prn==1) $this->_out('0 Tw');
            }
            if ($prn==1) {
                $this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
            }
            $i++;
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border and $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s, $i);
            continue;
        }
        if($c==' ')
        {
            $sep=$i;
            $ls=$l;
            $ns++;
        }
        $l+=$cw[$c];
        if($l>$wmax)
        {
            //Automatic line break
            if($sep==-1)
            {
                if($i==$j)
                    $i++;
                if($this->ws>0)
                {
                    $this->ws=0;
                    if ($prn==1) $this->_out('0 Tw');
                }
                if ($prn==1) {
                    $this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
                }
            }
            else
            {
                if($align=='J')
                {
                    $this->ws=($ns>1) ? ($wmax-$ls)/1000*$this->FontSize/($ns-1) : 0;
                    if ($prn==1) $this->_out(sprintf('%.3f Tw', $this->ws*$this->k));
                }
                if ($prn==1){
                    $this->Cell($w, $h, substr($s, $j, $sep-$j), $b, 2, $align, $fill);
                }
                $i=$sep+1;
            }
            $sep=-1;
            $j=$i;
            $l=0;
            $ns=0;
            $nl++;
            if($border and $nl==2)
                $b=$b2;
            if ( $maxline && $nl > $maxline )
                return substr($s, $i);
        }
        else
            $i++;
    }
    //Last chunk
    if($this->ws>0)
    {
        $this->ws=0;
        if ($prn==1) $this->_out('0 Tw');
    }
    if($border and is_int(strpos($border, 'B')))
        $b.='B';
    if ($prn==1) {
        $this->Cell($w, $h, substr($s, $j, $i-$j), $b, 2, $align, $fill);
    }
    $this->x=$this->lMargin;
    return $nl;
}


//End of class
}

//Handle special IE contype request
if(isset($_SERVER['HTTP_USER_AGENT']) && $_SERVER['HTTP_USER_AGENT']=='contype')
{
	header('Content-Type: application/pdf');
	exit;
}

?>
