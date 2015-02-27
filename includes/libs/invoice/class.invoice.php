<?php

define('EURO', chr(128) );
define('EURO_VAL', 6.55957 );

// Xavier Nicolay 2004
// Version 1.01

//////////////////////////////////////
// Public functions                 //
//////////////////////////////////////
//  function sizeOfText( $texte, $larg )
//  function addSociete( $nom, $adresse )
//  function fact_dev( $libelle, $num )
//  function addDevis( $numdev )
//  function addFacture( $numfact )
//  function addDate( $date )
//  function addClient( $ref )
//  function addPageNumber( $page )
//  function addClientAdresse( $adresse )
//  function addReglement( $mode )
//  function addEcheance( $date )
//  function addNumTVA($tva)
//  function addReference($ref)
//  function addCols( $tab )
//  function addLineFormat( $tab )
//  function lineVert( $tab )
//  function addLine( $ligne, $tab )
//  function addRemarque($remarque)
//  function addCadreTVAs()
//  function addCadreEurosFrancs()
//  function addTVAs( $params, $tab_tva, $invoice )
//  function temporaire( $texte )

class invoice extends fpdf
{
// private variables
var $colonnes;
var $format;
var $angle=0;

// private functions
function RoundedRect($x, $y, $w, $h, $r, $style = '')
{
    $k = $this->k;
    $hp = $this->h;
    if($style=='F')
        $op='f';
    elseif($style=='FD' or $style=='DF')
        $op='B';
    else
        $op='S';
    $MyArc = 4/3 * (sqrt(2) - 1);
    $this->_out(sprintf('%.2f %.2f m', ($x+$r)*$k, ($hp-$y)*$k ));
    $xc = $x+$w-$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2f %.2f l', $xc*$k, ($hp-$y)*$k ));

    $this->_Arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);
    $xc = $x+$w-$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2f %.2f l', ($x+$w)*$k, ($hp-$yc)*$k));
    $this->_Arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);
    $xc = $x+$r ;
    $yc = $y+$h-$r;
    $this->_out(sprintf('%.2f %.2f l', $xc*$k, ($hp-($y+$h))*$k));
    $this->_Arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);
    $xc = $x+$r ;
    $yc = $y+$r;
    $this->_out(sprintf('%.2f %.2f l', ($x)*$k, ($hp-$yc)*$k ));
    $this->_Arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
    $this->_out($op);
}

