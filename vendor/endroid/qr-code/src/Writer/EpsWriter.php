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

class EpsWriter extends AbstractWriter
{
    public function writeString(QrCodeInterface $qrCode): string
    {
        $data = $qrCode->getData();

        $epsData = [];
        $epsData[] = '%!PS-Adobe-3.0 EPSF-3.0';
        $epsData[] = '%%BoundingBox: 0 0 '.$data['outer_width'].' '.$data['outer_height'];
        $epsData[] = '/F { rectfill } def';
        $epsData[] = number_format($qrCode->getBackgroundColor()['r'] / 100, 2, '.', ',').' '.number_format($qrCode->getBackgroundColor()['g'] / 100, 2, '.', ',').' '.number_format($qrCode->getBackgroundColor()['b'] / 100, 2, '.', ',').' setrgbcolor';
        $epsData[] = '0 0 '.$data['outer_width'].' '.$data['outer_height'].' F';
        $epsData[] = number_format($qrCode->getForegroundColor()['r'] / 100, 2, '.', ',').' '.number_format($qrCode->getForegroundColor()['g'] / 100, 2, '.', ',').' '.number_format($qrCode->getForegroundColor()['b'] / 100, 2, '.', ',').' setrgbcolor';

        // Please note an EPS has a reversed Y axis compared to PNG and SVG
        $data['matrix'] = array_reverse($data['matrix']);
        foreach ($data['matrix'] as $row => $values) {
            foreach ($values as $column => $value) {
                if (1 === $value) {
                    $x = $data['margin_left'] + $data['block_size'] * $column;
                    $y = $data['margin_left'] + $data['block_size'] * $row;
                    $epsData[] = $x.' '.$y.' '.$data['block_size'].' '.$data['block_size'].' F';
                }
            }
        }

        return implode("\n", $epsData);
    }

    public static function getContentType(): string
    {
        return 'image/eps';
    }

    public static function getSupportedExtensions(): array
    {
        return ['eps'];
    }

    public function getName(): string
    {
        return 'eps';
    }
}
