<?php

namespace leantime\domain\models\connector {

    final class  fieldTypes
    {
        public static $int = "int";
        public static $shortString = "varchar(255)";

        public static $array = "array";

        public static $text = "text";

        public static $email = "email";

        public static $dateTime = "dateTime";

        public function __construct()
        {
        }
    }

}