function _Arc($x1, $y1, $x2, $y2, $x3, $y3)
{
    $h = $this->h;
    $this->_out(sprintf('%.2f %.2f %.2f %.2f %.2f %.2f c ', $x1*$this->k, ($h-$y1)*$this->k,
                        $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
}

function Rotate($angle, $x=-1, $y=-1)
{
    if($x==-1)
        $x=$this->x;
    if($y==-1)
        $y=$this->y;
    if($this->angle!=0)
        $this->_out('Q');
    $this->angle=$angle;
    if($angle!=0)
    {
        $angle*=M_PI/180;
        $c=cos($angle);
        $s=sin($angle);
        $cx=$x*$this->k;
        $cy=($this->h-$y)*$this->k;
        $this->_out(sprintf('q %.5f %.5f %.5f %.5f %.2f %.2f cm 1 0 0 1 %.2f %.2f cm', $c, $s, -$s, $c, $cx, $cy, -$cx, -$cy));
    }
}

function _endpage()
{
    if($this->angle!=0)
    {
        $this->angle=0;
        $this->_out('Q');
    }
    parent::_endpage();
}

// public functions
function sizeOfText( $texte, $largeur )
{
    $index    = 0;
    $nb_lines = 0;
    $loop     = TRUE;
    
   // echo "TEXT:" . $texte .  "  largeur:" . $largeur . "<hr>";
    while ( $loop )
    {
        $pos = strpos($texte, "\n");
        if (!$pos)
        {
            $loop  = FALSE;
            $ligne = $texte;
        }
        else
        {
            $ligne  = substr( $texte, $index, $pos);
            $texte = substr( $texte, $pos+1 );
        }
        $length = floor( $this->GetStringWidth( $ligne ) );
        if  ($largeur==0){
        	$largeur=1;
        }
        
        $res = 1 + floor( $length / $largeur) ;
        $nb_lines += $res;
    }
    return $nb_lines;
}

// Company
function addSociete( $nom, $adresse )
{
    $x1 = 10;
    $y1 = 8;
    //Positionnement en bas
    $this->SetXY( $x1, $y1 );
    $this->SetFont('Arial', 'B', 12);
    $length = $this->GetStringWidth( $nom );
    $this->MultiCell( $length, 4, $nom);
    $this->SetXY( $x1, $y1 + 8 );
    $this->SetFont('Arial', '', 10);
    $length = $this->GetStringWidth( $adresse );
   
    //Coordonnées de la société
    $lignes = $this->sizeOfText( $adresse, $length) ;
    $this->MultiCell($length, 4, $adresse);
}

// Label and number of invoice/estimate
function fact_dev( $libelle, $num )
{
    $r1  = $this->w - 100;
    $r2  = $r1 + 68;
    $y1  = 30;
    $y2  = $y1 + 2;
    $mid = ($r1 + $r2 ) / 2;
    
    $texte  = $num;    
    $szfont = 16;
    $loop   = 0;
    
    while ( $loop == 0 )
    {
       $this->SetFont( "Helvetica", "B", $szfont );
       $sz = $this->GetStringWidth( $texte );
       if ( ($r1+$sz) > $r2 )
          $szfont --;
       else
          $loop ++;
    }

    $this->SetLineWidth(0.1);
    $this->SetFillColor(192);
    //$this->RoundedRect($r1, $y1, ($r2 - $r1), $y2, 2.5, 'DF');
    $this->SetXY( $r1+1, $y1+10);
    $this->Cell($r2-$r1 -1, 5, $texte, 0, 0, "C" );
}

// Estimate
function addDevis( $numdev )
{
    $string = sprintf("DEV%04d", $numdev);
    $this->fact_dev( "Devis", $string );
}

// Invoice
function addFacture( $numfact )
{
    $string = sprintf("FA%04d", $numfact);
    $this->fact_dev( "Facture", $string );
}

function addDate( $date )
{
    $r1  = $this->w - 61;
    $r2  = $r1 + 30;
    $y1  = 17;
    $y2  = $y1 ;
    $mid = $y1 + ($y2 / 2);
    $this->Rect($r1, $y1, ($r2 - $r1), $y2,  'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(10, 5, "Datum", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+9 );
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(10, 5, $date, 0, 0, "C");
}

function addClient( $ref )
{
    $r1  = $this->w - 31;
    $r2  = $r1 + 19;
    $y1  = 17;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    $this->Rect($r1, $y1, ($r2 - $r1), $y2,  'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(10, 5, "Kunde", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(10, 5, $ref, 0, 0, "C");
}

function addPageNumber( $page )
{
    $r1  = $this->w - 80;
    $r2  = $r1 + 19;
    $y1  = 17;
    $y2  = $y1;
    $mid = $y1 + ($y2 / 2);
    $this->Rect($r1, $y1, ($r2 - $r1), $y2,  'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1+3 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(10, 5, "Seite", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5, $y1 + 9 );
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(10, 5, $page, 0, 0, "C");
}

// Client address
function addClientAdresse( $adresse )
{
	
	$this->SetFont( "Helvetica", "", 11);
    $r1     = $this->w - $this->w + 10;
    $r2     = $r1 + 68;
    $y1     = 50;
    $this->SetXY( $r1, $y1);
    $this->MultiCell( 60, 4, $adresse);
}


// addInvoiceIdentifier
function addInvoiceIdentifier( $InvoiceIdentifier )
{
	
	$this->SetFont( "Helvetica", "", 10);
    $r1     = $this->w - 90;
    $r2     = $r1 + 68;
    $y1     = 50;
    $this->SetXY( $r1, $y1);
    $this->MultiCell( 80, 4, $InvoiceIdentifier);
}



// Mode of payment
function addReglement( $mode )
{
    $r1  = 10;
    $r2  = $r1 + 60;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1+1 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(10, 4, "MODE DE REGLEMENT", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 -5 , $y1 + 5 );
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(10, 5, $mode, 0, 0, "C");
}

// Expiry date
function addEcheance( $date )
{
    $r1  = 80;
    $r2  = $r1 + 40;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);
    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + ($r2 - $r1)/2 - 5 , $y1+1 );
    $this->SetFont( "Helvetica", "B", 10);
    $this->Cell(10, 4, "DATE D'ECHEANCE", 0, 0, "C");
    $this->SetXY( $r1 + ($r2-$r1)/2 - 5 , $y1 + 5 );
    $this->SetFont( "Helvetica", "", 10);
    $this->Cell(10, 5, $date, 0, 0, "C");
}

// VAT number
function addNumTVA($tva)
{
    $this->SetFont( "Helvetica", "B", 10);
    $r1  = $this->w - 80;
    $r2  = $r1 + 70;
    $y1  = 80;
    $y2  = $y1+10;
    $mid = $y1 + (($y2-$y1) / 2);

    $this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1), 'D');
    $this->Line( $r1, $mid, $r2, $mid);
    $this->SetXY( $r1 + 16 , $y1+1 );
    $this->Cell(40, 4, "Abschlussnummer", '', '', "C");
    $this->SetFont( "Helvetica", "", 10);
    $this->SetXY( $r1 + 16 , $y1+5 );
    $this->Cell(40, 5, $tva, '', '', "C");
}

function addReference($ref)
{
    $this->SetFont( "Helvetica", "", 10);
    $length = $this->GetStringWidth( "Betreff : " . $ref );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = 92;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length, 4, "Betreff : " . $ref);
}

