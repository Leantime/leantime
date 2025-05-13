<?php

namespace Test\Unit;

class ExampleTest extends \Unit\TestCase
{
    public function test_example(): void
    {
        // A simple test to demonstrate the testing process
        $this->assertTrue(true);
    }

    public function test_string_operations(): void
    {
        // A slightly more complex test
        $string = 'Hello, Leantime!';
        $this->assertEquals('Hello, Leantime!', $string);
        $this->assertStringContainsString('Leantime', $string);
        $this->assertStringStartsWith('Hello', $string);
        $this->assertStringEndsWith('!', $string);
    }
}
