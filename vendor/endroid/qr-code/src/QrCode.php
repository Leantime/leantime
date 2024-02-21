<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\QrCode;

use BaconQrCode\Encoder\Encoder;
use Endroid\QrCode\Exception\InvalidFontException;
use Endroid\QrCode\Exception\UnsupportedExtensionException;
use Endroid\QrCode\Exception\ValidationException;
use Endroid\QrCode\Writer\WriterInterface;

class QrCode implements QrCodeInterface
{
    const LABEL_FONT_PATH_DEFAULT = __DIR__.'/../assets/fonts/noto_sans.otf';

    const ROUND_BLOCK_SIZE_MODE_MARGIN = 'margin';
    const ROUND_BLOCK_SIZE_MODE_SHRINK = 'shrink';
    const ROUND_BLOCK_SIZE_MODE_ENLARGE = 'enlarge';

    private $text;

    /** @var int */
    private $size = 300;

    /** @var int */
    private $margin = 10;

    /** @var array<int> */
    private $foregroundColor = [
        'r' => 0,
        'g' => 0,
        'b' => 0,
        'a' => 0,
    ];

    /** @var array<int> */
    private $backgroundColor = [
        'r' => 255,
        'g' => 255,
        'b' => 255,
        'a' => 0,
    ];

    /** @var string */
    private $encoding = 'UTF-8';

    /** @var bool */
    private $roundBlockSize = true;

    /** @var string */
    private $roundBlockSizeMode = self::ROUND_BLOCK_SIZE_MODE_MARGIN;

    private $errorCorrectionLevel;

    /** @var string */
    private $logoPath;

    /** @var int|null */
    private $logoWidth;

    /** @var int|null */
    private $logoHeight;

    /** @var string */
    private $label;

    /** @var int */
    private $labelFontSize = 16;

    /** @var string */
    private $labelFontPath = self::LABEL_FONT_PATH_DEFAULT;

    private $labelAlignment;

    /** @var array<string, int> */
    private $labelMargin = [
        't' => 0,
        'r' => 10,
        'b' => 10,
        'l' => 10,
    ];

    /** @var WriterRegistryInterface */
    private $writerRegistry;

    /** @var WriterInterface|null */
    private $writer;

    /** @var array<mixed> */
    private $writerOptions = [];

    /** @var bool */
    private $validateResult = false;

    public function __construct(string $text = '')
    {
        $this->text = $text;

        $this->errorCorrectionLevel = ErrorCorrectionLevel::LOW();
        $this->labelAlignment = LabelAlignment::CENTER();

        $this->createWriterRegistry();
    }

    public function setText(string $text): void
    {
        $this->text = $text;
    }

    public function getText(): string
    {
        return $this->text;
    }

    public function setSize(int $size): void
    {
        $this->size = $size;
    }

    public function getSize(): int
    {
        return $this->size;
    }

    public function setMargin(int $margin): void
    {
        $this->margin = $margin;
    }

    public function getMargin(): int
    {
        return $this->margin;
    }

    /** @param array<int> $foregroundColor */
    public function setForegroundColor(array $foregroundColor): void
    {
        if (!isset($foregroundColor['a'])) {
            $foregroundColor['a'] = 0;
        }

        foreach ($foregroundColor as &$color) {
            $color = intval($color);
        }

        $this->foregroundColor = $foregroundColor;
    }

    public function getForegroundColor(): array
    {
        return $this->foregroundColor;
    }

    /** @param array<int> $backgroundColor */
    public function setBackgroundColor(array $backgroundColor): void
    {
        if (!isset($backgroundColor['a'])) {
            $backgroundColor['a'] = 0;
        }

        foreach ($backgroundColor as &$color) {
            $color = intval($color);
        }

        $this->backgroundColor = $backgroundColor;
    }

    public function getBackgroundColor(): array
    {
        return $this->backgroundColor;
    }

    public function setEncoding(string $encoding): void
    {
        $this->encoding = $encoding;
    }

    public function getEncoding(): string
    {
        return $this->encoding;
    }

