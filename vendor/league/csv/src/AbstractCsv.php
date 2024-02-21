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

use Generator;
use SplFileObject;
use function filter_var;
use function get_class;
use function mb_strlen;
use function rawurlencode;
use function sprintf;
use function str_replace;
use function str_split;
use function strcspn;
use function strlen;
use const FILTER_FLAG_STRIP_HIGH;
use const FILTER_FLAG_STRIP_LOW;
use const FILTER_UNSAFE_RAW;

/**
 * An abstract class to enable CSV document loading.
 */
abstract class AbstractCsv implements ByteSequence
{
    protected const STREAM_FILTER_MODE = STREAM_FILTER_READ;

    /** @var SplFileObject|Stream The CSV document. */
    protected $document;
    /** @var array<string, bool> collection of stream filters. */
    protected array $stream_filters = [];
    protected ?string $input_bom = null;
    protected string $output_bom = '';
    protected string $delimiter = ',';
    protected string $enclosure = '"';
    protected string $escape = '\\';
    protected bool $is_input_bom_included = false;

    /**
     * @final This method should not be overwritten in child classes
     *
     * @param SplFileObject|Stream $document The CSV Object instance
     */
    protected function __construct($document)
    {
        $this->document = $document;
        [$this->delimiter, $this->enclosure, $this->escape] = $this->document->getCsvControl();
        $this->resetProperties();
    }

    /**
     * Reset dynamic object properties to improve performance.
     */
    abstract protected function resetProperties(): void;

    public function __destruct()
    {
        unset($this->document);
    }

    public function __clone()
    {
        throw UnavailableStream::dueToForbiddenCloning(static::class);
    }

    /**
     * Return a new instance from a SplFileObject.
     *
     * @return static
     */
    public static function createFromFileObject(SplFileObject $file)
    {
        return new static($file);
    }

    /**
     * Return a new instance from a PHP resource stream.
     *
     * @param resource $stream
     *
     * @return static
     */
    public static function createFromStream($stream)
    {
        return new static(new Stream($stream));
    }

    /**
     * Return a new instance from a string.
     *
     * @return static
     */
    public static function createFromString(string $content = '')
    {
        return new static(Stream::createFromString($content));
    }

    /**
     * Return a new instance from a file path.
     *
     * @param resource|null $context the resource context
     *
     * @return static
     */
    public static function createFromPath(string $path, string $open_mode = 'r+', $context = null)
    {
        return new static(Stream::createFromPath($path, $open_mode, $context));
    }

    /**
     * Returns the current field delimiter.
     */
    public function getDelimiter(): string
    {
        return $this->delimiter;
    }

    /**
     * Returns the current field enclosure.
     */
    public function getEnclosure(): string
    {
        return $this->enclosure;
    }

    /**
     * Returns the pathname of the underlying document.
     */
    public function getPathname(): string
    {
        return $this->document->getPathname();
    }

    /**
     * Returns the current field escape character.
     */
    public function getEscape(): string
    {
        return $this->escape;
    }

    /**
     * Returns the BOM sequence in use on Output methods.
     */
    public function getOutputBOM(): string
    {
        return $this->output_bom;
    }

