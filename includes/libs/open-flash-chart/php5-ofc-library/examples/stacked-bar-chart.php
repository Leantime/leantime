<?php

require_once('OFC/OFC_Chart.php');

$title = new OFC_Elements_Title( date("D M d Y") );

$bar_stack = new OFC_Charts_Bar_Stack();
$bar_stack->append_stack( array( 2.5, 5 ) );
$bar_stack->append_stack( array( 7.5 ) );
$bar_stack->append_stack( array( 5, new OFC_Charts_Bar_Stack_Value(5, '#ff0000') ) );
$bar_stack->append_stack( array( 2, 2, 2, 2, new OFC_Charts_Bar_Stack_Value(2, '#ff00ff') ) );

$y = new OFC_Elements_Axis_Y();
$y->set_range( 0, 14, 7 );

$x = new OFC_Elements_Axis_X();
$x->set_labels( array( 'a', 'b', 'c', 'd' ) );

$chart = new OFC_Chart();
$chart->set_title( $title );
$chart->add_element( $bar_stack );
$chart->set_x_axis( $x );
$chart->add_y_axis( $y );

echo $chart->toPrettyString();
