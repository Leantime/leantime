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

// Pretty print some JSON
function json_format($json)
{
	$tab = "  ";
	$new_json = "";
	$indent_level = 0;
	$in_string = false;

	/*
	 commented out by monk.e.boy 22nd May '08
	 because my web server is PHP4, and
	 json_* are PHP5 functions...

	 $json_obj = json_decode($json);

	 if($json_obj === false)
	 return false;

	 $json = json_encode($json_obj);
	 */
	$len = strlen($json);

	for($c = 0; $c < $len; $c++)
	{
		$char = $json[$c];
		switch($char)
		{
			case '{':
			case '[':
				if(!$in_string)
				{
					$new_json .= $char . "\n" . str_repeat($tab, $indent_level+1);
					$indent_level++;
				}
				else
				{
					$new_json .= $char;
				}
				break;
			case '}':
			case ']':
				if(!$in_string)
				{
					$indent_level--;
					$new_json .= "\n" . str_repeat($tab, $indent_level) . $char;
				}
				else
				{
					$new_json .= $char;
				}
				break;
			case ',':
				if(!$in_string)
				{
					$new_json .= ",\n" . str_repeat($tab, $indent_level);
				}
				else
				{
					$new_json .= $char;
				}
				break;
			case ':':
				if(!$in_string)
				{
					$new_json .= ": ";
				}
				else
				{
					$new_json .= $char;
				}
				break;
			case '"':
				if($c > 0 && $json[$c-1] != '\\')
				{
					$in_string = !$in_string;
				}
			default:
				$new_json .= $char;
				break;
		}
	}

	return $new_json;
}