function addCols( $tab )
{
    global $colonnes;
    
    $r1  = 10;
    $r2  = $this->w - ($r1 * 2) ;
    $y1  = 100;
    $y2  = $this->h - 80 - $y1;
    $this->SetXY( $r1, $y1 );
    $this->Rect( $r1, $y1, $r2, $y2, "D");
    $this->Line( $r1, $y1+6, $r1+$r2, $y1+6);
    $colX = $r1;
    $colonnes = $tab;
    while ( list( $lib, $pos ) = each ($tab) )
    {
        $this->SetXY( $colX, $y1+2 );
        $this->Cell( $pos, 1, $lib, 0, 0, "C");
        $colX += $pos;
        $this->Line( $colX, $y1, $colX, $y1+$y2);
    }
}

function addLineFormat( $tab )
{
    global $format, $colonnes;
    
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        if ( isset( $tab["$lib"] ) )
            $format[ $lib ] = $tab["$lib"];
    }
}

function lineVert( $tab )
{
    global $colonnes;

    reset( $colonnes );
    $maxSize=0;
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        $texte = $tab[ $lib ];
        $longCell  = $pos -2;
        $size = $this->sizeOfText( $texte, $longCell );
        if ($size > $maxSize)
            $maxSize = $size;
    }
    return $maxSize;
}

// add a line to the invoice/estimate
/*    $ligne = array( "REFERENCE"    => $prod["ref"],
                      "DESIGNATION"  => $libelle,
                      "QUANTITE"     => sprintf( "%.2f", $prod["qte"]) ,
                      "P.U. HT"      => sprintf( "%.2f", $prod["px_unit"]),
                      "MONTANT H.T." => sprintf ( "%.2f", $prod["qte"] * $prod["px_unit"]) ,
                      "TVA"          => $prod["tva"] );
*/
function addLine( $ligne, $tab )
{
    global $colonnes, $format;

    $ordonnee     = 10;
    $maxSize      = $ligne;

    reset( $colonnes );
    while ( list( $lib, $pos ) = each ($colonnes) )
    {
        $longCell  = $pos -2;
        $texte     = $tab[ $lib ];
        $length    = $this->GetStringWidth( $texte );
        $tailleTexte = $this->sizeOfText( $texte, $length );
        $formText  = $format[ $lib ];
        $this->SetXY( $ordonnee, $ligne-1);
        $this->MultiCell( $longCell, 4 , $texte, 0, $formText);
        if ( $maxSize < ($this->GetY()  ) )
            $maxSize = $this->GetY() ;
        $ordonnee += $pos;
    }
    return ( $maxSize - $ligne );
}

function addRemarque($remarque)
{
    $this->SetFont( "Helvetica", "", 10);
    $length = $this->GetStringWidth( "Hinweis : " . $remarque );
    $r1  = 10;
    $r2  = $r1 + $length;
    $y1  = $this->h - 45.5;
    $y2  = $y1+5;
    $this->SetXY( $r1 , $y1 );
    $this->Cell($length, 4, "Hinweis : " . $remarque);
}

