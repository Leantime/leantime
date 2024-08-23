<?php

namespace Leantime\Domain\Queue\Workers;
enum Workers: string
{
    case EMAILS = "email";
    case HTTPREQUESTS = "httprequests";

    case DEFAULT = "default";
}
