<?php
class rechnung extends fpdf {

	public $SetRechnungsDatum;
	public $SetRechnungsNummer;
	public $SetVertragsNummer;
	public $SetCurrency;
	public $SetVersandkosten;
	public $SetZeitraum;

	public $SetKundenNummer;
	public $SetKundenFirma;
	public $SetKundenAnrede;
	public $SetKundenName;
	public $SetKundenStrasse;
	public $SetKundenOrt;
	public $SetKundenPLZ;
	public $SetKundenLand;

	private $mwst;
	private $re_zw_summe;
	private $re_summe;

	public $re_format;

	public $SetFootText1;
	public $SetFootText2;
	public $SetFootText3;
	public $SetFootText4;

	private $pos_y;
	private $pos_y_p;

	private $re_w1;
	private $re_w2;
	private $re_w3;
	private $re_w4;
	private $re_w5;

	private $spa_l;
	private $spa_r;


	/* ----------------------------------------------------------------- */

	public function Header() {

		$this->re_w2 = 15;
		$this->re_w3 = 15;
		$this->re_w4 = 25;
		$this->re_w5 = 25;
		$this->re_w1 = $this->w - ($this->lMargin + $this->rMargin + $this->re_w2 + $this->re_w3 + $this->re_w4 + $this->re_w5);
		$this->spa_l = 130;
		$this->spa_r = 50;

		$this->SetLineWidth(0.5); 

		// Firmenanschrift, Logo, Kundenanschrift
		$this->SetTextColor(0, 0, 255);
		$this->SetFont('trebuchet', '', 20);
			$this->Cell($this->spa_l, 10, 'JHDVersicherungen', 'U', 0);
		$this->SetFont('trebuchet', '', 150);
			$this->Cell($this->spa_r, 10, '1', 0, 1);
		$this->SetTextColor(0);
		$this->Ln(5);
		$this->SetFont('trebuchet', '', 20);
		$this->Cell(0, 10, 'Ihr Abschluss', 0, 1);


		// Kundenanschrift
		$this->SetY(70);
		$this->SetFont('trebuchet', 'B', 11);
		if ( $this->SetKundenFirma ) 
			$this->Cell(0, 5, $this->SetKundenFirma, 0, 1);
		if ( $this->SetKundenAnrede ) 
			$this->Cell(0, 5, $this->SetKundenAnrede, 0, 1);
		if ( $this->SetKundenName ) 
			$this->Cell(0, 5, $this->SetKundenName, 0, 1);
		$this->Cell(0, 5, $this->SetKundenStrasse, 0, 1);
		$this->Cell(0, 5, $this->SetKundenPLZ.' '.$this->SetKundenOrt, 0, 1);
		if ( $this->SetKundenLand ) 
			$this->Cell(0, 5, $this->SetKundenLand, 0, 1);


		// Rechnungsdaten
		$this->SetFont('trebuchet', '', 10);
		$this->SetXY($this->spa_l, 45 ); 
			$this->Cell($this->re_w4, 5, 'Rechnungsdatum: ', 0, 0, 'R');
			$this->Cell($this->re_w5 * 2, 5, $this->SetRechnungsDatum, 0, 1, 'R');
		$this->SetXY($this->spa_l, $this->GetY() ); 
			$this->Cell($this->re_w4, 5, 'Rechnungsnummer: ', 0, 0, 'R');
			$this->Cell($this->re_w5 * 2, 5, $this->SetRechnungsNummer, 0, 1, 'R');
		$this->SetXY($this->spa_l, $this->GetY() ); 
			$this->Cell($this->re_w4, 5, 'Kundennummer: ', 0, 0, 'R');
			$this->Cell($this->re_w5 * 2, 5, $this->SetKundenNummer, 0, 1, 'R');
		$this->SetXY($this->spa_l, $this->GetY() ); 
			$this->Cell($this->re_w4, 5, 'Vertragsnummer: ', 0, 0, 'R');
			$this->Cell($this->re_w5 * 2, 5, $this->SetVertragsNummer, 0, 1, 'R');


		// Wort Rechnung erstellen
		$this->SetY(110);
		$this->SetFont('trebuchet', 'B', 14);
		$this->Cell(0, 5, 'Ihre Rechnung', 0, 1);
		$this->Ln(5);


		// Abrechnungszeitraum
		$this->SetFont('trebuchet', '', 10);
		$this->Cell(0, 5, $this->SetZeitraum, 0, 1);
		$this->Ln(5);

		// Spalten der Rechnung
		$this->SetFullLine ( 0.25 );
		$this->SetFont('trebuchet', 'B', 10);
		$this->Cell($this->re_w1, 5, 'Bezeichnung', 0, 0);
		$this->Cell($this->re_w2, 5, 'Einheit', 0, 0, 'R');
		$this->Cell($this->re_w3, 5, 'MwSt', 0, 0, 'R');
		$this->Cell($this->re_w4, 5, 'E-Preis', 0, 0, 'R');
		$this->Cell($this->re_w5, 5, 'Netto', 0, 1, 'R');
		$this->SetFullLine ( 0.25 );
		$this->Ln(5);

	}