function addTotal($val)
{
    $this->SetFont( "Arial", "B", 10);
    $r1  = 10;
    $r2  = $r1 + 120;
    $y1  = $this->h - 70;
    $y2  = $y1+20;
    //$this->Rect($r1, $y1, ($r2 - $r1), ($y2-$y1),  'D');
	
	
	
	
	
	$this->SetXY( $r1+135, $y1);
    $this->MultiCell( 50, 2, 'Endbetrag Euro: '.$val.'', 0, 'R');
	
	/*$partial = $val * 0.19;
	
	$this->SetXY( $r1+140, $y1+7);
    $this->MultiCell( 50, 2, 'Davon MwsT: '.$partial.'', 0, 'R');
	
	$this->SetXY( $r1+140, $y1+14);
    $this->MultiCell( 50, 2, 'Noch was', 0, 'R');
	*/
}

function addTotalLeft($val)
{
    $this->SetFont( "Arial", "B", 10);
    $r1  = 10;
    $r2  = $r1 + 120;
    $y1  = $this->h - 70;
    $y2  = $y1+20;
    
  $this->SetXY( $r1, $y1);
    $this->MultiCell( 120, 2, $val, 0, 'L');
	
	}


function addAdditionalInfo($val)
{
    $this->SetFont( "Arial", "", 11);
    $r1  = 30;
    $r2  = $r1 + 120;
    $y1  = $this->h + 20;
    $y2  = $y1+20;
    
  	$this->SetXY( $r1, $y1);
    $this->MultiCell( 150, 5, $val, 0, 'L');
	

}


function addFooterLeft($val)
{
    $this->SetFont( "Arial", "", 9);
    $r1  = 10;
    $r2  = $r1 + 120;
    $y1  = $this->h - 60;
    $y2  = $y1+20;
    
  	$this->SetXY( $r1, $y1);
    $this->MultiCell( 200, 5, $val, 0, 'L');
	

}

function addCadreEurosFrancs()
{
    $r1  = $this->w - 70;
    $r2  = $r1 + 60;
    $y1  = $this->h - 40;
    $y2  = $y1+20;
    $this->RoundedRect($r1, $y1, ($r2 - $r1), ($y2-$y1), 2.5, 'D');
    $this->Line( $r1+20,   $y1, $r1+20, $y2); // avant EUROS
    $this->Line( $r1+20, $y1+4, $r2, $y1+4); // Sous Euros & Francs
    $this->Line( $r1+38,   $y1, $r1+38, $y2); // Entre Euros & Francs
    $this->SetFont( "Arial", "B", 8);
    $this->SetXY( $r1+22, $y1 );
    $this->Cell(15, 4, "EUROS", 0, 0, "C");
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $r1+42, $y1 );
    $this->Cell(15, 4, "FRANCS", 0, 0, "C");
    $this->SetFont( "Arial", "B", 6);
    $this->SetXY( $r1, $y1+5 );
    $this->Cell(20, 4, "TOTAL TTC", 0, 0, "C");
    $this->SetXY( $r1, $y1+10 );
    $this->Cell(20, 4, "ACOMPTE", 0, 0, "C");
    $this->SetXY( $r1, $y1+15 );
    $this->Cell(20, 4, "NET A PAYER", 0, 0, "C");
}

