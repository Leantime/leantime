<?php

namespace leantime\core;

use Symfony\Component\ErrorHandler\ErrorHandler as SymfonyErrorHandler;

class CustomErrorHandler extends SymfonyErrorHandler
{
    //private int $thrownErrors = \E_ERROR;
    //private int $loggedErrors = \E_WARNING | \E_NOTICE;
}
