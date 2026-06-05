<?php

namespace Leantime\Domain\ContentTemplates\Support;

/**
 * Resolves translation references inside content-template string fields.
 *
 * Templates carry raw strings on disk. Consumers that want translation
 * call resolve() on each user-facing string. Two conventions are
 * supported, matching the way the hardcoded wiki templates used __():
 *
 *   "t:templates.prd.title"
 *     The whole string is a translation key. Resolved to the locale's
 *     translation.
 *
 *   "{{ t:templates.prd.author }} Gloria Folaron"
 *     Substring substitution. Every occurrence of `{{ t:KEY }}` is
 *     replaced with the locale's translation, leaving surrounding text
 *     intact. Whitespace inside the braces is tolerated.
 *
 * Resolution happens at consumer-call time so the current request's
 * locale is honored — caching the parsed template per-locale would
 * be the alternative but adds complexity without a real win for
 * content templates (cold-path, single resolve per page render).
 *
 * Implemented as a static utility because there's no per-instance
 * state. Callers don't need to inject it.
 */
final class TranslationResolver
{
    /**
     * Resolve translation references in a single string.
     */
    public static function resolve(string $input): string
    {
        if ($input === '') {
            return $input;
        }

        if (str_starts_with($input, 't:')) {
            return (string) __(substr($input, 2));
        }

        return (string) preg_replace_callback(
            '/\{\{\s*t:([\w.-]+)\s*\}\}/',
            static fn (array $m): string => (string) __($m[1]),
            $input
        );
    }

    /**
     * Walk an array, recursively, and resolve translation references in
     * every string value. Non-string values are passed through untouched.
     *
     * @param  array<mixed, mixed>  $data
     * @return array<mixed, mixed>
     */
    public static function resolveArray(array $data): array
    {
        foreach ($data as $key => $value) {
            if (is_string($value)) {
                $data[$key] = self::resolve($value);
            } elseif (is_array($value)) {
                $data[$key] = self::resolveArray($value);
            }
        }

        return $data;
    }
}
