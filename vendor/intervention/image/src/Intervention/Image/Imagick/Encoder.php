<?php

namespace Intervention\Image\Imagick;

use Intervention\Image\AbstractEncoder;
use Intervention\Image\Exception\NotSupportedException;

class Encoder extends AbstractEncoder
{
    /**
     * Processes and returns encoded image as JPEG string
     *
     * @return string
     */
    protected function processJpeg()
    {
        $format = 'jpeg';
        $compression = \Imagick::COMPRESSION_JPEG;

        $imagick = $this->image->getCore();
        $imagick->setImageBackgroundColor('white');
        $imagick->setBackgroundColor('white');
        $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_MERGE);
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);
        $imagick->setCompressionQuality($this->quality);
        $imagick->setImageCompressionQuality($this->quality);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as PNG string
     *
     * @return string
     */
    protected function processPng()
    {
        $format = 'png';
        $compression = \Imagick::COMPRESSION_ZIP;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        $this->image->mime = image_type_to_mime_type(IMAGETYPE_PNG);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as GIF string
     *
     * @return string
     */
    protected function processGif()
    {
        $format = 'gif';
        $compression = \Imagick::COMPRESSION_LZW;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        $this->image->mime = image_type_to_mime_type(IMAGETYPE_GIF);

        return $imagick->getImagesBlob();
    }

    protected function processWebp()
    {
        if ( ! \Imagick::queryFormats('WEBP')) {
            throw new NotSupportedException(
                "Webp format is not supported by Imagick installation."
            );
        }

        $format = 'webp';
        $compression = \Imagick::COMPRESSION_JPEG;

        $imagick = $this->image->getCore();
        $imagick->setImageBackgroundColor(new \ImagickPixel('transparent'));

        $imagick = $imagick->mergeImageLayers(\Imagick::LAYERMETHOD_MERGE);
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);
        $imagick->setImageCompressionQuality($this->quality);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as TIFF string
     *
     * @return string
     */
    protected function processTiff()
    {
        $format = 'tiff';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);
        $imagick->setCompressionQuality($this->quality);
        $imagick->setImageCompressionQuality($this->quality);

        $this->image->mime = image_type_to_mime_type(IMAGETYPE_TIFF_II);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as BMP string
     *
     * @return string
     */
    protected function processBmp()
    {
        $format = 'bmp';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        $this->image->mime = image_type_to_mime_type(IMAGETYPE_BMP);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as ICO string
     *
     * @return string
     */
    protected function processIco()
    {
        $format = 'ico';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        $this->image->mime = image_type_to_mime_type(IMAGETYPE_ICO);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as PSD string
     *
     * @return string
     */
    protected function processPsd()
    {
        $format = 'psd';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);

        $this->image->mime = image_type_to_mime_type(IMAGETYPE_PSD);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as AVIF string
     *
     * @return string
     */
    protected function processAvif()
    {
        if ( ! \Imagick::queryFormats('AVIF')) {
            throw new NotSupportedException(
                "AVIF format is not supported by Imagick installation."
            );
        }

        $format = 'avif';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);
        $imagick->setCompressionQuality($this->quality);
        $imagick->setImageCompressionQuality($this->quality);

        return $imagick->getImagesBlob();
    }

    /**
     * Processes and returns encoded image as HEIC string
     *
     * @return string
     */
    protected function processHeic()
    {
        if ( ! \Imagick::queryFormats('HEIC')) {
            throw new NotSupportedException(
                "HEIC format is not supported by Imagick installation."
            );
        }

        $format = 'heic';
        $compression = \Imagick::COMPRESSION_UNDEFINED;

        $imagick = $this->image->getCore();
        $imagick->setFormat($format);
        $imagick->setImageFormat($format);
        $imagick->setCompression($compression);
        $imagick->setImageCompression($compression);
        $imagick->setCompressionQuality($this->quality);
        $imagick->setImageCompressionQuality($this->quality);

        return $imagick->getImagesBlob();
    }
}
