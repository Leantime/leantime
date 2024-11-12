<?php

namespace Leantime\Domain\Api\Contracts;

enum StaticAssetType: string
{
    case AAC = 'audio/aac';
    case ABW = 'application/x-abiword';
    case ARC = 'application/x-freearc';
    case AVI = 'video/x-msvideo';
    case AZW = 'application/vnd.amazon.ebook';
    case BIN = 'application/octet-stream';
    case BMP = 'image/bmp';
    case BZ = 'application/x-bzip';
    case BZ2 = 'application/x-bzip2';
    case CSH = 'application/x-csh';
    case CSS = 'text/css';
    case CSV = 'text/csv';
    case DOC = 'application/msword';
    case DOCX = 'application/vnd.openxmlformats-officedocument.wordprocessingml.document';
    case EOT = 'application/vnd.ms-fontobject';
    case EPUB = 'application/epub+zip';
    case GIF = 'image/gif';
    case GZ = 'application/gzip';
    case HTM = 'HTML';
    case HTML = 'text/html';
    case ICO = 'image/vnd.microsoft.icon';
    case ICS = 'text/calendar';
    case JAR = 'application/java-archive';
    case JPEG = 'JPG';
    case JPG = 'image/jpeg';
    case JS = 'text/javascript';
    case JSON = 'application/json';
    case JSONLD = 'application/ld+json';
    case MD = 'text/markdown';
    case MID = 'MIDI';
    case MIDI = 'audio/midi';
    case MJS = 'JS';
    case MP3 = 'audio/mpeg';
    case MPEG = 'video/mpeg';
    case MPKG = 'application/vnd.apple.installer+xml';
    case ODP = 'application/vnd.oasis.opendocument.presentation';
    case ODS = 'application/vnd.oasis.opendocument.spreadsheet';
    case ODT = 'application/vnd.oasis.opendocument.text';
    case OGA = 'audio/ogg';
    case OGV = 'video/ogg';
    case OGX = 'application/ogg';
    case OPUS = 'audio/opus';
    case OTF = 'font/otf';
    case PDF = 'application/pdf';
    case PNG = 'image/png';
    case PPT = 'application/vnd.ms-powerpoint';
    case PPTX = 'application/vnd.openxmlformats-officedocument.presentationml.presentation';
    case RAR = 'application/vnd.rar';
    case RTF = 'application/rtf';
    case SVG = 'image/svg+xml';
    case TAR = 'application/x-tar';
    case TIF = 'TIFF';
    case TIFF = 'image/tiff';
    case TS = 'video/mp2t';
    case TTF = 'font/ttf';
    case TXT = 'text/plain';
    case VSD = 'application/vnd.visio';
    case WAV = 'audio/wav';
    case WEBA = 'audio/webm';
    case WEBM = 'video/webm';
    case WEBP = 'image/webp';
    case WOFF = 'font/woff';
    case WOFF2 = 'font/woff2';
    case XHTML = 'application/xhtml+xml';
    case XLS = 'application/vnd.ms-excel';
    case XLSX = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
    case XML = 'application/xml';
    case XUL = 'application/vnd.mozilla.xul+xml';
    case YAML = 'YML';
    case YML = 'text/yaml';
    case ZIP = 'application/zip';

    /**
     * Retrieves the MIME type by extension.
     *
     * @param  StaticAssetType  $extension  The file extension to get the MIME type for.
     * @return string The MIME type associated with the given extension.
     */
    public static function getMimeTypeByExtension(StaticAssetType $extension): string
    {
        if (in_array($value = $extension->value, self::getFileExtensions())) {
            $value = constant("self::$value")->value;
        }

        return $value;
    }

    /**
     * Retrieves the file extensions.
     *
     * @return array Array of file extensions.
     */
    public static function getFileExtensions(): array
    {
        return array_map(fn ($case) => $case->name, self::cases());
    }
}
