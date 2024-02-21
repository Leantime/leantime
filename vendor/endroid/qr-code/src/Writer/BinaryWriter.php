<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\QrCode\Writer;

use Endroid\QrCode\QrCodeInterface;

class BinaryWriter extends AbstractWriter
{
    public function writeString(QrCodeInterface $qrCode): string
    {
        $rows = [];
        $data = $qrCode->getData();
        foreach ($data['matrix'] as $row) {
            $values = '';
            foreach ($row as $value) {
                $values .= $value;
            }
            $rows[] = $values;
        }

        return implode("\n", $rows);
    }

    public static function getContentType(): string
    {
        return 'text/plain';
    }

    public static function getSupportedExtensions(): array
    {
        return ['bin', 'txt'];
    }

    public function getName(): string
    {
        return 'binary';
    }
}
