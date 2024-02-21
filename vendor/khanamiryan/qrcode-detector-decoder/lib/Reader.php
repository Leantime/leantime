<?php

namespace Zxing;

interface Reader
{
	public function decode(BinaryBitmap $image);

	public function reset();
}
