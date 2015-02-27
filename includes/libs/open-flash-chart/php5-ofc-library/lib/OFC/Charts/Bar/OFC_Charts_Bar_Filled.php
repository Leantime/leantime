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

require_once('OFC/Charts/OFC_Charts_Bar.php');

class OFC_Charts_Bar_Filled_Value extends OFC_Charts_Bar_Value
{
	function OFC_Charts_Bar_Filled_Value( $val, $colour )
	{
		parent::OFC_Charts_Bar_Value( $val, $colour );
	}

	function set_outline_colour( $outline_colour )
	{
		$this->{'outline-colour'} = $outline_colour;
	}
}

class OFC_Charts_Bar_Filled extends OFC_Charts_Bar
{
	function OFC_Charts_Bar_Filled( $colour=null, $outline_colour=null )
	{
		parent::OFC_Charts_Bar();

		$this->type      = 'bar_filled';

		if( isset( $colour ) )
		{
			$this->set_colour( $colour );
		}

		if( isset( $outline_colour ) )
		{
			$this->set_outline_colour( $outline_colour );
		}
	}

	function set_outline_colour( $outline_colour )
	{
		$this->{'outline-colour'} = $outline_colour;
	}
}

