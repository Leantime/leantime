<?php

namespace Leantime\Core\Files\Exceptions;

use Exception;

/**
 * Exception thrown when file validation fails
 */
class FileValidationException extends Exception
{
    // Error codes
    public const INVALID_FILE = 1001;

    public const INVALID_MIME_TYPE = 1002;

    public const FILE_TOO_LARGE = 1003;

    public const MALICIOUS_CONTENT = 1004;

    public const DIMENSIONS_TOO_LARGE = 1005;

    public const VALIDATION_ERROR = 1099;

    /**
     * @var string The user-friendly error message
     */
    protected $userMessage;

    /**
     * Constructor
     *
     * @param  string  $message  The error message
     * @param  int  $code  The error code
     * @param  Exception|null  $previous  The previous exception
     * @param  string|null  $userMessage  A user-friendly error message
     */
    public function __construct(string $message, int $code = 0, ?Exception $previous = null, ?string $userMessage = null)
    {
        parent::__construct($message, $code, $previous);
        $this->userMessage = $userMessage ?? $this->getUserMessageFromCode($code);
    }

    /**
     * Get a user-friendly message based on the error code
     *
     * @param  int  $code  The error code
     * @return string The user-friendly message
     */
    protected function getUserMessageFromCode(int $code): string
    {
        return match ($code) {
            self::INVALID_FILE => 'The file is invalid or corrupted. Please try uploading a different file.',
            self::INVALID_MIME_TYPE => 'This file type is not allowed. Please upload a supported file type.',
            self::FILE_TOO_LARGE => 'The file is too large. Please upload a smaller file.',
            self::MALICIOUS_CONTENT => 'The file contains potentially malicious content and cannot be uploaded.',
            self::DIMENSIONS_TOO_LARGE => 'The image dimensions are too large. Please resize the image and try again.',
            default => 'An error occurred while validating the file. Please try again with a different file.'
        };
    }

    /**
     * Get the user-friendly error message
     *
     * @return string The user-friendly error message
     */
    public function getUserMessage(): string
    {
        return $this->userMessage;
    }
}
