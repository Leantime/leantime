<?php

namespace Leantime\Core\Events;

use Exception;

trait DispatchesEvents
{
    private static string $event_context = '';

    /**
     * dispatches an event with context
     */
    public static function dispatch_event(string $hook, mixed $available_params = [], string|int|null $function = null): void
    {
        EventDispatcher::dispatch_event($hook, $available_params, static::get_event_context($function));
    }

    // The new dispatchEvent method is below. We're keeping both for backwards compatibility until v4.0
    // Temporary for backwards compatibility
    public static function dispatchEvent(string $hook, mixed $available_params = [], string|int|null $function = null): void
    {
        EventDispatcher::dispatch_event($hook, $available_params, static::get_event_context($function));
    }

    /**
     * dispatches a filter with context
     */
    public static function dispatch_filter(string $hook, mixed $payload, mixed $available_params = [], string|int|null $function = null): mixed
    {
        return EventDispatcher::dispatch_filter($hook, $payload, $available_params, static::get_event_context($function));
    }

    // The new dispatchEvent method is below. We're keeping both for backwards compatibility until v4.0
    // Temporary for backwards compatibility
    public static function dispatchFilter(string $hook, mixed $payload, mixed $available_params = [], string|int|null $function = null): mixed
    {
        return EventDispatcher::dispatch_filter($hook, $payload, $available_params, static::get_event_context($function));
    }

    /**
     * Gets the context of the event
     */
    protected static function get_event_context($function): string
    {
        if (empty(self::$event_context)) {
            self::$event_context = static::set_class_context();
        }

        $eventContext = self::$event_context.'.';

        if (! empty($function) && is_string($function) && ! is_numeric($function)) {

            $function = $function;

            // If context starts with leantime, the full context was provided by caller
            if (str_starts_with($function, 'leantime.')) {
                $eventContext = '';
            }

        } else {
            $function = static::get_function_context(is_numeric($function) ? (int) $function : null);
        }

        return $eventContext.$function;
    }

    /**
     * Gets the class Context based on path, this uses the same method as the autoloader
     * Helps create unique strings for events/filters
     */
    private static function set_class_context(): string
    {
        return str_replace('\\', '.', strtolower(static::class));
    }

    /**
     * Gets the caller function name
     *
     * This way we don't have to use much memory by using debug_backtrace
     */
    private static function get_function_context(?int $functionInt = null): string
    {
        $tracePointer = is_int($functionInt) ? $functionInt : 3;

        // Create an exception
        $ex = new Exception;

        // Call getTrace() function
        $trace = $ex->getTrace();

        // Position 0 would be the line
        // that called this function
        return $trace[$tracePointer]['function'];
    }
}
