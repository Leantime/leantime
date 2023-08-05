<?php

namespace leantime\core;

use Illuminate\Container\Container;

/**
 * Application Class - IoC Container for the application
 *
 * @package    leantime
 * @subpackage core
 */
class application extends Container
{
    /**
     * Application bootstrap status
     *
     * @var boolean
     */
    private static bool $bootstrapped = false;

    /**
     * Check if application has been bootstrapped
     *
     * @return boolean
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
