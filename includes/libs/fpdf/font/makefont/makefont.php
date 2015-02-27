<?php
/*******************************************************************************
* Utility to generate font definition files                                    *
*                                                                              *
* Version: 1.14                                                                *
* Date:    2008-08-03                                                          *
* Author:  Olivier PLATHEY                                                     *
*******************************************************************************/

function ReadMap($enc)
{
	//Read a map file
	$file=dirname(__FILE__).'/'.strtolower($enc).'.map';
	$a=file($file);
	if(empty($a))
		die('<b>Error:</b> encoding not found: '.$enc);
	$cc2gn=array();
	foreach($a as $l)
	{
		if($l[0]=='!')
		{
			$e=preg_split('/[ \\t]+/',rtrim($l));
			$cc=hexdec(substr($e[0],1));
			$gn=$e[2];
			$cc2gn[$cc]=$gn;
		}
	}
	for($i=0;$i<=255;$i++)
	{
		if(!isset($cc2gn[$i]))
			$cc2gn[$i]='.notdef';
	}
	return $cc2gn;
}

function ReadAFM($file, &$map)
{
	//Read a font metric file
	$a=file($file);
	if(empty($a))
		die('File not found');
	$widths=array();
	$fm=array();
	$fix=array('Edot'=>'Edotaccent','edot'=>'edotaccent','Idot'=>'Idotaccent','Zdot'=>'Zdotaccent','zdot'=>'zdotaccent',
		'Odblacute'=>'Ohungarumlaut','odblacute'=>'ohungarumlaut','Udblacute'=>'Uhungarumlaut','udblacute'=>'uhungarumlaut',
		'Gcedilla'=>'Gcommaaccent','gcedilla'=>'gcommaaccent','Kcedilla'=>'Kcommaaccent','kcedilla'=>'kcommaaccent',
		'Lcedilla'=>'Lcommaaccent','lcedilla'=>'lcommaaccent','Ncedilla'=>'Ncommaaccent','ncedilla'=>'ncommaaccent',
		'Rcedilla'=>'Rcommaaccent','rcedilla'=>'rcommaaccent','Scedilla'=>'Scommaaccent','scedilla'=>'scommaaccent',
		'Tcedilla'=>'Tcommaaccent','tcedilla'=>'tcommaaccent','Dslash'=>'Dcroat','dslash'=>'dcroat','Dmacron'=>'Dcroat','dmacron'=>'dcroat',
		'combininggraveaccent'=>'gravecomb','combininghookabove'=>'hookabovecomb','combiningtildeaccent'=>'tildecomb',
		'combiningacuteaccent'=>'acutecomb','combiningdotbelow'=>'dotbelowcomb','dongsign'=>'dong');
	foreach($a as $l)
	{
		$e=explode(' ',rtrim($l));
		if(count($e)<2)
			continue;
		$code=$e[0];
		$param=$e[1];
		if($code=='C')
		{
			//Character metrics
			$cc=(int)$e[1];
			$w=$e[4];
			$gn=$e[7];
			if(substr($gn,-4)=='20AC')
				$gn='Euro';
			if(isset($fix[$gn]))
			{
				//Fix incorrect glyph name
				foreach($map as $c=>$n)
				{
					if($n==$fix[$gn])
						$map[$c]=$gn;
				}
			}
			if(empty($map))
			{
				//Symbolic font: use built-in encoding
				$widths[$cc]=$w;
			}
			else
			{
				$widths[$gn]=$w;
				if($gn=='X')
					$fm['CapXHeight']=$e[13];
			}
			if($gn=='.notdef')
				$fm['MissingWidth']=$w;
		}
		elseif($code=='FontName')
			$fm['FontName']=$param;
		elseif($code=='Weight')
			$fm['Weight']=$param;
		elseif($code=='ItalicAngle')
			$fm['ItalicAngle']=(double)$param;
		elseif($code=='Ascender')
			$fm['Ascender']=(int)$param;
		elseif($code=='Descender')
			$fm['Descender']=(int)$param;
		elseif($code=='UnderlineThickness')
			$fm['UnderlineThickness']=(int)$param;
		elseif($code=='UnderlinePosition')
			$fm['UnderlinePosition']=(int)$param;
		elseif($code=='IsFixedPitch')
			$fm['IsFixedPitch']=($param=='true');
		elseif($code=='FontBBox')
			$fm['FontBBox']=array($e[1],$e[2],$e[3],$e[4]);
		elseif($code=='CapHeight')
			$fm['CapHeight']=(int)$param;
		elseif($code=='StdVW')
			$fm['StdVW']=(int)$param;
	}
	if(!isset($fm['FontName']))
		die('FontName not found');
	if(!empty($map))
	{
		if(!isset($widths['.notdef']))
			$widths['.notdef']=600;
		if(!isset($widths['Delta']) && isset($widths['increment']))
			$widths['Delta']=$widths['increment'];
		//Order widths according to map
		for($i=0;$i<=255;$i++)
		{
			if(!isset($widths[$map[$i]]))
			{
				echo '<b>Warning:</b> character '.$map[$i].' is missing<br>';
				$widths[$i]=$widths['.notdef'];
			}
			else
				$widths[$i]=$widths[$map[$i]];
		}
	}
	$fm['Widths']=$widths;
	return $fm;
}

