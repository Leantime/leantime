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

require_once('OFC/Charts/OFC_Charts_Area.php');

class OFC_Charts_Area_Hollow extends OFC_Charts_Area
{
	function OFC_Charts_Area_Hollow()
	{
		parent::OFC_Charts_Area();

		$this->type      = 'area_hollow';

		$this->{'fill-alpha'} = 0.35;

		$this->values    = array();
	}

	function set_width( $w )
	{
		$this->width     = $w;
	}

	function set_colour( $colour )
	{
		$this->colour = $colour;
	}

	function set_values( $v ) {
		$this->values = $v;
	}

	function set_dot_size( $size )
	{
		$this->{'dot-size'} = $size;
	}

	function set_key( $text, $font_size )
	{
		$this->text      = $text;
		$this->{'font-size'} = $font_size;
	}
}

