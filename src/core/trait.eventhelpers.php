<?php

namespace leantime\core {

    use leantime\core\events;
    use function leantime\core\getLeantimeClassPath;

    trait eventhelpers {

        private static $event_context = '';

        /**
         * dispatches an event with context
         *
         * @access protected
         *
         * @param string $hook
         * @param mixed $available_params
         *
         * @return void
         */
        public static function dispatch_event(string $hook, mixed $available_params = [], string|int $function = null): void
        {
            events::dispatch_event($hook, $available_params, self::get_event_context($function));
        }

        /**
         * dispatches a filter with context
         *
         * @access protected
         *
         * @param string $hook
         * @param mixed $payload
         * @param mixed $available_params
         *
         * @return mixed
         */
        public static function dispatch_filter(string $hook, mixed $payload, mixed $available_params = [], string|int $function = null): mixed
        {
            return events::dispatch_filter($hook, $payload, $available_params, self::get_event_context($function));
        }

        /**
         * Gets the context of the event
         *
         * @access private
         *
         * @return string
         */
        private static function get_event_context($function): string
        {
            if (empty(self::$event_context)) {
                self::$event_context = self::set_class_context();
            }

            $function = !empty($function) && is_string($function) && !is_numeric($function)
                ? $function
                : self::get_function_context(is_numeric($function) ? (int) $function : null);

            return self::$event_context . '.' . $function;
        }

        /**
         * Gets the class context based on path, this uses the same method as the autoloader
         * Helps create unique strings for events/filters
         *
         * @access private
         *
         * @return string
         */
        private static function set_class_context(): string
        {
            $parts = getLeantimeClassPath(get_called_class());
            $parts['path'] = str_replace("/", '.', $parts['path']);

            return implode('.', $parts);
        }

        /**
         * Gets the caller function name
         *
         * This way we don't have to use much memory by using debug_backtrace
         *
         * @access private
         *
         * @return string
         */
        private static function get_function_context($functionInt = null): string
        {
            $tracePointer = is_int($functionInt) ? $functionInt : 3;

            // Create an exception
            $ex = new \Exception();

            // Call getTrace() function
            $trace = $ex->getTrace();

            // Position 0 would be the line
            // that called this function
            $function = $trace[$tracePointer]['function'];

            return $function;
        }

    }
}
