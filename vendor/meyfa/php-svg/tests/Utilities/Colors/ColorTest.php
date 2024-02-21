<?php

namespace SVG;

use SVG\Utilities\Colors\Color;

/**
 * @SuppressWarnings(PHPMD)
 */
class ColorTest extends \PHPUnit\Framework\TestCase
{
    public function testParse()
    {
        // named colors
        $this->assertEquals(array(0, 0, 0, 255), Color::parse('black'));
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('white'));
        $this->assertEquals(array(250, 128, 114, 255), Color::parse('salmon'));

        // transparency
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('transparent'));

        // invalid color name
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('doesnotexist'));

        // hex 3 (#RGB)
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('#FFF'));
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('#fff'));
        $this->assertEquals(array(0, 0, 0, 255), Color::parse('#000'));
        $this->assertEquals(array(136, 170, 204, 255), Color::parse('#8AC'));
        // hex 4 (#RGBA)
        $this->assertEquals(array(255, 255, 255, 153), Color::parse('#FFF9'));
        $this->assertEquals(array(255, 255, 255, 153), Color::parse('#fff9'));
        $this->assertEquals(array(0, 0, 0, 170), Color::parse('#000A'));
        $this->assertEquals(array(136, 170, 204, 187), Color::parse('#8ACB'));
        // hex 6 (#RRGGBB)
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('#FFFFFF'));
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('#ffffff'));
        $this->assertEquals(array(0, 0, 0, 255), Color::parse('#000000'));
        $this->assertEquals(array(137, 171, 205, 255), Color::parse('#89ABCD'));
        // hex 8 (#RRGGBBAA)
        $this->assertEquals(array(255, 255, 255, 153), Color::parse('#FFFFFF99'));
        $this->assertEquals(array(255, 255, 255, 153), Color::parse('#ffffff99'));
        $this->assertEquals(array(0, 0, 0, 170), Color::parse('#000000AA'));
        $this->assertEquals(array(137, 171, 205, 187), Color::parse('#89ABCDBB'));

        // invalid hex
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('#FF'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('#FFFFFFF'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('#GGG'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('#GGGGGG'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('##FFFFFF'));

        // rgb(a) - without alpha component
        // - standard
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('rgb(255, 255, +255)'));
        $this->assertEquals(array(0, 0, 0, 255), Color::parse('rgb(0, 0, 0)'));
        // - percentages
        $this->assertEquals(array(255, 127, 0, 255), Color::parse('rgb(100%, +50%, 0%)'));
        // - delimiters
        $this->assertEquals(array(136, 170, 204, 255), Color::parse('rgba(136,170,204)'));
        $this->assertEquals(array(136, 170, 204, 255), Color::parse('rgb(  136  ,  170  ,  204  )'));
        $this->assertEquals(array(136, 170, 204, 255), Color::parse('rgb(136 170.3 204.9)'));
        // - out of range
        $this->assertEquals(array(255, 0, 255, 255), Color::parse('rgb(255, -10, 1000)'));
        // - floating point
        $this->assertEquals(array(136, 170, 204, 255), Color::parse('rgb(136, +170.3, 204.9)'));
        // - rgba keyword (alias)
        $this->assertEquals(array(136, 170, 204, 255), Color::parse('rgba(136, 170.3, 204.9)'));
        $this->assertEquals(array(136, 170, 204, 255), Color::parse('rgba(136 170.3, 204.9)'));
        // rgb(a) - with alpha component
        // - standard
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('rgb(255, 255, 255, 1)'));
        $this->assertEquals(array(255, 255, 255, 127), Color::parse('rgb(255, 255, 255, 0.5)'));
        $this->assertEquals(array(255, 255, 255, 127), Color::parse('rgb(255, 255, 255, +.5)'));
        // - percentages
        $this->assertEquals(array(255, 127, 0, 127), Color::parse('rgb(100%, 50%, 0%, 50%)'));
        // - delimiters
        $this->assertEquals(array(136, 170, 204, 127), Color::parse('rgb(136,170,204,.5)'));
        $this->assertEquals(array(136, 170, 204, 255), Color::parse('rgb(  136  ,  170  ,  204  ,  1  )'));
        $this->assertEquals(array(136, 170, 204, 127), Color::parse('rgb(136 170 204 .5)'));
        $this->assertEquals(array(136, 170, 204, 127), Color::parse('rgb(136, 170 204 / .5)'));
        $this->assertEquals(array(136, 170, 204, 127), Color::parse('rgb(136, 170 204 / 50%)'));
        // - out of range
        $this->assertEquals(array(255, 0, 255, 255), Color::parse('rgb(255, -10, 1000, 1.9)'));
        $this->assertEquals(array(255, 0, 0, 0), Color::parse('rgb(255, -10, -10, -.1)'));
        // rgba keyword (alias)
        $this->assertEquals(array(255, 255, 255, 127), Color::parse('rgba(255, 255, 255, .5)'));
        $this->assertEquals(array(0, 0, 0, 127), Color::parse('rgba(0, 0, 0, 50%)'));
        $this->assertEquals(array(255, 255, 255, 127), Color::parse('rgba(100%, 100%, 100% / 50%)'));

        // invalid rgb(a)
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('rgb(136, 170)'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('rgb(136, , 204)'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('rgb (136, 170, 204)'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('rgb(136, 170, 204'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('rgba(136, 170, 204, )'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('rgb (136, 170, 204, 0.5)'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('rgb(136, 170, 204, 0.5'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('rgba(136, 170, 204, 0.5, )'));

        // hsl(a) - without alpha component
        // - standard
        $this->assertEquals(array(0, 0, 0, 255), Color::parse('hsl(0, 0%, 0%)'));
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('hsl(0, 0%, 100%)'));
        $this->assertEquals(array(255, 0, 0, 255), Color::parse('hsl(0, 100%, 50%)'));
        $this->assertEquals(array(0, 255, 0, 255), Color::parse('hsl(120, 100%, 50%)'));
        $this->assertEquals(array(0, 0, 255, 255), Color::parse('hsl(240, 100%, 50%)'));
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(90, 10%, 35%)'));
        // - units
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(90deg, 10%, 35%)'));
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(1.570796327rad, 10%, 35%)'));
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(100grad, 10%, 35%)'));
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(.25turn, 10%, 35%)'));
        // - delimiters
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(90deg,10%,35%)'));
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(  90deg , 10% , 35%  )'));
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(90deg 10% 35%)'));
        // - out of range
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(3690deg,10%,35%)'));
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsl(-3510deg,10%,35%)'));
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('hsl(0, -50%, +200%)'));
        // - hsla keyword (alias)
        $this->assertEquals(array(127, 127, 127, 255), Color::parse('hsla(0, 0%, 50%)'));
        $this->assertEquals(array(255, 255, 255, 255), Color::parse('hsla(0, 0%, 100%)'));
        $this->assertEquals(array(89, 98, 80, 255), Color::parse('hsla(90, 10%, 35%)'));
        // hsl(a) - with alpha component
        // - standard
        $this->assertEquals(array(0, 0, 0, 255), Color::parse('hsl(0, 0%, 0%, 1)'));
        $this->assertEquals(array(255, 255, 255, 127), Color::parse('hsl(0, 0%, 100%, +.5)'));
        // - percentages
        $this->assertEquals(array(255, 255, 255, 127), Color::parse('hsl(0, 0%, 100%, 50%)'));
        // - delimiters
        $this->assertEquals(array(255, 0, 0, 127), Color::parse('hsl(  0 100% 50% .5  )'));
        $this->assertEquals(array(255, 0, 0, 127), Color::parse('hsl(0 100%, 50% / .5)'));
        $this->assertEquals(array(255, 0, 0, 127), Color::parse('hsl(0 100%, 50% / 50%)'));
        // - out of range
        $this->assertEquals(array(0, 255, 0, 0), Color::parse('hsl(120, 100%, 50%, -.1)'));
        $this->assertEquals(array(0, 255, 0, 255), Color::parse('hsl(120, 100%, 50%, 1.9)'));
        // - hsla keyword (alias)
        $this->assertEquals(array(255, 0, 0, 127), Color::parse('hsla(  0 100% 50% .5  )'));
        $this->assertEquals(array(255, 0, 0, 127), Color::parse('hsla(0 100%, 50% / .5)'));
        $this->assertEquals(array(255, 0, 0, 127), Color::parse('hsla(0 100%, 50% / 50%)'));

        // invalid hsl(a)
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('hsl(deg, 100%, 0%)'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('hsl(90, 100%)'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('hsl (90, 10%, 35%)'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('hsl(90, 10%, 35%'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('hsla(90, 10%, 35%, )'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('hsl (90, 10%, 35%, 0.5)'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('hsl(136, 170, 204, 0.5'));
        $this->assertEquals(array(0, 0, 0, 0), Color::parse('hsla(136, 170, 204, 0.5, )'));
    }
}
