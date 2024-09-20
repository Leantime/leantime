<?php

namespace Leantime\Domain\Connector\Models;

final class FieldTypes
{
    public static string $int = 'int';
    public static string $shortString = 'varchar(255)';

    public static string $array = 'array';

    public static string $text = 'text';

    public static string $email = 'email';

    public static string $dateTime = 'dateTime';

    public function __construct()
    {
    }
}
