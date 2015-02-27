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

$title = new OFC_Elements_Title( "Our New House Schedule" );

$hbar = new OFC_Charts_Bar_Horizontal();
$hbar->append_value( new OFC_Charts_Bar_Horizontal_Value(0,4) );
$hbar->append_value( new OFC_Charts_Bar_Horizontal_Value(4,8) );
$hbar->append_value( new OFC_Charts_Bar_Horizontal_Value(8,11) );

$chart = new OFC_Chart();
$chart->set_title( $title );
$chart->add_element( $hbar );
$chart->add_y_axis( new OFC_Elements_Axis_Y() );

$x = new OFC_Elements_Axis_X();
$x->set_offset( false );
$x->set_labels_from_array( array('Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec') );
$chart->set_x_axis( $x );

$y = new OFC_Elements_Axis_Y();
$y->set_offset( true );
$y->set_labels( array( "Make garden look sexy","Paint house","Move into house" ) );
$chart->add_y_axis( $y );

echo $chart->toPrettyString();
