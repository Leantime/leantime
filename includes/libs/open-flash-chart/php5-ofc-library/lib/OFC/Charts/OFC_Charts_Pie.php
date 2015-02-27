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

require_once('OFC/Charts/OFC_Charts_Base.php');

class OFC_Charts_Pie_Value
{
	function OFC_Charts_Pie_Value( $value, $text )
	{
		$this->value = $value;
		$this->text = $text;
	}
}

class OFC_Charts_Pie extends OFC_Charts_Base
{
	function OFC_Charts_Pie()
	{
		parent::OFC_Charts_Base();

		$this->type             = 'pie';
		$this->colours          = array("#d01f3c","#356aa0","#C79810");
		$this->alpha			= 0.6;
		$this->border			= 2;
		$this->values			= array(2,3,new OFC_Charts_Pie_Value(6.5, 'hello (6.5)'));
	}

	// boolean
	function set_animate( $v )
	{
		$this->animate = $v;
	}

	// real
	function set_start_angle( $angle )
	{
		$this->{'start-angle'} = $angle;
	}
}

