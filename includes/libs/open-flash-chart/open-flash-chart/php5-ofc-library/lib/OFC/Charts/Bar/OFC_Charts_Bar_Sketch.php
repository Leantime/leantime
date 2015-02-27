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

class OFC_Charts_Bar_Sketch extends OFC_Charts_Bar
{
	function OFC_Charts_Bar_Sketch( $colour, $outline_colour, $fun_factor )
	{
		parent::OFC_Charts_Bar();

		$this->type      = 'bar_sketch';

		$this->set_colour( $colour );
		$this->set_outline_colour( $outline_colour );
		$this->offset = $fun_factor;
	}

	function set_outline_colour( $outline_colour )
	{
		$this->{'outline-colour'} = $outline_colour;
	}
}

