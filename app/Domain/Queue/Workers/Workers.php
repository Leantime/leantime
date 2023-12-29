<?php

namespace Leantime\Domain\Queue\Workers;
enum Workers: string
{
    case EMAILS = "emails";
    case HTTPREQUESTS = "httprequests";
}
