<?php

declare(strict_types=1);

/*
 * (c) Jeroen van den Enden <info@endroid.nl>
 *
 * This source file is subject to the MIT license that is bundled
 * with this source code in the file LICENSE.
 */

namespace Endroid\QrCode\Writer;

use Endroid\QrCode\Exception\ValidationException;
use Endroid\QrCode\QrCodeInterface;

class FpdfWriter extends AbstractWriter
{
    /**
     * Defines as which unit the size is handled. Default is: "mm".
     *
     * Allowed values: 'mm', 'pt', 'cm', 'in'
     */
    public const WRITER_OPTION_MEASURE_UNIT = 'fpdf_measure_unit';

    public function writeString(QrCodeInterface $qrCode): string
    {
        if (!\class_exists(\FPDF::class)) {
            throw new \BadMethodCallException('The Fpdf writer requires FPDF as dependency but the class "\\FPDF" couldn\'t be found.');
        }

        if ($qrCode->getValidateResult()) {
            throw new ValidationException('Built-in validation reader can not check fpdf qr codes: please disable via setValidateResult(false)');
        }
        $foregroundColor = $qrCode->getForegroundColor();
        if (0 !== $foregroundColor['a']) {
            throw new \InvalidArgumentException('The foreground color has an alpha channel, but the fpdf qr writer doesn\'t support alpha channels.');
        }
        $backgroundColor = $qrCode->getBackgroundColor();
        if (0 !== $backgroundColor['a']) {
            throw new \InvalidArgumentException('The foreground color has an alpha channel, but the fpdf qr writer doesn\'t support alpha channels.');
        }

        $label = $qrCode->getLabel();
        $labelHeight = null !== $label ? 30 : 0;

        $data = $qrCode->getData();
        $options = $qrCode->getWriterOptions();

        $fpdf = new \FPDF(
            'P',
            $options[self::WRITER_OPTION_MEASURE_UNIT] ?? 'mm',
            [$data['outer_width'], $data['outer_height'] + $labelHeight]
        );
        $fpdf->AddPage();

        $fpdf->SetFillColor($backgroundColor['r'], $backgroundColor['g'], $backgroundColor['b']);
        $fpdf->Rect(0, 0, $data['outer_width'], $data['outer_height'], 'F');

        $fpdf->SetFillColor($foregroundColor['r'], $foregroundColor['g'], $foregroundColor['b']);
        foreach ($data['matrix'] as $row => $values) {
            foreach ($values as $column => $value) {
                if (1 === $value) {
                    $fpdf->Rect(
                        $data['margin_left'] + ($column * $data['block_size']),
                        $data['margin_left'] + ($row * $data['block_size']),
                        $data['block_size'],
                        $data['block_size'],
                        'F'
                    );
                }
            }
        }

        $logoPath = $qrCode->getLogoPath();
        if (null !== $logoPath) {
            $this->addLogo(
                $fpdf,
                $logoPath,
                $qrCode->getLogoWidth(),
                $qrCode->getLogoHeight(),
                $data['outer_width'],
                $data['outer_height']
            );
        }

        if (null !== $label) {
            $fpdf->setY($data['outer_height'] + 5);
            $fpdf->SetFont('Helvetica', null, $qrCode->getLabelFontSize());
            $fpdf->Cell(0, 0, $label, 0, 0, strtoupper($qrCode->getLabelAlignment()[0]));
        }

        return $fpdf->Output('S');
    }

    protected function addLogo(\FPDF $fpdf, string $logoPath, ?int $logoWidth, ?int $logoHeight, int $imageWidth, int $imageHeight): void
    {
        if (null === $logoHeight || null === $logoWidth) {
            [$logoSourceWidth, $logoSourceHeight] = \getimagesize($logoPath);

            if (null === $logoWidth) {
                $logoWidth = (int) $logoSourceWidth;
            }

            if (null === $logoHeight) {
                $aspectRatio = $logoWidth / $logoSourceWidth;
                $logoHeight = (int) ($logoSourceHeight * $aspectRatio);
            }
        }

        $logoX = $imageWidth / 2 - (int) $logoWidth / 2;
        $logoY = $imageHeight / 2 - (int) $logoHeight / 2;

        $fpdf->Image($logoPath, $logoX, $logoY, $logoWidth, $logoHeight);
    }

    public static function getContentType(): string
    {
        return 'application/pdf';
    }

    public static function getSupportedExtensions(): array
    {
        return ['pdf'];
    }

    public function getName(): string
    {
        return 'fpdf';
    }
}
