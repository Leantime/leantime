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

// generate some random data
srand((double)microtime()*1000000);

$data_1 = array();
$data_2 = array();
$data_3 = array();
for( $i=0; $i<9; $i++ )
{
	$data_1[] = rand(1,6);
	$data_2[] = rand(7,13);
	$data_3[] = rand(14,19);
}


$line_dot = new OFC_Charts_Line_Dot();
$line_dot->set_width( 4 );
$line_dot->set_colour( '#DFC329' );
$line_dot->set_dot_size( 5 );
$line_dot->set_values( $data_1 );

$line_hollow = new OFC_Charts_Line_Hollow();
$line_hollow->set_width( 1 );
$line_hollow->set_colour( '#6363AC' );
$line_hollow->set_dot_size( 5 );
$line_hollow->set_values( $data_2 );

$line = new OFC_Charts_Line();
$line->set_width( 1 );
$line->set_colour( '#5E4725' );
$line->set_dot_size( 5 );
$line->set_values( $data_3 );

$y = new OFC_Elements_Axis_Y();
$y->set_range( 0, 20, 5 );

$chart = new OFC_Chart();
$chart->set_title( new OFC_Elements_Title( 'Three lines example' ) );
$chart->set_y_axis( $y );
//
// here we add our data sets to the chart:
//
$chart->add_element( $line_dot );
$chart->add_element( $line_hollow );
$chart->add_element( $line );

echo $chart->toPrettyString();
