<?php

namespace Leantime\Domain\CsvImport\Exceptions;

/**
 * A file that can't be imported, with a message for the person who uploaded it.
 *
 * The message is already translated and safe to show; other exceptions during upload are
 * logged and replaced with a generic message.
 */
class ImportFileException extends \RuntimeException {}
