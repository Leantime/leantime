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
         * @param mixed $payload
         *
         * @return void
         */
        protected static function dispatch_event($hook, $payload = [])
        {
            events::dispatch_event($hook, $payload, self::get_event_context());
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
        protected static function dispatch_filter($hook, $payload, $available_params = [])
        {
            return events::dispatch_filter($hook, $payload, $available_params, self::get_event_context());
        }

        /**
         * Gets the context of the event
         *
         * @access private
         *
         * @return string
         */
        private static function get_event_context()
        {
            if (empty(self::$event_context)) {
                self::$event_context = self::set_class_context();
            }

            return self::$event_context . '.' . self::get_function_context();
        }

        /**
         * Gets the class context based on path, this uses the same method as the autoloader
         * Helps create unique strings for events/filters
         *
         * @access private
         *
         * @return string
         */
        private static function set_class_context()
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
        private static function get_function_context()
        {
            // Create an exception
            $ex = new \Exception();

            // Call getTrace() function
            $trace = $ex->getTrace();

            // Position 0 would be the line
            // that called this function
            $function = $trace[3]['function'];

            return $function;
        }

    }
}