	public function Footer() {

		$this->SetLineWidth(0.25);
		$this->Line($this->lMargin, 263, $this->w - $this->rMargin, 263);
		$this->SetLineWidth(0.125);
		$this->Line($this->lMargin, 265, 25, 265);
		$this->Line(30, 265, $this->w - $this->rMargin, 265);
		$this->Line(30, 265, 30, 268);
		$this->Line(70, 265, 70, 268);
		$this->Line(110, 265, 110, 268);
		$this->Line(150, 265, 150, 268);

		$this->SetFont('trebuchet','',8);
		$this->SetTextColor(150);

		$this->SetXY(30, 270);
		$this->MultiCell( 0, 5, trim(str_replace("\t",'', $this->SetFootText1)) );
		$this->SetXY(70, 270);
		$this->MultiCell( 0, 5, trim(str_replace("\t",'', $this->SetFootText2)) );
		$this->SetXY(110, 270);
		$this->MultiCell( 0, 5, trim(str_replace("\t",'', $this->SetFootText3)) );
		$this->SetXY(150, 270);
		$this->MultiCell( 0, 5, trim(str_replace("\t",'', $this->SetFootText4)) );
	}


	/* ----------------------------------------------------------------- */

	public function SetProductName ( $str ) {

		if ( $this->GetY() > ($this->h - 50) ) { $this->AddPage(); }

		$str = str_replace("\t",'', $str);

		$this->pos_y = $this->pos_y_p = $this->GetY();

		$this->MultiCell($this->re_w1, 6, $str);

		$this->SetLineWidth(0.1);
		$this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());
		$this->Ln(1);

		$this->pos_y = $this->GetY();

	}

	public function SetProductPrice ( $price , $anz = 1, $mwst = 19 , $vpe = '') {

		// falls Preisformat nicht gesetzt wurde
		if ( !is_array($this->re_format) ) $this->SetPriceFormat( 2, ',', '' );

		$zw_sum = $anz * $price;
		$this->re_zw_summe += $zw_sum;

		// mwst
		$set_mwst = $mwst / 100;
		$this->mwst[ $mwst ][] = $zw_sum * $set_mwst;

		$this->SetXY( $this->re_w1 + $this->lMargin, $this->pos_y_p);

		$this->Cell($this->re_w2, 5, $anz, 0, 0, 'R');
		$this->Cell($this->re_w3, 5, $mwst.'%', 0, 0, 'R');
		$this->Cell($this->re_w4, 5, number_format($price, $this->re_format[0], $this->re_format[1], $this->re_format[2]), 0, 0, 'R');
		$this->Cell($this->re_w5, 5, number_format($zw_sum, $this->re_format[0], $this->re_format[1], $this->re_format[2]).' '.$this->SetCurrency, 0, 0, 'R');

		$this->SetXY($this->lMargin, $this->pos_y);

	}

	public function SetEnd ( $hinweis = '' ) {

		if ( $this->GetY() > ($this->h - 50) ) { $this->AddPage(); }

		$this->SetFullLine ( 0.25 );
		$this->Ln(5);

		$zw_summe = $this->re_zw_summe;
		$out_zw_summe = number_format( $zw_summe , $this->re_format[0], $this->re_format[1], $this->re_format[2]);

		$this->SetFont('trebuchet', '', 10);
		$this->Cell($this->spa_l, 5, 'Zwischensumme (Netto)', 0, 0, 'L');
		$this->Cell($this->spa_r, 5, $out_zw_summe.' '.$this->SetCurrency, 0, 1, 'R');

		$total_summe = $zw_summe;
		$mwst_summe = 0;
		$mwst_total = 0;

		foreach( $this->mwst as $key => $val ) {

			$mwst_summe = array_sum($val);
			$mwst_total += $mwst_summe;

			$out_mwst_summe = number_format( $mwst_summe , $this->re_format[0], $this->re_format[1], $this->re_format[2]);
			$this->Cell($this->spa_l, 5, '+ Mehrwertsteuer '.$key.'% ', 0, 0, 'L');
			$this->Cell($this->spa_r, 5, $out_mwst_summe.' '.$this->SetCurrency, 0, 1, 'R');

		}

		if ( $this->SetVersandkosten > 0 ) {
			$out_versand = number_format( $this->SetVersandkosten , $this->re_format[0], $this->re_format[1], $this->re_format[2]);
			$this->Cell($this->spa_l, 5, '+ Versandkosten ', 0, 0, 'L');
			$this->Cell($this->spa_r, 5, $out_versand.' '.$this->SetCurrency, 0, 1, 'R');
		}

		// alles zusammen rechnen
		$total_summe += $mwst_total;
		$total_summe += $this->SetVersandkosten;

		$out_total_summe = number_format( $total_summe , $this->re_format[0], $this->re_format[1], $this->re_format[2]);

		$this->SetFullLine ( 0.25 );
		$this->Ln(5);

		$this->SetFont('trebuchet', 'B', 12);
		$this->Cell($this->spa_l, 5, 'Zu zahlender Betrag', 0, 0, 'L');
		$this->Cell($this->spa_r, 5, $out_total_summe.' '.$this->SetCurrency, 0, 1, 'R');
		$this->Ln(5);

		$this->SetFont('trebuchet', '', 10);
		$this->MultiCell(0, 5, trim(str_replace("\t",'', $hinweis)) , 0, 'L');


	}

	public function SetPriceFormat( $dez, $point, $str ) {

		$this->re_format = Array( $dez, $point, $str);

	}

	/* ----------------------------------------------------------------- */

	public function SetFullLine ( $height ) {

		$this->SetLineWidth( $height );
		$this->Line($this->lMargin, $this->GetY(), $this->w - $this->rMargin, $this->GetY());

	}
}
?>
