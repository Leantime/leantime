<?php

namespace SVG;

use SVG\Utilities\Units\Length;

/**
 * @SuppressWarnings(PHPMD)
 */
class LengthTest extends \PHPUnit\Framework\TestCase
{
    public function testConvert()
    {
        // units
        $this->assertEquals(16, Length::convert('12pt', 100));
        $this->assertEquals(16, Length::convert('1pc', 100));
        $this->assertEquals(37.8, Length::convert('1cm', 100), '', 0.01);
        $this->assertEquals(37.8, Length::convert('10mm', 100), '', 0.01);
        $this->assertEquals(96, Length::convert('1in', 100));
        $this->assertEquals(50, Length::convert('50%', 100));
        $this->assertEquals(16, Length::convert('16px', 100));

        // no unit
        $this->assertEquals(16, Length::convert('16', 100));

        // number
        $this->assertEquals(16, Length::convert(16, 100));

        // illegal: missing number
        $this->assertNull(Length::convert('px', 100));
        $this->assertNull(Length::convert('', 100));
    }
}
