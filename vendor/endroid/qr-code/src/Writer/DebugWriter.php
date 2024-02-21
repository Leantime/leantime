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
use Exception;
use ReflectionClass;

class DebugWriter extends AbstractWriter
{
    public function writeString(QrCodeInterface $qrCode): string
    {
        $data = [];
        $skip = ['getData'];

        $reflectionClass = new ReflectionClass($qrCode);
        foreach ($reflectionClass->getMethods() as $method) {
            $methodName = $method->getShortName();
            if (0 === strpos($methodName, 'get') && 0 == $method->getNumberOfParameters() && !in_array($methodName, $skip)) {
                $value = $qrCode->{$methodName}();
                if (is_array($value) && !is_object(current($value))) {
                    $value = '['.implode(', ', $value).']';
                } elseif (is_bool($value)) {
                    $value = $value ? 'true' : 'false';
                } elseif (is_string($value)) {
                    $value = '"'.$value.'"';
                } elseif (is_null($value)) {
                    $value = 'null';
                }
                try {
                    $data[] = $methodName.': '.$value;
                } catch (Exception $exception) {
                }
            }
        }

        $string = implode(" \n", $data);

        return $string;
    }

    public static function getContentType(): string
    {
        return 'text/plain';
    }

    public function getName(): string
    {
        return 'debug';
    }
}