    /**
     * Returns the BOM sequence of the given CSV.
     */
    public function getInputBOM(): string
    {
        if (null !== $this->input_bom) {
            return $this->input_bom;
        }

        $this->document->setFlags(SplFileObject::READ_CSV);
        $this->document->rewind();
        $this->input_bom = Info::fetchBOMSequence((string) $this->document->fread(4)) ?? '';

        return $this->input_bom;
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated since version 9.7.0
     * @see AbstractCsv::supportsStreamFilterOnRead
     * @see AbstractCsv::supportsStreamFilterOnWrite
     *
     * Returns the stream filter mode.
     */
    public function getStreamFilterMode(): int
    {
        return static::STREAM_FILTER_MODE;
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated since version 9.7.0
     * @see AbstractCsv::supportsStreamFilterOnRead
     * @see AbstractCsv::supportsStreamFilterOnWrite
     *
     * Tells whether the stream filter capabilities can be used.
     */
    public function supportsStreamFilter(): bool
    {
        return $this->document instanceof Stream;
    }

    /**
     * Tells whether the stream filter read capabilities can be used.
     */
    public function supportsStreamFilterOnRead(): bool
    {
        return $this->document instanceof Stream
            && (static::STREAM_FILTER_MODE & STREAM_FILTER_READ) === STREAM_FILTER_READ;
    }

    /**
     * Tells whether the stream filter write capabilities can be used.
     */
    public function supportsStreamFilterOnWrite(): bool
    {
        return $this->document instanceof Stream
            && (static::STREAM_FILTER_MODE & STREAM_FILTER_WRITE) === STREAM_FILTER_WRITE;
    }

    /**
     * Tell whether the specify stream filter is attach to the current stream.
     */
    public function hasStreamFilter(string $filtername): bool
    {
        return $this->stream_filters[$filtername] ?? false;
    }

    /**
     * Tells whether the BOM can be stripped if presents.
     */
    public function isInputBOMIncluded(): bool
    {
        return $this->is_input_bom_included;
    }

    /**
     * Returns the CSV document as a Generator of string chunk.
     *
     * @param int $length number of bytes read
     *
     * @throws Exception if the number of bytes is lesser than 1
     */
    public function chunk(int $length): Generator
    {
        if ($length < 1) {
            throw InvalidArgument::dueToInvalidChunkSize($length, __METHOD__);
        }

        $input_bom = $this->getInputBOM();
        $this->document->rewind();
        $this->document->setFlags(0);
        $this->document->fseek(strlen($input_bom));
        /** @var  array<int, string> $chunks */
        $chunks = str_split($this->output_bom.$this->document->fread($length), $length);
        foreach ($chunks as $chunk) {
            yield $chunk;
        }

        while ($this->document->valid()) {
            yield $this->document->fread($length);
        }
    }

    /**
     * DEPRECATION WARNING! This method will be removed in the next major point release.
     *
     * @deprecated since version 9.1.0
     * @see AbstractCsv::toString
     *
     * Retrieves the CSV content
     */
    public function __toString(): string
    {
        return $this->toString();
    }

    /**
     * Retrieves the CSV content.
     *
     * DEPRECATION WARNING! This method will be removed in the next major point release
     *
     * @deprecated since version 9.7.0
     * @see AbstractCsv::toString
     */
    public function getContent(): string
    {
        return $this->toString();
    }

    /**
     * Retrieves the CSV content.
     *
     * @throws Exception If the string representation can not be returned
     */
    public function toString(): string
    {
        $raw = '';
        foreach ($this->chunk(8192) as $chunk) {
            $raw .= $chunk;
        }

        return $raw;
    }

    /**
     * Outputs all data on the CSV file.
     *
     * @return int Returns the number of characters read from the handle
     *             and passed through to the output.
     */
    public function output(string $filename = null): int
    {
        if (null !== $filename) {
            $this->sendHeaders($filename);
        }

        $this->document->rewind();
        if (!$this->is_input_bom_included) {
            $this->document->fseek(strlen($this->getInputBOM()));
        }

        echo $this->output_bom;

        return strlen($this->output_bom) + (int) $this->document->fpassthru();
    }

    /**
     * Send the CSV headers.
     *
     * Adapted from Symfony\Component\HttpFoundation\ResponseHeaderBag::makeDisposition
     *
     * @throws Exception if the submitted header is invalid according to RFC 6266
     *
     * @see https://tools.ietf.org/html/rfc6266#section-4.3
     */
    protected function sendHeaders(string $filename): void
    {
        if (strlen($filename) != strcspn($filename, '\\/')) {
            throw InvalidArgument::dueToInvalidHeaderFilename($filename);
        }

        $flag = FILTER_FLAG_STRIP_LOW;
        if (strlen($filename) !== mb_strlen($filename)) {
            $flag |= FILTER_FLAG_STRIP_HIGH;
        }

        /** @var string $filtered_name */
        $filtered_name = filter_var($filename, FILTER_UNSAFE_RAW, $flag);
        $filename_fallback = str_replace('%', '', $filtered_name);

        $disposition = sprintf('attachment; filename="%s"', str_replace('"', '\\"', $filename_fallback));
        if ($filename !== $filename_fallback) {
            $disposition .= sprintf("; filename*=utf-8''%s", rawurlencode($filename));
        }

        header('Content-Type: text/csv');
        header('Content-Transfer-Encoding: binary');
        header('Content-Description: File Transfer');
        header('Content-Disposition: '.$disposition);
    }

    /**
     * Sets the field delimiter.
     *
     * @throws InvalidArgument If the Csv control character is not one character only.
     *
     * @return static
     */
    public function setDelimiter(string $delimiter): self
    {
        if ($delimiter === $this->delimiter) {
            return $this;
        }

        if (1 !== strlen($delimiter)) {
            throw InvalidArgument::dueToInvalidDelimiterCharacter($delimiter, __METHOD__);
        }

        $this->delimiter = $delimiter;
        $this->resetProperties();

        return $this;
    }

    /**
     * Sets the field enclosure.
     *
     * @throws InvalidArgument If the Csv control character is not one character only.
     *
     * @return static
     */
    public function setEnclosure(string $enclosure): self
    {
        if ($enclosure === $this->enclosure) {
            return $this;
        }

        if (1 !== strlen($enclosure)) {
            throw InvalidArgument::dueToInvalidEnclosureCharacter($enclosure, __METHOD__);
        }

        $this->enclosure = $enclosure;
        $this->resetProperties();

        return $this;
    }

    /**
     * Sets the field escape character.
     *
     * @throws InvalidArgument If the Csv control character is not one character only.
     *
     * @return static
     */
    public function setEscape(string $escape): self
    {
        if ($escape === $this->escape) {
            return $this;
        }

        if ('' !== $escape && 1 !== strlen($escape)) {
            throw InvalidArgument::dueToInvalidEscapeCharacter($escape, __METHOD__);
        }

        $this->escape = $escape;
        $this->resetProperties();

        return $this;
    }

    /**
     * Enables BOM Stripping.
     *
     * @return static
     */
    public function skipInputBOM(): self
    {
        $this->is_input_bom_included = false;

        return $this;
    }

    /**
     * Disables skipping Input BOM.
     *
     * @return static
     */
    public function includeInputBOM(): self
    {
        $this->is_input_bom_included = true;

        return $this;
    }

    /**
     * Sets the BOM sequence to prepend the CSV on output.
     *
     * @return static
     */
    public function setOutputBOM(string $str): self
    {
        $this->output_bom = $str;

        return $this;
    }

    /**
     * append a stream filter.
     *
     * @param null|array $params
     *
     * @throws InvalidArgument    If the stream filter API can not be appended
     * @throws UnavailableFeature If the stream filter API can not be used
     *
     * @return static
     */
    public function addStreamFilter(string $filtername, $params = null): self
    {
        if (!$this->document instanceof Stream) {
            throw UnavailableFeature::dueToUnsupportedStreamFilterApi(get_class($this->document));
        }

        $this->document->appendFilter($filtername, static::STREAM_FILTER_MODE, $params);
        $this->stream_filters[$filtername] = true;
        $this->resetProperties();
        $this->input_bom = null;

        return $this;
    }
}
