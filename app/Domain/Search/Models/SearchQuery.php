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
     * InnoDB's default full-text stopword list (INFORMATION_SCHEMA.INNODB_FT_DEFAULT_STOPWORD).
     * These words are never indexed, so requiring them in a boolean query can only fail.
     */
    private const FULLTEXT_STOPWORDS = [
        'a', 'about', 'an', 'are', 'as', 'at', 'be', 'by', 'com', 'de', 'en', 'for', 'from', 'how', 'i',
        'in', 'is', 'it', 'la', 'of', 'on', 'or', 'that', 'the', 'this', 'to', 'was', 'what', 'when',
        'where', 'who', 'will', 'with', 'und', 'www',
    ];

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
     * @param  int[]|null  $accessibleProjectIds  Projects the user may see; null means unrestricted (admin/owner),
     *                                            an empty list means no project-backed results (owner-scoped
     *                                            entities such as private files are still searched).
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
     * Tokens usable in a boolean-mode full-text query, or null when the query must use LIKE.
     *
     * Boolean operator characters are stripped from the token edges. One left inside a word
     * ("foo-bar", "c++x") forces the LIKE path: the index stores "foo" and "bar" separately, so
     * neither "foobar*" nor an operator-laden query would match. A token shorter than the index
     * minimum can never be matched either. Default stopwords are not indexed, but they are
     * simply left out of the query (a user typing "the roadmap" wants "roadmap"); only a term
     * made of stopwords alone falls back to LIKE.
     *
     * @return string[]|null
     */
    public function fullTextTokens(): ?array
    {
        $clean = [];

        foreach ($this->tokens as $token) {
            $stripped = trim($token, '+-<>()~*"@');

            if (preg_match('/[+\-<>()~*"@]/u', $stripped) === 1) {
                return null;
            }

            if (in_array(mb_strtolower($stripped), self::FULLTEXT_STOPWORDS, true)) {
                continue;
            }

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
