<?php

namespace Unit\app\Domain\CsvImport\Services;

use Leantime\Core\Language;
use Leantime\Domain\CsvImport\Exceptions\ImportFileException;
use Leantime\Domain\CsvImport\Models\ImportTable;
use Leantime\Domain\CsvImport\Services\FileReader;
use Leantime\Domain\CsvImport\Services\TableBuilder;
use Unit\TestCase;

/**
 * The Read step: files as people export them become the same table.
 */
class FileReaderTest extends TestCase
{
    use \Codeception\Test\Feature\Stub;

    /** @var array<int, string> Files written by the test. */
    private array $files = [];

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            @unlink($file);
        }

        parent::tearDown();
    }

    /**
     * A Language stub that returns "key|%s", so messages can be asserted by key.
     */
    private function reader(int $maxCells = FileReader::MAX_CELLS, int $maxUnpackedBytes = FileReader::MAX_UNPACKED_BYTES): FileReader
    {
        $language = $this->make(Language::class, [
            '__' => fn (string $index, string $default = '') => $index.'|%s',
        ]);

        return new FileReader(new TableBuilder($language), $language, $maxCells, $maxUnpackedBytes);
    }

    private function file(string $bytes, string $extension = 'csv'): string
    {
        $path = sys_get_temp_dir().'/lt-import-'.uniqid().'.'.$extension;
        file_put_contents($path, $bytes);
        $this->files[] = $path;

        return $path;
    }

    /**
     * Builds an .xlsx workbook: shared strings, a date style (numFmt 14) and optional sheets.
     *
     * @param  array<string, array<int, array<int, mixed>>>  $sheets  Sheet name => rows of cells; a
     *                                                                ['date' => 46361.0] cell is a date.
     * @param  array<int, string>  $hidden  Names of hidden sheets.
     */
    private function xlsx(array $sheets, array $hidden = [], string $extension = 'xlsx'): string
    {
        $strings = [];
        $sheetXml = [];
        $index = 0;
        foreach ($sheets as $rows) {
            $index++;
            $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><dimension ref="A1:XFD1048576"/><sheetData>';
            foreach ($rows as $r => $cells) {
                $xml .= '<row r="'.($r + 1).'">';
                foreach ($cells as $c => $value) {
                    $ref = TableBuilder::columnLetter($c).($r + 1);
                    if (is_array($value)) {
                        $xml .= '<c r="'.$ref.'" s="1"><v>'.$value['date'].'</v></c>';
                    } elseif (is_bool($value)) {
                        $xml .= '<c r="'.$ref.'" t="b"><v>'.($value ? 1 : 0).'</v></c>';
                    } elseif (is_int($value) || is_float($value)) {
                        $xml .= '<c r="'.$ref.'"><v>'.$value.'</v></c>';
                    } else {
                        $strings[] = (string) $value;
                        $xml .= '<c r="'.$ref.'" t="s"><v>'.(count($strings) - 1).'</v></c>';
                    }
                }
                $xml .= '</row>';
            }
            $sheetXml[$index] = $xml.'</sheetData></worksheet>';
        }

        $workbookSheets = '';
        $rels = '';
        $types = '';
        $i = 0;
        foreach (array_keys($sheets) as $name) {
            $i++;
            $state = in_array($name, $hidden, true) ? ' state="hidden"' : '';
            $workbookSheets .= '<sheet name="'.$name.'" sheetId="'.$i.'"'.$state.' r:id="rId'.$i.'"/>';
            $rels .= '<Relationship Id="rId'.$i.'" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet'.$i.'.xml"/>';
            $types .= '<Override PartName="/xl/worksheets/sheet'.$i.'.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        $rels .= '<Relationship Id="rIdS" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings" Target="sharedStrings.xml"/>';
        $rels .= '<Relationship Id="rIdY" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>';

        $path = sys_get_temp_dir().'/lt-import-'.uniqid().'.'.$extension;
        $zip = new \ZipArchive;
        $zip->open($path, \ZipArchive::CREATE);
        $zip->addFromString('[Content_Types].xml', '<?xml version="1.0" encoding="UTF-8"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'.$types.'<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/><Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/></Types>');
        $zip->addFromString('_rels/.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>');
        $zip->addFromString('xl/workbook.xml', '<?xml version="1.0" encoding="UTF-8"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets>'.$workbookSheets.'</sheets></workbook>');
        $zip->addFromString('xl/_rels/workbook.xml.rels', '<?xml version="1.0" encoding="UTF-8"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'.$rels.'</Relationships>');
        $zip->addFromString('xl/styles.xml', '<?xml version="1.0" encoding="UTF-8"?><styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"><cellXfs count="2"><xf numFmtId="0"/><xf numFmtId="14" applyNumberFormat="1"/></cellXfs></styleSheet>');
        $shared = '';
        foreach ($strings as $string) {
            $shared .= '<si><t xml:space="preserve">'.htmlspecialchars($string, ENT_XML1).'</t></si>';
        }
        $zip->addFromString('xl/sharedStrings.xml', '<?xml version="1.0" encoding="UTF-8"?><sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" count="'.count($strings).'" uniqueCount="'.count($strings).'">'.$shared.'</sst>');
        foreach ($sheetXml as $i => $xml) {
            $zip->addFromString('xl/worksheets/sheet'.$i.'.xml', $xml);
        }
        $zip->close();
        $this->files[] = $path;

        return $path;
    }

    /**
     * Asserts that reading fails with the message key.
     */
    private function assertRefused(string $path, string $messageKey, ?FileReader $reader = null): void
    {
        try {
            ($reader ?? $this->reader())->read($path);
            $this->fail('expected '.$messageKey);
        } catch (ImportFileException $e) {
            $this->assertStringStartsWith($messageKey, $e->getMessage());
        }
    }

    public function test_jira_repeated_columns_get_numbered_headings(): void
    {
        $table = $this->reader()->read($this->file("Summary,Issue key,Labels,Labels,Labels\nFix login,WEB-1,auth,urgent,\nNew logo,WEB-2,design,,\n"));

        $this->assertSame(['Summary', 'Issue key', 'Labels', 'Labels (2)', 'Labels (3)'], $table->headers);
        $this->assertSame('urgent', $table->rows[0]['Labels (2)']);
        $this->assertSame(',', $table->delimiter);
        $this->assertSame('2', $table->rows[0][ImportTable::ROW_KEY]);
    }

    public function test_semicolons_tabs_and_quoted_separators(): void
    {
        $semicolons = $this->reader()->read($this->file("Titel;Projekt;Stunden\n\"Angebot, neu\";Website;2,5\nRechnung;Website;1\n"));
        $this->assertSame(';', $semicolons->delimiter);
        $this->assertSame('Angebot, neu', $semicolons->rows[0]['Titel'], 'a comma inside quotes is not a separator');
        $this->assertSame('2,5', $semicolons->rows[0]['Stunden']);

        $tabs = $this->reader()->read($this->file("Title\tStatus\nOne\tDone\n", 'tsv'));
        $this->assertSame("\t", $tabs->delimiter);
        $this->assertSame('Done', $tabs->rows[0]['Status']);
    }

    public function test_encodings_are_converted_to_utf8(): void
    {
        $windows = $this->reader()->read($this->file("Title,Owner\nCaf\xE9 menu,Ren\xE9e\n"));
        $this->assertSame('Windows-1252', $windows->encoding);
        $this->assertSame('Café menu', $windows->rows[0]['Title']);

        $utf16 = $this->reader()->read($this->file("\xFF\xFE".mb_convert_encoding("Title\tOwner\nNaïve plan\tZoë\n", 'UTF-16LE', 'UTF-8'), 'txt'));
        $this->assertSame('UTF-16LE', $utf16->encoding);
        $this->assertSame(['Title', 'Owner'], $utf16->headers);
        $this->assertSame('Zoë', $utf16->rows[0]['Owner']);

        $bom = $this->reader()->read($this->file("\xEF\xBB\xBFTitle,Owner\nOne,Ada\n"));
        $this->assertSame('Title', $bom->headers[0], 'the byte order mark is not part of the first heading');
    }

    public function test_rows_above_the_table_are_skipped_and_row_numbers_match_the_sheet(): void
    {
        $csv = "Plan name,Website relaunch\nExported,2026-11-23\n\nTask Name,Bucket Name,Progress,Assigned To\nHero image,Design,Not started,Ada\n,,,\nFooter,Build,In progress,Ben\n";
        $table = $this->reader()->read($this->file($csv));

        $this->assertSame(4, $table->headerRow);
        $this->assertSame(['Task Name', 'Bucket Name', 'Progress', 'Assigned To'], $table->headers);
        $this->assertSame(2, $table->rowCount(), 'the empty row is skipped');
        $this->assertSame(['5', '7'], array_column($table->rows, ImportTable::ROW_KEY));

        $override = $this->reader()->read($this->file($csv), null, 1);
        $this->assertSame(1, $override->headerRow, 'the person can pick another heading row');
        $this->assertSame('Plan name', $override->headers[0]);
    }

    public function test_unnamed_columns_and_invisible_characters(): void
    {
        $table = $this->reader()->read($this->file("Title,\u{00A0}Owner\u{200B},,\nOne,Ada,,note\n"));

        $this->assertSame(['Title', 'Owner', 'label.import_column_letter|C', 'label.import_column_letter|D'], $table->headers);
        $this->assertSame('note', $table->rows[0]['label.import_column_letter|D']);
    }

    public function test_a_pipe_in_a_heading_does_not_break_mapping(): void
    {
        $table = $this->reader()->read($this->file("Title;Size | Effort\nOne;M\n"));

        $this->assertSame('Size / Effort', $table->headers[1]);
    }

    public function test_excel_workbooks_with_dates_hidden_sheets_and_rows_above_the_table(): void
    {
        $path = $this->xlsx([
            'Notes' => [],
            'Tasks' => [
                ['Plan name', 'Website relaunch'],
                ['Date of export', ['date' => 46349.0]],
                [],
                ['Task Name', 'Due date', 'Start', 'Effort', 'Done'],
                ['Hero image', ['date' => 46361.0], ['date' => 46351.395833333], 3, false],
                ['Footer', '', '', 2.5, true],
            ],
            'Archive' => [['Old', 'Stuff'], ['a', 'b']],
        ], ['Archive']);

        $table = $this->reader()->read($path);

        $this->assertSame('xlsx', $table->format);
        $this->assertSame([0 => 'Notes', 1 => 'Tasks'], $table->sheetNames, 'hidden sheets are not offered');
        $this->assertSame('Tasks', $table->sheetName(), 'the first sheet with data is read');
        $this->assertSame(4, $table->headerRow);
        $this->assertSame('2026-12-05', $table->rows[0]['Due date'], 'a date cell without a time is a date');
        $this->assertSame('2026-11-25 09:30:00', $table->rows[0]['Start']);
        $this->assertSame('3', $table->rows[0]['Effort']);
        $this->assertSame('2.5', $table->rows[1]['Effort']);
        $this->assertSame('FALSE', $table->rows[0]['Done']);
        $this->assertSame(['5', '6'], array_column($table->rows, ImportTable::ROW_KEY));
    }

    public function test_the_person_can_pick_another_sheet(): void
    {
        $path = $this->xlsx(['First' => [['A', 'B'], ['1x', '2x']], 'Second' => [['C', 'D'], ['3x', '4x']]]);

        $table = $this->reader()->read($path, 1);

        $this->assertSame(['C', 'D'], $table->headers);
        $this->assertSame(1, $table->sheetIndex);
    }

    public function test_the_format_comes_from_the_content_not_the_name(): void
    {
        $renamed = $this->xlsx(['Sheet1' => [['Title'], ['From Excel']]], [], 'csv');
        $this->assertSame('xlsx', $this->reader()->read($renamed)->format);

        $this->assertRefused($this->file("\xD0\xCF\x11\xE0\xA1\xB1\x1A\xE1old excel", 'xls'), 'notification.import_file_xls');

        $zip = sys_get_temp_dir().'/lt-import-'.uniqid().'.xlsx';
        $archive = new \ZipArchive;
        $archive->open($zip, \ZipArchive::CREATE);
        $archive->addFromString('word/document.xml', '<document/>');
        $archive->close();
        $this->files[] = $zip;
        $this->assertRefused($zip, 'notification.import_file_not_workbook');
    }

    public function test_files_over_the_limits_are_refused_before_they_are_read(): void
    {
        $this->assertRefused($this->file("Title,Owner\nOne,Ada\nTwo,Ben\n"), 'notification.import_file_too_large', $this->reader(maxCells: 4));

        $workbook = $this->xlsx(['Sheet1' => [['Title'], [str_repeat('x', 5000)]]]);
        $this->assertRefused($workbook, 'notification.import_file_too_large', $this->reader(maxUnpackedBytes: 2000));

        $cells = $this->xlsx(['Sheet1' => [['Title', 'Owner'], ['One', 'Ada'], ['Two', 'Ben']]]);
        $this->assertRefused($cells, 'notification.import_file_too_large', $this->reader(maxCells: 4));
    }

    public function test_empty_files_and_files_without_rows(): void
    {
        $this->assertRefused($this->file(" \n\n"), 'notification.import_file_empty');
        $this->assertRefused($this->file("Title,Owner\n"), 'notification.import_file_no_rows');
    }

    public function test_column_letters(): void
    {
        $this->assertSame(['A', 'Z', 'AA', 'AZ', 'ZZ', 'AAA'], array_map([TableBuilder::class, 'columnLetter'], [0, 25, 26, 51, 701, 702]));
    }
}
