<?php
/**
 * PHP Integration of Open Flash Chart
 * Copyright (C) 2008 John Glazebrook <open-flash-chart@teethgrinder.co.uk>
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
 */

require_once('OFC/OFC_Chart.php');

$data_1 = array();
$data_2 = array();
$data_3 = array();

for( $i=0; $i<6.2; $i+=0.2 )
{
	$data_1[] = (sin($i) * 1.9) + 7;
	$data_2[] = (sin($i) * 1.9) + 10;
	$data_3[] = (sin($i) * 1.9) + 4;

	// just show to two decimal places
	// in our labels:
	//$labels[] = number_format($tmp,2);
}

$title = new OFC_Elements_Title( date("D M d Y") );

$line_1 = new OFC_Charts_Line_Hollow();
$line_1->set_values( $data_1 );
$line_1->set_halo_size( 0 );
$line_1->set_width( 2 );
$line_1->set_dot_size( 5 );

$line_2 = new OFC_Charts_Line_Hollow();
$line_2->set_values( $data_2 );
$line_2->set_halo_size( 1 );
$line_2->set_width( 1 );
$line_2->set_dot_size( 4 );

$line_3 = new OFC_Charts_Line_Hollow();
$line_3->set_values( $data_3 );
$line_3->set_halo_size( 1 );
$line_3->set_width( 6 );
$line_3->set_dot_size( 4 );

$y = new OFC_Elements_Axis_Y();
$y->set_range( 0, 15, 5 );


$chart = new OFC_Chart();
$chart->set_title( $title );
$chart->add_element( $line_1 );
$chart->add_element( $line_2 );
$chart->add_element( $line_3 );
$chart->set_y_axis( $y );

echo $chart->toPrettyString();
