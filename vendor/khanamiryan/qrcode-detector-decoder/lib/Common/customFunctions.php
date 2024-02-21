<?php

if (!function_exists('arraycopy')) {
	function arraycopy($srcArray, $srcPos, $destArray, $destPos, $length)
	{
		$srcArrayToCopy = array_slice($srcArray, $srcPos, $length);
		array_splice($destArray, $destPos, $length, $srcArrayToCopy);

		return $destArray;
	}
}

if (!function_exists('hashCode')) {
	function hashCode($s)
	{
		$h = 0;
		$len = strlen((string) $s);
		for ($i = 0; $i < $len; $i++) {
			$h = (31 * $h + ord($s[$i]));
		}

		return $h;
	}
}

if (!function_exists('numberOfTrailingZeros')) {
	function numberOfTrailingZeros($i)
	{
		if ($i == 0) {
			return 32;
		}
		$num = 0;
		while (($i & 1) == 0) {
			$i >>= 1;
			$num++;
		}

		return $num;
	}
}

if (!function_exists('uRShift')) {
	function uRShift($a, $b)
	{
		static $mask = (8 * PHP_INT_SIZE - 1);
		if ($b === 0) {
			return $a;
		}

		return ($a >> $b) & ~(1 << $mask >> ($b - 1));
	}
}

/*
function sdvig3($num,$count=1){//>>> 32 bit
	$s = decbin($num);

	$sarray  = str_split($s,1);
	$sarray = array_slice($sarray,-32);//32bit

	for($i=0;$i<=1;$i++) {
		array_pop($sarray);
		array_unshift($sarray, '0');
	}
	return bindec(implode($sarray));
}
*/

if (!function_exists('sdvig3')) {
	function sdvig3($a, $b)
	{
		if ($a >= 0) {
			return bindec(decbin($a >> $b)); //simply right shift for positive number
		}

		$bin = decbin($a >> $b);

		$bin = substr($bin, $b); // zero fill on the left side

		return bindec($bin);
	}
}

if (!function_exists('floatToIntBits')) {
	function floatToIntBits($float_val)
	{
		$int = unpack('i', pack('f', $float_val));

		return $int[1];
	}
}


if (!function_exists('fill_array')) {
	function fill_array($index, $count, $value)
	{
		if ($count <= 0) {
			return [0];
		}

		return array_fill($index, $count, $value);
	}
}
