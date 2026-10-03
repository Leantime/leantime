<?php

namespace Unit\app\Core\Support;

use Carbon\CarbonImmutable;
use Leantime\Core\Support\CarbonMacros;
use Leantime\Core\Support\Format;
use Tests\DateTimeHelper;
use Tests\Language;
use Tests\MockObject;
use Unit\TestCase;

class FormatTest extends TestCase
{
    /**
     * @var DateTimeHelper|MockObject
     */
    private $carbonMacrosMock;

    /**
     * @var Language|MockObject
     */
    private $languageMock;

    protected function setUp(): void
    {

        parent::setUp();

        $this->languageMock = $this->createMock(\Leantime\Core\Language::class);
        app()->instance(\Leantime\Core\Support\CarbonMacros::class, $this->carbonMacrosMock);
        app()->instance(\Leantime\Core\Language::class, $this->languageMock);

        // America Los_Angeles is UTC - 8 so all db times need to come back from UTC - 8 hours
        CarbonImmutable::mixin(new CarbonMacros(
            'America/Los_Angeles',
            'en-US',
            'm/d/Y',
            'h:i A'
        ));

    }

    public function test_date(): void
    {
        $formattedDateString = '12/31/2021';
        $dbDate = '2022-01-01 00:00:00';
        $format = new Format($dbDate, '');

        $this->assertSame($formattedDateString, $format->date());
    }

    public function test_time(): void
    {
        $formattedTimeString = '04:00 PM';
        $dbDate = '2022-01-01 00:00:00';
        $format = new Format($dbDate, '');

        $this->assertSame($formattedTimeString, $format->time());
    }

    public function test_time24(): void
    {
        $formattedTimeString = '16:00';
        $dbDate = '2022-01-01 00:00:00';
        $format = new Format($dbDate, '');

        $this->assertSame($formattedTimeString, $format->time24());

    }

    /**
     * @return array<string, array{0: mixed, 1: string}>
     */
    public static function hoursMinutesCases(): array
    {
        return [
            'quarter hour' => [1.25, '1h 15m'],
            'numeric string from db' => ['1.25', '1h 15m'],
            'looks like minutes but is not' => ['0.26', '0h 16m'],
            'whole hours' => [8, '8h 00m'],
            'zero' => [0, '0h 00m'],
            'rounds to the next hour' => [1.999, '2h 00m'],
            'negative difference' => [-0.5, '-0h 30m'],
            'empty' => ['', ''],
            'null' => [null, ''],
            'not a number' => ['abc', ''],
        ];
    }

    /**
     * @dataProvider hoursMinutesCases
     */
    public function test_hours_minutes(mixed $decimalHours, string $expected): void
    {
        $this->assertSame($expected, Format::hoursMinutes($decimalHours));
    }

    /**
     * @return array<string, array{0: ?string, 1: string}>
     */
    public static function plainTextCases(): array
    {
        return [
            'null' => [null, ''],
            'empty' => ['', ''],
            'plain text untouched' => ['Just text', 'Just text'],
            'paragraphs keep a separator' => ['<p>First</p><p>Second</p>', 'First Second'],
            'line breaks and lists' => ['<ul><li>One</li><li>Two</li></ul>Line<br/>Break', 'One Two Line Break'],
            'inline markup does not split words' => ['<p>A <strong>bold</strong>ly <a href="x">link</a></p>', 'A boldly link'],
            'entities decoded' => ['<p>Fish &amp; chips&nbsp;&lt;3 &quot;q&quot;</p>', 'Fish & chips <3 "q"'],
            'whitespace collapsed' => ["  <p>a\n\n   b</p>\t", 'a b'],
            'scripts lose their tags' => ['<script>alert(1)</script>ok', 'alert(1)ok'],
        ];
    }

    /**
     * @dataProvider plainTextCases
     */
    public function test_plain_text(?string $html, string $expected): void
    {
        $this->assertSame($expected, Format::plainText($html));
    }

    /**
     * @return array<string, array{0: string, 1: string}>
     */
    public static function spreadsheetSafeCases(): array
    {
        return [
            'empty' => ['', ''],
            'plain text' => ['Fix the login', 'Fix the login'],
            'formula' => ['=HYPERLINK("http://x","y")', "'=HYPERLINK(\"http://x\",\"y\")"],
            'plus' => ['+1+1', "'+1+1"],
            'minus' => ['-2+3', "'-2+3"],
            'at' => ['@SUM(A1)', "'@SUM(A1)"],
            'tab' => ["\t=1", "'\t=1"],
            'formula char later is fine' => ['a=b', 'a=b'],
        ];
    }

    /**
     * @dataProvider spreadsheetSafeCases
     */
    public function test_spreadsheet_safe(string $value, string $expected): void
    {
        $this->assertSame($expected, Format::spreadsheetSafe($value));
    }

    public function test_hours_minutes_uses_translated_pattern(): void
    {
        $this->assertSame('1 Std. 15 Min.', Format::hoursMinutes(1.25, '%s Std. %s Min.'));
    }
}
