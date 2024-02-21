<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\QrCode;

interface QrCodeInterface
{
    public function getText(): string;

    public function getSize(): int;

    public function getMargin(): int;

    /** @return array<int> */
    public function getForegroundColor(): array;

    /** @return array<int> */
    public function getBackgroundColor(): array;

    public function getEncoding(): string;

    public function getRoundBlockSize(): bool;

    public function getErrorCorrectionLevel(): ErrorCorrectionLevel;

    public function getLogoPath(): ?string;

    public function getLogoWidth(): ?int;

    public function getLogoHeight(): ?int;

    public function getLabel(): ?string;

    public function getLabelFontPath(): string;

    public function getLabelFontSize(): int;

    public function getLabelAlignment(): string;

    /** @return array<int> */
    public function getLabelMargin(): array;

    public function getValidateResult(): bool;

    /** @return array<mixed> */
    public function getWriterOptions(): array;

    public function getContentType(): string;

    public function setWriterRegistry(WriterRegistryInterface $writerRegistry): void;

    public function writeString(): string;

    public function writeDataUri(): string;

    public function writeFile(string $path): void;

    /** @return array<mixed> */
    public function getData(): array;
}
