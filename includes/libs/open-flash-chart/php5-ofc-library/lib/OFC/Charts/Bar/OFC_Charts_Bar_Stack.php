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

class OFC_Charts_Bar_Stack_Value
{
	function OFC_Charts_Bar_Stack_Value( $val, $colour )
	{
		$this->val = $val;
		$this->colour = $colour;
	}
}

class OFC_Charts_Bar_Stack extends OFC_Charts_Bar
{
	function OFC_Charts_Bar_Stack()
	{
		parent::OFC_Charts_Bar();

		$this->type      = 'bar_stack';
	}

	function append_stack( $v )
	{
		$this->append_value( $v );
	}
}

