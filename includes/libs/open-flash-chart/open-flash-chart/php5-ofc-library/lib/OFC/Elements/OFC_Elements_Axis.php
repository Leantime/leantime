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

require_once('OFC/Elements/OFC_Elements_Base.php');

class OFC_Elements_Axis extends OFC_Elements_Base
{
	function OFC_Elements_Axis()
	{
		parent::OFC_Elements_Base();
	}

	function set_colours( $colour, $grid_colour )
	{
		$this->set_colour( $colour );
		$this->set_grid_colour( $grid_colour );
	}

	function set_colour( $colour )
	{
		$this->colour = $colour;
	}

	function set_grid_colour( $colour )
	{
		$this->{'grid-colour'} = $colour;
	}

	function set_steps( $steps=1 )
	{
		$this->steps = $steps;
	}
}

