<?php

namespace Leantime\Domain\Search\Models;

/**
 * A normalized search request: the term split into tokens plus the access scope
 * every provider must apply.
 */
final class SearchQuery
{
    public const MIN_TERM_LENGTH = 2;

    public const MAX_TOKENS = 5;

    public const MAX_LIMIT = 50;

    /**
     * InnoDB's default innodb_ft_min_token_size; shorter words are not in the index.
     */
    public const FULLTEXT_MIN_TOKEN_LENGTH = 3;

    /**
     * The whitespace-normalized term as the user typed it.
     */
    public readonly string $term;

    /**
     * Distinct words of the term (max MAX_TOKENS). Every token must match.
     *
     * @var string[]
     */
    public readonly array $tokens;

    public readonly int $limit;

    public readonly int $offset;

    /**
     * @param  int  $userId  The searching user (always the session user).
     * @param  int[]|null  $accessibleProjectIds  Projects the user may see; null means unrestricted (admin/owner).
     * @param  array<string, mixed>  $filters  Optional provider filters (projectId, …).
     */
    public function __construct(
        string $term,
        public readonly int $userId,
        public readonly ?array $accessibleProjectIds,
        int $limit = 20,
        int $offset = 0,
        public readonly array $filters = [],
    ) {
        $this->term = self::normalize($term);
        $this->tokens = self::tokenize($this->term);
        $this->limit = max(1, min(self::MAX_LIMIT, $limit));
        $this->offset = max(0, $offset);
    }

    /**
     * Collapse whitespace and trim.
     */
    public static function normalize(string $term): string
    {
        return trim((string) preg_replace('/\s+/u', ' ', $term));
    }

    /**
     * Split a term into distinct, non-empty words, keeping at most MAX_TOKENS.
     *
     * @return string[]
     */
    public static function tokenize(string $term): array
    {
        $words = array_filter(explode(' ', self::normalize($term)), fn (string $word) => $word !== '');

        return array_slice(array_values(array_unique($words)), 0, self::MAX_TOKENS);
    }

    /**
     * Escape LIKE wildcards so user input matches literally. Backslash is the default
     * escape character on both MySQL and PostgreSQL.
     */
    public static function escapeLike(string $value): string
    {
        return addcslashes($value, '\\%_');
    }

    /**
     * True when the term is long enough to search.
     */
    public function isSearchable(): bool
    {
        return mb_strlen($this->term) >= self::MIN_TERM_LENGTH && $this->tokens !== [];
    }

    /**
     * False when the user is restricted to an empty project list, so providers can
     * return nothing without querying.
     */
    public function hasProjectAccess(): bool
    {
        return $this->accessibleProjectIds === null || $this->accessibleProjectIds !== [];
    }

    /**
     * LIKE pattern matching the token anywhere in a column.
     */
    public function containsPattern(string $token): string
    {
        return '%'.self::escapeLike($token).'%';
    }

    /**
     * LIKE pattern matching a column that starts with the token.
     */
    public function prefixPattern(string $token): string
    {
        return self::escapeLike($token).'%';
    }

    /**
     * Tokens usable in a boolean-mode full-text query, or null when the query must use LIKE:
     * boolean operator characters are stripped, and any token shorter than the index minimum
     * afterwards cannot be matched by the index at all.
     *
     * @return string[]|null
     */
    public function fullTextTokens(): ?array
    {
        $clean = [];

        foreach ($this->tokens as $token) {
            $stripped = (string) preg_replace('/[+\-<>()~*"@]+/u', '', $token);

            if (mb_strlen($stripped) < self::FULLTEXT_MIN_TOKEN_LENGTH) {
                return null;
            }

            $clean[] = $stripped;
        }

        return $clean === [] ? null : $clean;
    }

    /**
     * Boolean-mode search string: every token required, matched as a prefix ("+sprint* +rele*").
     */
    public function booleanModeQuery(): string
    {
        return implode(' ', array_map(fn (string $token) => '+'.$token.'*', $this->fullTextTokens() ?? []));
    }

    /**
     * Read an optional filter value.
     */
    public function filter(string $key, mixed $default = null): mixed
    {
        return $this->filters[$key] ?? $default;
    }
}