    public function setRoundBlockSize(bool $roundBlockSize, string $roundBlockSizeMode = self::ROUND_BLOCK_SIZE_MODE_MARGIN): void
    {
        $this->roundBlockSize = $roundBlockSize;
        $this->setRoundBlockSizeMode($roundBlockSizeMode);
    }

    public function getRoundBlockSize(): bool
    {
        return $this->roundBlockSize;
    }

    public function setRoundBlockSizeMode(string $roundBlockSizeMode): void
    {
        if (!in_array($roundBlockSizeMode, [
            self::ROUND_BLOCK_SIZE_MODE_ENLARGE,
            self::ROUND_BLOCK_SIZE_MODE_MARGIN,
            self::ROUND_BLOCK_SIZE_MODE_SHRINK,
        ])) {
            throw new ValidationException('Invalid round block size mode: '.$roundBlockSizeMode);
        }

        $this->roundBlockSizeMode = $roundBlockSizeMode;
    }

    public function setErrorCorrectionLevel(ErrorCorrectionLevel $errorCorrectionLevel): void
    {
        $this->errorCorrectionLevel = $errorCorrectionLevel;
    }

    public function getErrorCorrectionLevel(): ErrorCorrectionLevel
    {
        return $this->errorCorrectionLevel;
    }

    public function setLogoPath(string $logoPath): void
    {
        $this->logoPath = $logoPath;
    }

    public function getLogoPath(): ?string
    {
        return $this->logoPath;
    }

    public function setLogoSize(int $logoWidth, int $logoHeight = null): void
    {
        $this->logoWidth = $logoWidth;
        $this->logoHeight = $logoHeight;
    }

    public function setLogoWidth(int $logoWidth): void
    {
        $this->logoWidth = $logoWidth;
    }

    public function getLogoWidth(): ?int
    {
        return $this->logoWidth;
    }

    public function setLogoHeight(int $logoHeight): void
    {
        $this->logoHeight = $logoHeight;
    }

    public function getLogoHeight(): ?int
    {
        return $this->logoHeight;
    }

    /** @param array<string, int> $labelMargin */
    public function setLabel(string $label, int $labelFontSize = null, string $labelFontPath = null, string $labelAlignment = null, array $labelMargin = null): void
    {
        $this->label = $label;

        if (null !== $labelFontSize) {
            $this->setLabelFontSize($labelFontSize);
        }

        if (null !== $labelFontPath) {
            $this->setLabelFontPath($labelFontPath);
        }

        if (null !== $labelAlignment) {
            $this->setLabelAlignment($labelAlignment);
        }

        if (null !== $labelMargin) {
            $this->setLabelMargin($labelMargin);
        }
    }

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabelFontSize(int $labelFontSize): void
    {
        $this->labelFontSize = $labelFontSize;
    }

    public function getLabelFontSize(): int
    {
        return $this->labelFontSize;
    }

    public function setLabelFontPath(string $labelFontPath): void
    {
        $resolvedLabelFontPath = (string) realpath($labelFontPath);

        if (!is_file($resolvedLabelFontPath)) {
            throw new InvalidFontException('Invalid label font path: '.$labelFontPath);
        }

        $this->labelFontPath = $resolvedLabelFontPath;
    }

    public function getLabelFontPath(): string
    {
        return $this->labelFontPath;
    }

    public function setLabelAlignment(string $labelAlignment): void
    {
        $this->labelAlignment = new LabelAlignment($labelAlignment);
    }

    public function getLabelAlignment(): string
    {
        return $this->labelAlignment->getValue();
    }

    /** @param array<string, int> $labelMargin */
    public function setLabelMargin(array $labelMargin): void
    {
        $this->labelMargin = array_merge($this->labelMargin, $labelMargin);
    }

    public function getLabelMargin(): array
    {
        return $this->labelMargin;
    }

    public function setWriterRegistry(WriterRegistryInterface $writerRegistry): void
    {
        $this->writerRegistry = $writerRegistry;
    }

    public function setWriter(WriterInterface $writer): void
    {
        $this->writer = $writer;
    }