function MakeFontDescriptor($fm, $symbolic)
{
	//Ascent
	$asc=(isset($fm['Ascender']) ? $fm['Ascender'] : 1000);
	$fd="array('Ascent'=>".$asc;
	//Descent
	$desc=(isset($fm['Descender']) ? $fm['Descender'] : -200);
	$fd.=",'Descent'=>".$desc;
	//CapHeight
	if(isset($fm['CapHeight']))
		$ch=$fm['CapHeight'];
	elseif(isset($fm['CapXHeight']))
		$ch=$fm['CapXHeight'];
	else
		$ch=$asc;
	$fd.=",'CapHeight'=>".$ch;
	//Flags
	$flags=0;
	if(isset($fm['IsFixedPitch']) && $fm['IsFixedPitch'])
		$flags+=1<<0;
	if($symbolic)
		$flags+=1<<2;
	if(!$symbolic)
		$flags+=1<<5;
	if(isset($fm['ItalicAngle']) && $fm['ItalicAngle']!=0)
		$flags+=1<<6;
	$fd.=",'Flags'=>".$flags;
	//FontBBox
	if(isset($fm['FontBBox']))
		$fbb=$fm['FontBBox'];
	else
		$fbb=array(0,$desc-100,1000,$asc+100);
	$fd.=",'FontBBox'=>'[".$fbb[0].' '.$fbb[1].' '.$fbb[2].' '.$fbb[3]."]'";
	//ItalicAngle
	$ia=(isset($fm['ItalicAngle']) ? $fm['ItalicAngle'] : 0);
	$fd.=",'ItalicAngle'=>".$ia;
	//StemV
	if(isset($fm['StdVW']))
		$stemv=$fm['StdVW'];
	elseif(isset($fm['Weight']) && preg_match('/bold|black/i',$fm['Weight']))
		$stemv=120;
	else
		$stemv=70;
	$fd.=",'StemV'=>".$stemv;
	//MissingWidth
	if(isset($fm['MissingWidth']))
		$fd.=",'MissingWidth'=>".$fm['MissingWidth'];
	$fd.=')';
	return $fd;
}

function MakeWidthArray($fm)
{
	//Make character width array
	$s="array(\n\t";
	$cw=$fm['Widths'];
	for($i=0;$i<=255;$i++)
	{
		if(chr($i)=="'")
			$s.="'\\''";
		elseif(chr($i)=="\\")
			$s.="'\\\\'";
		elseif($i>=32 && $i<=126)
			$s.="'".chr($i)."'";
		else
			$s.="chr($i)";
		$s.='=>'.$fm['Widths'][$i];
		if($i<255)
			$s.=',';
		if(($i+1)%22==0)
			$s.="\n\t";
	}
	$s.=')';
	return $s;
}

function MakeFontEncoding($map)
{
	//Build differences from reference encoding
	$ref=ReadMap('cp1252');
	$s='';
	$last=0;
	for($i=32;$i<=255;$i++)
	{
		if($map[$i]!=$ref[$i])
		{
			if($i!=$last+1)
				$s.=$i.' ';
			$last=$i;
			$s.='/'.$map[$i].' ';
		}
	}
	return rtrim($s);
}

function SaveToFile($file, $s, $mode)
{
	$f=fopen($file,'w'.$mode);
	if(!$f)
		die('Can\'t write to file '.$file);
	fwrite($f,$s,strlen($s));
	fclose($f);
}

function ReadShort($f)
{
	$a=unpack('n1n',fread($f,2));
	return $a['n'];
}

function ReadLong($f)
{
	$a=unpack('N1N',fread($f,4));
	return $a['N'];
}

function CheckTTF($file)
{
	//Check if font license allows embedding
	$f=fopen($file,'rb');
	if(!$f)
		die('<b>Error:</b> Can\'t open '.$file);
	//Extract number of tables
	fseek($f,4,SEEK_CUR);
	$nb=ReadShort($f);
	fseek($f,6,SEEK_CUR);
	//Seek OS/2 table
	$found=false;
	for($i=0;$i<$nb;$i++)
	{
		if(fread($f,4)=='OS/2')
		{
			$found=true;
			break;
		}
		fseek($f,12,SEEK_CUR);
	}
	if(!$found)
	{
		fclose($f);
		return;
	}
	fseek($f,4,SEEK_CUR);
	$offset=ReadLong($f);
	fseek($f,$offset,SEEK_SET);
	//Extract fsType flags
	fseek($f,8,SEEK_CUR);
	$fsType=ReadShort($f);
	$rl=($fsType & 0x02)!=0;
	$pp=($fsType & 0x04)!=0;
	$e=($fsType & 0x08)!=0;
	fclose($f);
	if($rl && !$pp && !$e)
		echo '<b>Warning:</b> font license does not allow embedding';
}

