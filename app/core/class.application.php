<?php

namespace leantime\core;

use Illuminate\Container\Container;

class application extends Container
{
    /**
     * Application bootstrap status
     *
     * @var bool
     * @static
     * @access private
     */
    private static bool $bootstrapped = false;

    /**
     * Check if application has been bootstrapped
     *
     * @return bool
     */
    public static function hasBeenBootstrapped(): bool
    {
        return self::$bootstrapped;
    }

    /**
     * Set the application as having been bootstrapped
     *
     * @return void
     */
    public static function setHasBeenBootstrapped(): void
    {
        self::$bootstrapped = true;
    }
}
