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

$chart = new OFC_Chart();

$title = new OFC_Elements_Title( date("D M d Y") );
$chart->set_title( $title );

$scatter = new OFC_Charts_Scatter( '#FFD600', 10 );
$scatter->set_values(
array(
new OFC_Charts_Scatter_Value( 0, 0 )
)
);

$chart->add_element( $scatter );


//
// plot a circle
//
$s2 = new OFC_Charts_Scatter( '#D600FF', 3 );
$v = array();

for( $i=0; $i<360; $i+=5 )
{
	$v[] = new OFC_Charts_Scatter_Value(
	number_format(sin(deg2rad($i)), 2, '.', ''),
	number_format(cos(deg2rad($i)), 2, '.', '') );
}
$s2->set_values( $v );
$chart->add_element( $s2 );

$x = new OFC_Elements_Axis_X();
$x->set_range( -2, 2 );
$chart->set_x_axis( $x );

$y = new OFC_Elements_Axis_Y();
$y->set_range( -2, 2 );
$chart->add_y_axis( $y );


echo $chart->toPrettyString();

