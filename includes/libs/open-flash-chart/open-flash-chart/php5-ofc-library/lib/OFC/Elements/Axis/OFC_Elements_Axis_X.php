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

require_once('OFC/Elements/OFC_Elements_Axis.php');
require_once('OFC/Elements/Axis/OFC_Elements_Axis_X_Label_Set.php');

class OFC_Elements_Axis_X extends OFC_Elements_Axis
{
	function OFC_Elements_Axis_X()
	{
		parent::OFC_Elements_Axis();
	}

	function set_stroke( $stroke )
	{
		$this->stroke = $stroke;
	}

	function set_tick_height( $height )
	{
		$this->{'tick-height'} = $height;
	}

	// $o is a boolean
	function set_offset( $o )
	{
		$this->offset = ($o) ? true : false;
	}

	function set_3d( $val )
	{
		$this->{'3d'} = $val;
	}

	function set_labels( $x_axis_labels )
	{
		$this->labels = $x_axis_labels;
	}

	function set_range( $min, $max, $steps=1 )
	{
		$this->min = $min;
		$this->max = $max;
		$this->set_steps( $steps );
	}

	/**
	 * helper function to make the examples
	 * simpler.
	 */
	function set_labels_from_array( $a )
	{
		$x_axis_labels = new OFC_Elements_Axis_X_Label_Set();
		$x_axis_labels->set_labels( $a );

		$this->labels = $x_axis_labels;

		if( isset( $this->steps ) )
		{
			$x_axis_labels->set_steps( $this->steps );
		}
	}
}

