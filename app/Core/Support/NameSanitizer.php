<?php

namespace Leantime\Core\Support;

/**
 * Sanitizes person/display names before they are stored or rendered into
 * outgoing emails (invite mails, mention notifications, From display names).
 *
 * Names were previously stored and emailed raw, which let spammers use the
 * firstname field as an email payload (URLs, contact numbers, bidi tricks).
 * Legitimate names in any script (CJK, Arabic, Cyrillic, ...) must pass —
 * this strips abuse vectors, it does not enforce a charset.
 */
class NameSanitizer
{
    /**
     * Maximum length of a sanitized name.
     */
    private const MAX_LENGTH = 50;

    /**
     * Clean a person name for storage and email use.
     *
     * Removes HTML, control/format characters (including bidi overrides and
     * zero-width characters), URLs, email addresses, and long digit runs
     * (contact-number spam), then collapses whitespace and caps the length.
     *
     * @param  mixed  $name  The raw name value
     * @return string The sanitized name (may be an empty string)
     */
    public static function clean(mixed $name): string
    {
        if (! is_string($name)) {
            return '';
        }

        $name = strip_tags($name);

        // Control + format characters: bidi overrides, zero-width chars, etc.
        $name = preg_replace('/\p{C}+/u', '', $name) ?? '';

        // URLs and bare domains used as spam payloads
        $name = preg_replace('~(?:https?|ftp)://\S+~iu', '', $name) ?? '';
        $name = preg_replace('~www\.\S+~iu', '', $name) ?? '';

        // Email addresses embedded in names
        $name = preg_replace('/\S+@\S+\.\S+/u', '', $name) ?? '';

        // Long digit runs (QQ/WeChat/phone contact spam); real names don't have them
        $name = preg_replace('/\d{5,}/u', '', $name) ?? '';

        $name = preg_replace('/\s+/u', ' ', $name) ?? '';
        $name = trim($name);

        // Explicit UTF-8 so the length cap is deterministic regardless of the
        // PHP internal-encoding setting (multi-script names count by codepoint).
        return mb_substr($name, 0, self::MAX_LENGTH, 'UTF-8');
    }
}