    public function getWriter(string $name = null): WriterInterface
    {
        if (!is_null($name)) {
            return $this->writerRegistry->getWriter($name);
        }

        if ($this->writer instanceof WriterInterface) {
            return $this->writer;
        }

        return $this->writerRegistry->getDefaultWriter();
    }

    /** @param array<string, mixed> $writerOptions */
    public function setWriterOptions(array $writerOptions): void
    {
        $this->writerOptions = $writerOptions;
    }

    public function getWriterOptions(): array
    {
        return $this->writerOptions;
    }

    private function createWriterRegistry(): void
    {
        $this->writerRegistry = new WriterRegistry();
        $this->writerRegistry->loadDefaultWriters();
    }

    public function setWriterByName(string $name): void
    {
        $this->writer = $this->getWriter($name);
    }

    public function setWriterByPath(string $path): void
    {
        $extension = pathinfo($path, PATHINFO_EXTENSION);

        $this->setWriterByExtension($extension);
    }

    public function setWriterByExtension(string $extension): void
    {
        foreach ($this->writerRegistry->getWriters() as $writer) {
            if ($writer->supportsExtension($extension)) {
                $this->writer = $writer;

                return;
            }
        }

        throw new UnsupportedExtensionException('Missing writer for extension "'.$extension.'"');
    }

    public function writeString(): string
    {
        return $this->getWriter()->writeString($this);
    }

    public function writeDataUri(): string
    {
        return $this->getWriter()->writeDataUri($this);
    }

    public function writeFile(string $path): void
    {
        $this->getWriter()->writeFile($this, $path);
    }

    public function getContentType(): string
    {
        return $this->getWriter()->getContentType();
    }

    public function setValidateResult(bool $validateResult): void
    {
        $this->validateResult = $validateResult;
    }

    public function getValidateResult(): bool
    {
        return $this->validateResult;
    }

    public function getData(): array
    {
        $baconErrorCorrectionLevel = $this->errorCorrectionLevel->toBaconErrorCorrectionLevel();

        $baconQrCode = Encoder::encode($this->text, $baconErrorCorrectionLevel, $this->encoding);

        $baconMatrix = $baconQrCode->getMatrix();

        $matrix = [];
        $columnCount = $baconMatrix->getWidth();
        $rowCount = $baconMatrix->getHeight();
        for ($rowIndex = 0; $rowIndex < $rowCount; ++$rowIndex) {
            $matrix[$rowIndex] = [];
            for ($columnIndex = 0; $columnIndex < $columnCount; ++$columnIndex) {
                $matrix[$rowIndex][$columnIndex] = $baconMatrix->get($columnIndex, $rowIndex);
            }
        }

        $data = ['matrix' => $matrix];
        $data['block_count'] = count($matrix[0]);
        $data['block_size'] = $this->size / $data['block_count'];
        if ($this->roundBlockSize) {
            switch ($this->roundBlockSizeMode) {
                case self::ROUND_BLOCK_SIZE_MODE_ENLARGE:
                    $data['block_size'] = intval(ceil($data['block_size']));
                    $this->size = $data['block_size'] * $data['block_count'];
                    break;
                case self::ROUND_BLOCK_SIZE_MODE_SHRINK:
                    $data['block_size'] = intval(floor($data['block_size']));
                    $this->size = $data['block_size'] * $data['block_count'];
                    break;
                case self::ROUND_BLOCK_SIZE_MODE_MARGIN:
                default:
                    $data['block_size'] = intval(floor($data['block_size']));
            }
        }
        $data['inner_width'] = $data['block_size'] * $data['block_count'];
        $data['inner_height'] = $data['block_size'] * $data['block_count'];
        $data['outer_width'] = $this->size + 2 * $this->margin;
        $data['outer_height'] = $this->size + 2 * $this->margin;
        $data['margin_left'] = ($data['outer_width'] - $data['inner_width']) / 2;
        if ($this->roundBlockSize) {
            $data['margin_left'] = intval(floor($data['margin_left']));
        }
        $data['margin_right'] = $data['outer_width'] - $data['inner_width'] - $data['margin_left'];

        return $data;
    }
}
