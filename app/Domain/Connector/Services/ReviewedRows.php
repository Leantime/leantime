<?php

namespace Leantime\Domain\Connector\Services;

use Illuminate\Support\Facades\Log;
use Ramsey\Uuid\Uuid;

/**
 * The rows a review passed, kept for the import step.
 *
 * They are stored in a file on the server's own disk (storage/app/imports, next to the
 * uploaded files, and removed by the same daily cleanup), not in the session: a large file's
 * rows would otherwise travel with every request. The session keeps only the token that
 * names the file, and the entity that was reviewed.
 */
class ReviewedRows
{
    private const SESSION_TOKEN = 'serImportToken';

    private const SESSION_ENTITY = 'serImportEntity';

    /**
     * Stores reviewed rows, replacing earlier ones, and returns their token.
     *
     * @param  array<int, array{sourceField: string, leantimeField: string}>  $fields  Column mappings.
     * @param  array<int|string, array<string, mixed>>  $values  Rows after the review.
     * @param  string  $entity  The entity that was reviewed.
     */
    public function store(array $fields, array $values, string $entity): string
    {
        $this->forget();

        $token = Uuid::uuid4()->toString();
        if (! is_dir($this->directory())) {
            mkdir($this->directory(), 0775, true);
        }
        file_put_contents(
            $this->path($token),
            json_encode(['fields' => $fields, 'values' => $values], JSON_UNESCAPED_UNICODE | JSON_THROW_ON_ERROR),
            LOCK_EX
        );

        session([self::SESSION_TOKEN => $token, self::SESSION_ENTITY => $entity]);

        return $token;
    }

    /**
     * Token of the stored rows, or '' when there are none.
     */
    public function token(): string
    {
        $token = session(self::SESSION_TOKEN, '');

        return is_string($token) && Uuid::isValid($token) ? $token : '';
    }

    /**
     * The stored rows, mappings and entity; empty when there are none.
     *
     * @return array{values: array, fields: array, entity: string}
     */
    public function load(): array
    {
        $nothing = ['values' => [], 'fields' => [], 'entity' => ''];
        $token = $this->token();
        if ($token === '' || ! is_file($this->path($token))) {
            return $nothing;
        }

        try {
            $data = json_decode((string) file_get_contents($this->path($token)), true, 512, JSON_THROW_ON_ERROR);
        } catch (\JsonException $e) {
            Log::warning('Unreadable reviewed import rows '.$token);

            return $nothing;
        }

        return [
            'values' => (array) ($data['values'] ?? []),
            'fields' => (array) ($data['fields'] ?? []),
            'entity' => (string) session(self::SESSION_ENTITY, ''),
        ];
    }

    /**
     * Removes the stored rows and their token.
     */
    public function forget(): void
    {
        $token = $this->token();
        if ($token !== '' && is_file($this->path($token))) {
            @unlink($this->path($token));
        }

        session()->forget([self::SESSION_TOKEN, self::SESSION_ENTITY, 'serValues', 'serFields']);
    }

    private function path(string $token): string
    {
        return $this->directory().'/reviewed-'.$token.'.json';
    }

    private function directory(): string
    {
        return storage_path('app/imports');
    }
}
