<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Csv;

use InvalidArgumentException;
use function array_fill_keys;
use function array_keys;
use function array_map;
use function array_merge;
use function array_unique;
use function is_object;
use function is_string;
use function method_exists;

/**
 * A Formatter to tackle CSV Formula Injection.
 *
 * @see http://georgemauer.net/2017/10/07/csv-injection.html
 */
class EscapeFormula
{
    /** Spreadsheet formula starting character. */
    const FORMULA_STARTING_CHARS = ['=', '-', '+', '@', "\t", "\r"];

    /** Effective Spreadsheet formula starting characters. */
    protected array $special_chars = [];
    /** Escape character to escape each CSV formula field. */
    protected string $escape;

    /**
     * @param string   $escape        escape character to escape each CSV formula field
     * @param string[] $special_chars additional spreadsheet formula starting characters
     */
    public function __construct(string $escape = "'", array $special_chars = [])
    {
        $this->escape = $escape;
        if ([] !== $special_chars) {
            $special_chars = $this->filterSpecialCharacters(...$special_chars);
        }

        $chars = array_unique(array_merge(self::FORMULA_STARTING_CHARS, $special_chars));
        $this->special_chars = array_fill_keys($chars, 1);
    }

    /**
     * Filter submitted special characters.
     *
     * @param string ...$characters
     *
     * @throws InvalidArgumentException if the string is not a single character
     *
     * @return array<string>
     */
    protected function filterSpecialCharacters(string ...$characters): array
    {
        foreach ($characters as $str) {
            if (1 != strlen($str)) {
                throw new InvalidArgumentException('The submitted string '.$str.' must be a single character');
            }
        }

        return $characters;
    }

    /**
     * Returns the list of character the instance will escape.
     *
     * @return array<string>
     */
    public function getSpecialCharacters(): array
    {
        return array_keys($this->special_chars);
    }

    /**
     * Returns the escape character.
     */
    public function getEscape(): string
    {
        return $this->escape;
    }

    /**
     * League CSV formatter hook.
     *
     * @see escapeRecord
     */
    public function __invoke(array $record): array
    {
        return $this->escapeRecord($record);
    }

    /**
     * Escape a CSV record.
     */
    public function escapeRecord(array $record): array
    {
        return array_map([$this, 'escapeField'], $record);
    }

    /**
     * Escape a CSV cell if its content is stringable.
     *
     * @param int|float|string|object|resource|array $cell the content of the cell
     *
     * @return mixed the escaped content
     */
    protected function escapeField($cell)
    {
        if (!is_string($cell) && (!is_object($cell) || !method_exists($cell, '__toString'))) {
            return $cell;
        }

        $str_cell = (string) $cell;
        if (isset($str_cell[0], $this->special_chars[$str_cell[0]])) {
            return $this->escape.$str_cell;
        }

        return $cell;
    }

    /**
     * @deprecated since 9.7.2 will be removed in the next major release
     * @codeCoverageIgnore
     *
     * Tells whether the submitted value is stringable.
     *
     * @param mixed $value value to check if it is stringable
     */
    protected function isStringable($value): bool
    {
        return is_string($value)
            || (is_object($value) && method_exists($value, '__toString'));
    }
}
