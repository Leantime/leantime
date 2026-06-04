<?php

namespace Leantime\Domain\ContentTemplates\Models;

/**
 * Immutable value object representing a single content template loaded from disk.
 *
 * Templates describe SEED CONTENT — items to insert into a canvas, or articles
 * to create in a wiki — not the schema of the target. (Schemas live in
 * Blueprints' TemplateRegistry.)
 *
 * One of `canvas` or `wiki` payloads is populated, determined by `appliesTo`.
 */
final class ContentTemplate
{
    /**
     * @param  string  $key  Unique within (appliesTo, key). Slug-like.
     * @param  string  $title  Display name in the picker UI.
     * @param  string  $description  Short blurb shown alongside the title.
     * @param  string  $appliesTo  Target type — canvas type slug ("logicmodel", "leancanvas", ...) or "wiki".
     * @param  string|null  $sector  Optional grouping tag (e.g. "education", "health").
     * @param  string|null  $icon  Optional Font Awesome class for the selector UI.
     * @param  string|null  $author  Attribution. Surfaces in the picker and is preserved on export.
     * @param  string|null  $version  Template version. Surfaces in the picker; not used for migrations.
     * @param  string|null  $license  License identifier (e.g. "CC0", "MIT").
     * @param  array<string, mixed>  $payload  Content payload — shape depends on appliesTo.
     */
    public function __construct(
        public readonly string $key,
        public readonly string $title,
        public readonly string $description,
        public readonly string $appliesTo,
        public readonly ?string $sector = null,
        public readonly ?string $icon = null,
        public readonly ?string $author = null,
        public readonly ?string $version = null,
        public readonly ?string $license = null,
        public readonly array $payload = [],
    ) {}

    /**
     * Construct from the array shape produced by Symfony YAML parsing.
     *
     * @param  array<string, mixed>  $data  Decoded YAML.
     */
    public static function fromArray(array $data): self
    {
        $appliesTo = (string) ($data['appliesTo'] ?? '');
        $payload = (array) ($data[$appliesTo] ?? []);

        return new self(
            key: (string) ($data['key'] ?? ''),
            title: (string) ($data['title'] ?? ''),
            description: (string) ($data['description'] ?? ''),
            appliesTo: $appliesTo,
            sector: isset($data['sector']) ? (string) $data['sector'] : null,
            icon: isset($data['icon']) ? (string) $data['icon'] : null,
            author: isset($data['author']) ? (string) $data['author'] : null,
            version: isset($data['version']) ? (string) $data['version'] : null,
            license: isset($data['license']) ? (string) $data['license'] : null,
            payload: $payload,
        );
    }

    /**
     * True when this template carries a non-empty payload.
     */
    public function isUsable(): bool
    {
        return $this->key !== '' && $this->appliesTo !== '' && $this->payload !== [];
    }
}
