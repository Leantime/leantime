<?php

/**
 * League.Csv (https://csv.thephpleague.com)
 *
 * (c) Ignace Nyamagana Butera <nyamsprod@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

declare(strict_types=1);

namespace League\Csv;

final class UnavailableStream extends Exception
{
    private function __construct(string $message)
    {
        parent::__construct($message);
    }

    public static function dueToPathNotFound(string $path): self
    {
        return new self('`'.$path.'`: failed to open stream: No such file or directory.');
    }

    public static function dueToForbiddenCloning(string $class_name): self
    {
        return new self('An object of class '.$class_name.' cannot be cloned.');
    }
}
