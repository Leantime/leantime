<?php
require('PDF_Label.php');

/*------------------------------------------------
To create the object, 2 possibilities:
either pass a custom format via an array
or use a built-in AVERY name
------------------------------------------------*/

// Example of custom format
//$pdf = new PDF_Label((array('paper-size'=>'A4',		'metric'=>'mm',	'marginLeft'=>7,		'marginTop'=>15, 		'NX'=>3,	'NY'=>7,	'SpaceX'=>25,		'SpaceY'=>25,	'width'=>99.1,		'height'=>38.1,		'font-size'=>9));

$format = 'L7160';

if(isset($_GET['format']) === true){
	$format = $_GET['format'];
}
// Standard format
$pdf = new PDF_Label($format);

$pdf->AddPage();

// Print labels
for($i=1;$i<=20;$i++) {
	$text = sprintf("%s\n%s\n%s\n%s %s, %s", "Laurent $i", 'Immeuble Toto', 'av. Fragonard', '123456', 'NICE', 'FRANCE');
	$pdf->Add_Label($text);
}

$pdf->Output();
?>
