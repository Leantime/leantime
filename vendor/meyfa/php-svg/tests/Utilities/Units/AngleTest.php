<?php

namespace SVG;

use SVG\Utilities\Units\Angle;

/**
 * @SuppressWarnings(PHPMD)
 */
class AngleTest extends \PHPUnit\Framework\TestCase
{
    public function testConvert()
    {
        // degrees
        $this->assertEquals(15.5, Angle::convert('15.5deg'));
        $this->assertEquals(-3600, Angle::convert('-3600deg'));
        $this->assertEquals(400, Angle::convert('+400deg'));

        // radians
        $this->assertEquals(0, Angle::convert('0rad'));
        $this->assertEquals(57.295779513, Angle::convert('1rad'), '', 0.00000001);
        $this->assertEquals(180, Angle::convert('3.14159265359rad'), 0.00000001);

        // gradians
        $this->assertEquals(0, Angle::convert('0grad'));
        $this->assertEquals(360, Angle::convert('400grad'));
        $this->assertEquals(-720, Angle::convert('-800grad'));

        // turns
        $this->assertEquals(0, Angle::convert('0turn'));
        $this->assertEquals(-360, Angle::convert('-1turn'));
        $this->assertEquals(540, Angle::convert('1.5turn'));

        // no unit
        $this->assertEquals(15.5, Angle::convert('15.5'));

        // number
        $this->assertEquals(15.5, Angle::convert(15.5));

        // illegal: missing number
        $this->assertNull(Angle::convert('deg'));
        $this->assertNull(Angle::convert(''));
    }
}
