<?php

namespace Leantime\Domain\Search\Support;

/**
 * Builds plain-text snippets from stored HTML and wraps matched tokens in <mark>.
 */
final class Highlighter
{
    /**
     * Escape text for HTML and wrap every token occurrence in <mark>.
     *
     * Matching happens on the raw text and escaping on the pieces, so a token can never
     * match inside an HTML entity. Safe to output with {!! !!}.
     *
     * @param  string[]  $tokens
     */
    public static function mark(string $text, array $tokens): string
    {
        $tokens = array_filter($tokens, fn (string $token) => $token !== '');

        if ($text === '' || $tokens === []) {
            return e($text);
        }

        $pattern = '/('.implode('|', array_map(fn (string $token) => preg_quote($token, '/'), $tokens)).')/iu';
        $pieces = preg_split($pattern, $text, -1, PREG_SPLIT_DELIM_CAPTURE);

        if ($pieces === false) {
            return e($text);
        }

        $html = '';
        foreach ($pieces as $index => $piece) {
            $isMatch = $index % 2 === 1;
            $html .= $isMatch ? '<mark>'.e($piece).'</mark>' : e($piece);
        }

        return $html;
    }

    /**
     * Reduce stored HTML to a short plain-text excerpt centred on the first matching token.
     *
     * @param  string[]  $tokens
     */
    public static function snippet(?string $html, array $tokens, int $length = 160): string
    {
        if ($html === null || $html === '') {
            return '';
        }

        $text = html_entity_decode(strip_tags($html), ENT_QUOTES | ENT_HTML5, 'UTF-8');
        $text = trim((string) preg_replace('/\s+/u', ' ', $text));

        if ($text === '') {
            return '';
        }

        $matchPosition = null;
        foreach ($tokens as $token) {
            $position = mb_stripos($text, $token);
            if ($position !== false && ($matchPosition === null || $position < $matchPosition)) {
                $matchPosition = $position;
            }
        }

        $start = $matchPosition === null ? 0 : max(0, $matchPosition - intdiv($length, 3));
        $excerpt = mb_substr($text, $start, $length);

        if ($start > 0) {
            $excerpt = '…'.ltrim($excerpt);
        }
        if ($start + $length < mb_strlen($text)) {
            $excerpt = rtrim($excerpt).'…';
        }

        return $excerpt;
    }
}
