<?php


$data = array();

for( $i=0; $i<6.2; $i+=0.2 )
{
	$tmp = sin($i) * 1.9;
	$data[] = $tmp;
}

require_once('OFC/OFC_Chart.php');

$chart = new OFC_Chart();
$chart->set_title( new OFC_Elements_Title( 'Area Chart' ) );

//
// Make our area chart:
//
$area = new OFC_Charts_Area_Hollow();
// set the circle line width:
$area->set_width( 1 );
$area->set_values( $data );
// add the area object to the chart:
$chart->add_element( $area );

$y_axis = new OFC_Elements_Axis_Y();
$y_axis->set_range( -2, 2, 2 );
$y_axis->labels = null;
$y_axis->set_offset( false );

$x_axis = new OFC_Elements_Axis_X();
$x_axis->labels = $data;
$x_axis->set_steps( 2 );

$x_labels = new OFC_Elements_Axis_X_Label_Set();
$x_labels->set_steps( 4 );
$x_labels->set_vertical();
// Add the X Axis Labels to the X Axis
$x_axis->set_labels( $x_labels );



$chart->add_y_axis( $y_axis );
$chart->x_axis = $x_axis;

echo $chart->toPrettyString();

