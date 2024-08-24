<?php

namespace Leantime\Core\Events {

    use Exception;
    use Illuminate\Contracts\Container\BindingResolutionException;

    /**
     *
     */
    trait DispatchesEvents
    {
        private static string $event_context = '';

        /**
         * dispatches an event with context
         *
         * @access protected
         *
         * @param string          $hook
         * @param mixed           $available_params
         * @param string|int|null $function
         * @return void
         * @throws BindingResolutionException
         */
        public static function dispatch_event(string $hook, mixed $available_params = [], string|int $function = null): void
        {
            EventDispatcher::dispatch_event($hook, $available_params, static::get_event_context($function));
        }

        /**
         * dispatches a filter with context
         *
         * @access protected
         *
         * @param string          $hook
         * @param mixed           $payload
         * @param mixed           $available_params
         * @param string|int|null $function
         * @return mixed
         * @throws BindingResolutionException
         */
        public static function dispatch_filter(string $hook, mixed $payload, mixed $available_params = [], string|int $function = null): mixed
        {
            return EventDispatcher::dispatch_filter($hook, $payload, $available_params, static::get_event_context($function));
        }

        /**
         * Gets the context of the event
         *
         * @access private
         *
         * @param $function
         * @return string
         */
        protected static function get_event_context($function): string
        {
            if (empty(self::$event_context)) {
                self::$event_context = static::set_class_context();
            }

            $function = !empty($function) && is_string($function) && !is_numeric($function)
                ? $function
                : static::get_function_context(is_numeric($function) ? (int) $function : null);

            return self::$event_context . '.' . $function;
        }

        /**
         * Gets the class Context based on path, this uses the same method as the autoloader
         * Helps create unique strings for events/filters
         *
         * @access private
         *
         * @return string
         */
        private static function set_class_context(): string
        {
            return str_replace('\\', '.', strtolower(get_called_class()));
        }

        /**
         * Gets the caller function name
         *
         * This way we don't have to use much memory by using debug_backtrace
         *
         * @access private
         *
         * @param ?int $functionInt
         * @return string
         */
        private static function get_function_context(?int $functionInt = null): string
        {
            $tracePointer = is_int($functionInt) ? $functionInt : 3;

            // Create an exception
            $ex = new Exception();

            // Call getTrace() function
            $trace = $ex->getTrace();

            // Position 0 would be the line
            // that called this function
            $function = $trace[$tracePointer]['function'];

            return $function;
        }
    }
}
