<?php
class PDF_HTML extends fpdf
{
    var $B=0;
    var $I=0;
    var $U=0;
    var $HREF='';
    var $ALIGN='';
	public $tags = array('HR' => 'HR', 'STRONG' => 'B', 'EM'=>'I', 'P'=>'P', 'SPAN' => 'U', 'A' => 'A', 'BR' => 'BR');

	function WriteHTML($html)
    {
        //HTML parser
        //$html=str_replace("\n", ' ', $html);
       
        
       
        $a=preg_split('/<(.*)>/U', $html, -1, PREG_SPLIT_DELIM_CAPTURE);
        foreach($a as $i=>$e)
        {
            if($i%2==0)
            {
                //Text
                if($this->HREF)
                    $this->PutLink($this->HREF, $e);
                elseif($this->ALIGN == 'center')
                    $this->Cell(0, 5, $e, 0, 1, 'C');
                else
                    $this->Write(5, $e);
            }
            else
            {
                //Tag
                if($e{0}=='/')
                    $this->CloseTag($this->tags[strtoupper(substr($e, 1))]);
                else
                {
                    //Extract properties
                    $a2=explode(' ', $e);
					
                    $tag=strtoupper(array_shift($a2));
                    $tag = $this->tags[$tag];
                    $prop=array();
                    foreach($a2 as $v)
                        if(preg_match('/(\w+)\s*(?:=\s*(?:"([^"]*)"|\'([^\']*)\'|(\w+)))?/usix', $v, $a3))
                            $prop[strtoupper($a3[1])]=$a3[2];

                   if(isset($prop['ALIGN']) === false){
                    	$prop['ALIGN'] = 'LEFT';
                    }
                    
                	if(isset($prop['HREF']) === false){
                    	$prop['HREF'] = '';
                    }
                    $this->OpenTag($tag, $prop);
                }
            }
        }
    }

    function OpenTag($tag, $prop)
    {
        //Opening tag
        if($tag=='B' or $tag=='I' or $tag=='U')
            $this->SetStyle($tag, true);
        if($tag=='A')
            $this->HREF=$prop['HREF'];
        if($tag=='BR')
            $this->Ln(5);
        if($tag=='P')
            $this->ALIGN=$prop['ALIGN'];
        if($tag=='HR')
        {
            if(isset($prop['WIDTH']) === true && $prop['WIDTH'] != '')
                $Width = $prop['WIDTH'];
            else
                $Width = $this->w - $this->lMargin-$this->rMargin;
            $this->Ln(2);
            $x = $this->GetX();
            $y = $this->GetY();
            $this->SetLineWidth(0.4);
            $this->Line($x, $y, $x+$Width, $y);
            $this->SetLineWidth(0.2);
            $this->Ln(2);
        }
    }

    function CloseTag($tag)
    {
        //Closing tag
        if($tag=='B' or $tag=='I' or $tag=='U')
            $this->SetStyle($tag, false);
        if($tag=='A')
            $this->HREF='';
        if($tag=='P')
            $this->ALIGN='';
    }

    function SetStyle($tag, $enable)
    {
        //Modify style and select corresponding font
        $this->$tag+=($enable ? 1 : -1);
        $style='';
        foreach(array('B', 'I', 'U') as $s)
            if($this->$s>0)
                $style.=$s;
        $this->SetFont('', $style);
    }

    function PutLink($URL, $txt)
    {
        //Put a hyperlink
        $this->SetTextColor(0, 0, 255);
        $this->SetStyle('U', true);
        $this->Write(5, $txt, $URL);
        $this->SetStyle('U', false);
        $this->SetTextColor(0);
    }
    

}
?>