// remplit les cadres TVA / Totaux et la remarque
// params  = array( "RemiseGlobale" => [0|1],
//                      "remise_tva"     => [1|2...],   // {la remise s'applique sur ce code TVA}
//                      "remise"         => value,      // {montant de la remise}
//                      "remise_percent" => percent,    // {pourcentage de remise sur ce montant de TVA}
//                  "FraisPort"     => [0|1],
//                      "portTTC"        => value,      // montant des frais de ports TTC
//                                                     // par defaut la TVA = 19.6 %
//                      "portHT"         => value,      // montant des frais de ports HT
//                      "portTVA"        => tva_value, // valeur de la TVA a appliquer sur le montant HT
//                  "AccompteExige" => [0|1],
//                      "accompte"         => value    // montant de l'acompte (TTC)
//                      "accompte_percent" => percent  // pourcentage d'acompte (TTC)
//                  "Remarque" => "texte"              // texte
// tab_tva = array( "1"       => 19.6,
//                  "2"       => 5.5, ... );
// invoice = array( "px_unit" => value,
//                  "qte"     => qte,
//                  "tva"     => code_tva );
function addTVAs( $params, $tab_tva, $invoice )
{
    $this->SetFont('Arial', '', 8);
    
    reset ($invoice);
    $px = array();
    while ( list( $k, $prod) = each( $invoice ) )
    {
        $tva = $prod["tva"];
        @ $px[$tva] += $prod["qte"] * $prod["px_unit"];
    }

    $prix     = array();
    $totalHT  = 0;
    $totalTTC = 0;
    $totalTVA = 0;
    $y = 261;
    reset ($px);
    natsort( $px );
    while ( list($code_tva, $articleHT) = each( $px ) )
    {
        $tva = $tab_tva[$code_tva];
        $this->SetXY(17, $y);
        $this->Cell( 19, 4, sprintf("%0.2f", $articleHT), '', '', 'R' );
        if ( $params["RemiseGlobale"]==1 )
        {
            if ( $params["remise_tva"] == $code_tva )
            {
                $this->SetXY( 37.5, $y );
                if ($params["remise"] > 0 )
                {
                    if ( is_int( $params["remise"] ) )
                        $l_remise = $param["remise"];
                    else
                        $l_remise = sprintf ("%.02f", $params["remise"]);
                    $this->Cell( 14.5, 4, $l_remise, '', '', 'R' );
                    $articleHT -= $params["remise"];
                }
                else if ( $params["remise_percent"] > 0 )
                {
                    $rp = $params["remise_percent"];
                    if ( $rp > 1 )
                        $rp /= 100;
                    $rabais = $articleHT * $rp;
                    $articleHT -= $rabais;
                    if ( is_int($rabais) )
                        $l_remise = $rabais;
                    else
                        $l_remise = sprintf ("%.02f", $rabais);
                    $this->Cell( 14.5, 4, $l_remise, '', '', 'R' );
                }
                else
                    $this->Cell( 14.5, 4, "ErrorRem", '', '', 'R' );
            }
        }
        $totalHT += $articleHT;
        $totalTTC += $articleHT * ( 1 + $tva/100 );
        $tmp_tva = $articleHT * $tva/100;
        $a_tva[ $code_tva ] = $tmp_tva;
        $totalTVA += $tmp_tva;
        $this->SetXY(11, $y);
        $this->Cell( 5, 4, $code_tva);
        $this->SetXY(53, $y);
        $this->Cell( 19, 4, sprintf("%0.2f", $tmp_tva), '', '' , 'R');
        $this->SetXY(74, $y);
        $this->Cell( 10, 4, sprintf("%0.2f", $tva) , '', '', 'R');
        $y+=4;
    }

    if ( $params["FraisPort"] == 1 )
    {
        if ( $params["portTTC"] > 0 )
        {
            $pTTC = sprintf("%0.2f", $params["portTTC"]);
            $pHT  = sprintf("%0.2f", $pTTC / 1.196);
            $pTVA = sprintf("%0.2f", $pHT * 0.196);
            $this->SetFont('Arial', '', 6);
            $this->SetXY(85, 261 );
            $this->Cell( 6 , 4, "HT : ", '', '', '');
            $this->SetXY(92, 261 );
            $this->Cell( 9 , 4, $pHT, '', '', 'R');
            $this->SetXY(85, 265 );
            $this->Cell( 6 , 4, "TVA : ", '', '', '');
            $this->SetXY(92, 265 );
            $this->Cell( 9 , 4, $pTVA, '', '', 'R');
            $this->SetXY(85, 269 );
            $this->Cell( 6 , 4, "TTC : ", '', '', '');
            $this->SetXY(92, 269 );
            $this->Cell( 9 , 4, $pTTC, '', '', 'R');
            $this->SetFont('Arial', '', 8);
            $totalHT += $pHT;
            $totalTVA += $pTVA;
            $totalTTC += $pTTC;
        }
        else if ( $params["portHT"] > 0 )
        {
            $pHT  = sprintf("%0.2f", $params["portHT"]);
            $pTVA = sprintf("%0.2f", $params["portTVA"] * $pHT / 100 );
            $pTTC = sprintf("%0.2f", $pHT + $pTVA);
            $this->SetFont('Arial', '', 6);
            $this->SetXY(85, 261 );
            $this->Cell( 6 , 4, "HT : ", '', '', '');
            $this->SetXY(92, 261 );
            $this->Cell( 9 , 4, $pHT, '', '', 'R');
            $this->SetXY(85, 265 );
            $this->Cell( 6 , 4, "TVA : ", '', '', '');
            $this->SetXY(92, 265 );
            $this->Cell( 9 , 4, $pTVA, '', '', 'R');
            $this->SetXY(85, 269 );
            $this->Cell( 6 , 4, "TTC : ", '', '', '');
            $this->SetXY(92, 269 );
            $this->Cell( 9 , 4, $pTTC, '', '', 'R');
            $this->SetFont('Arial', '', 8);
            $totalHT += $pHT;
            $totalTVA += $pTVA;
            $totalTTC += $pTTC;
        }
    }

    $this->SetXY(114, 266.4);
    $this->Cell(15, 4, sprintf("%0.2f", $totalHT), '', '', 'R' );
    $this->SetXY(114, 271.4);
    $this->Cell(15, 4, sprintf("%0.2f", $totalTVA), '', '', 'R' );

    $params["totalHT"] = $totalHT;
    $params["TVA"] = $totalTVA;
    $accompteTTC=0;
    if ( $params["AccompteExige"] == 1 )
    {
        if ( $params["accompte"] > 0 )
        {
            $accompteTTC=sprintf ("%.2f", $params["accompte"]);
            if ( strlen ($params["Remarque"]) == 0 )
                $this->addRemarque( "Accompte de $accompteTTC Euros exigé à la commande.");
            else
                $this->addRemarque( $params["Remarque"] );
        }
        else if ( $params["accompte_percent"] > 0 )
        {
            $percent = $params["accompte_percent"];
            if ( $percent > 1 )
                $percent /= 100;
            $accompteTTC=sprintf("%.2f", $totalTTC * $percent);
            $percent100 = $percent * 100;
            if ( strlen ($params["Remarque"]) == 0 )
                $this->addRemarque( "Accompte de $percent100 % (soit $accompteTTC Euros) exigé à la commande." );
            else
                $this->addRemarque( $params["Remarque"] );
        }
        else
            $this->addRemarque( "Drôle d'acompte !!! " . $params["Remarque"]);
    }
    else
    {
        if ( strlen ($params["Remarque"]) > 0 )
            $this->addRemarque( $params["Remarque"] );
    }
    $re  = $this->w - 50;
    $rf  = $this->w - 29;
    $y1  = $this->h - 40;
    $this->SetFont( "Arial", "", 8);
    $this->SetXY( $re, $y1+5 );
    $this->Cell( 17, 4, sprintf("%0.2f", $totalTTC), '', '', 'R');
    $this->SetXY( $re, $y1+10 );
    $this->Cell( 17, 4, sprintf("%0.2f", $accompteTTC), '', '', 'R');
    $this->SetXY( $re, $y1+14.8 );
    $this->Cell( 17, 4, sprintf("%0.2f", $totalTTC - $accompteTTC), '', '', 'R');
    $this->SetXY( $rf, $y1+5 );
    $this->Cell( 17, 4, sprintf( "%0.2f", $totalTTC * EURO_VAL), '', '', 'R');
    $this->SetXY( $rf, $y1+10 );
    $this->Cell( 17, 4, sprintf("%0.2f", $accompteTTC * EURO_VAL), '', '', 'R');
    $this->SetXY( $rf, $y1+14.8 );
    $this->Cell( 17, 4, sprintf("%0.2f", ($totalTTC - $accompteTTC) * EURO_VAL), '', '', 'R');
}

// add a watermark (temporary estimate, DUPLICATA...)
// call this method first
function temporaire( $texte )
{
    $this->SetFont('Arial', 'B', 50);
    $this->SetTextColor(203, 203, 203);
    $this->Rotate(45, 55, 190);
    $this->Text(55, 190, $texte);
    $this->Rotate(0);
    $this->SetTextColor(0, 0, 0);
}

}
?> 