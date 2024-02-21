<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\QrCode;

use Endroid\QrCode\Writer\WriterInterface;

interface WriterRegistryInterface
{
    /** @param WriterInterface[] $writers */
    public function addWriters(iterable $writers): void;

    public function addWriter(WriterInterface $writer): void;

    public function getWriter(string $name): WriterInterface;

    public function getDefaultWriter(): WriterInterface;

    /** @return WriterInterface[] */
    public function getWriters(): array;
}