/*******************************************************************************
* fontfile: path to TTF file (or empty string if not to be embedded)           *
* afmfile:  path to AFM file                                                   *
* enc:      font encoding (or empty string for symbolic fonts)                 *
* patch:    optional patch for encoding                                        *
* type:     font type if fontfile is empty                                     *
*******************************************************************************/
function MakeFont($fontfile, $afmfile, $enc='cp1252', $patch=array(), $type='TrueType')
{
	//Generate a font definition file
	if(get_magic_quotes_runtime())
		@set_magic_quotes_runtime(0);
	ini_set('auto_detect_line_endings','1');
	if($enc)
	{
		$map=ReadMap($enc);
		foreach($patch as $cc=>$gn)
			$map[$cc]=$gn;
	}
	else
		$map=array();
	if(!file_exists($afmfile))
		die('<b>Error:</b> AFM file not found: '.$afmfile);
	$fm=ReadAFM($afmfile,$map);
	if($enc)
		$diff=MakeFontEncoding($map);
	else
		$diff='';
	$fd=MakeFontDescriptor($fm,empty($map));
	//Find font type
	if($fontfile)
	{
		$ext=strtolower(substr($fontfile,-3));
		if($ext=='ttf')
			$type='TrueType';
		elseif($ext=='pfb')
			$type='Type1';
		else
			die('<b>Error:</b> unrecognized font file extension: '.$ext);
	}
	else
	{
		if($type!='TrueType' && $type!='Type1')
			die('<b>Error:</b> incorrect font type: '.$type);
	}
	//Start generation
	$s='<?php'."\n";
	$s.='$type=\''.$type."';\n";
	$s.='$name=\''.$fm['FontName']."';\n";
	$s.='$desc='.$fd.";\n";
	if(!isset($fm['UnderlinePosition']))
		$fm['UnderlinePosition']=-100;
	if(!isset($fm['UnderlineThickness']))
		$fm['UnderlineThickness']=50;
	$s.='$up='.$fm['UnderlinePosition'].";\n";
	$s.='$ut='.$fm['UnderlineThickness'].";\n";
	$w=MakeWidthArray($fm);
	$s.='$cw='.$w.";\n";
	$s.='$enc=\''.$enc."';\n";
	$s.='$diff=\''.$diff."';\n";
	$basename=substr(basename($afmfile),0,-4);
	if($fontfile)
	{
		//Embedded font
		if(!file_exists($fontfile))
			die('<b>Error:</b> font file not found: '.$fontfile);
		if($type=='TrueType')
			CheckTTF($fontfile);
		$f=fopen($fontfile,'rb');
		if(!$f)
			die('<b>Error:</b> Can\'t open '.$fontfile);
		$file=fread($f,filesize($fontfile));
		fclose($f);
		if($type=='Type1')
		{
			//Find first two sections and discard third one
			$header=(ord($file[0])==128);
			if($header)
			{
				//Strip first binary header
				$file=substr($file,6);
			}
			$pos=strpos($file,'eexec');
			if(!$pos)
				die('<b>Error:</b> font file does not seem to be valid Type1');
			$size1=$pos+6;
			if($header && ord($file[$size1])==128)
			{
				//Strip second binary header
				$file=substr($file,0,$size1).substr($file,$size1+6);
			}
			$pos=strpos($file,'00000000');
			if(!$pos)
				die('<b>Error:</b> font file does not seem to be valid Type1');
			$size2=$pos-$size1;
			$file=substr($file,0,$size1+$size2);
		}
		if(function_exists('gzcompress'))
		{
			$cmp=$basename.'.z';
			SaveToFile($cmp,gzcompress($file),'b');
			$s.='$file=\''.$cmp."';\n";
			echo 'Font file compressed ('.$cmp.')<br>';
		}
		else
		{
			$s.='$file=\''.basename($fontfile)."';\n";
			echo '<b>Notice:</b> font file could not be compressed (zlib extension not available)<br>';
		}
		if($type=='Type1')
		{
			$s.='$size1='.$size1.";\n";
			$s.='$size2='.$size2.";\n";
		}
		else
			$s.='$originalsize='.filesize($fontfile).";\n";
	}
	else
	{
		//Not embedded font
		$s.='$file='."'';\n";
	}
	$s.="?>\n";
	SaveToFile($basename.'.php',$s,'t');
	echo 'Font definition file generated ('.$basename.'.php'.')<br>';
}
?>
