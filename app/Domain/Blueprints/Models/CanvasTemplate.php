<?php

namespace Leantime\Domain\Blueprints\Models;

class CanvasTemplate
{
    public string $slug;

    public string $icon;

    public string $disclaimer;

    public int $minColumns;

    public int $minWidthOffset;

    public array $boxes;

    public array $statusLabels;

    public array $relatesLabels;

    public array $dataLabels;

    public array $layout;

    /**
     * Optional ContentTemplates key (see app/Domain/ContentTemplates).
     *
     * When set, freshly-created boards of this canvas type auto-apply the
     * referenced content template's items, giving the user a non-empty
     * starting point. Looked up against the registry as
     * forAppliesTo($this->slug)[$startContent].
     *
     * Null when the blueprint ships no starter content (the default).
     */
    public ?string $startContent;

    private const DEFAULT_STATUS_LABELS = [
        'status_draft' => ['icon' => 'fa-circle-question', 'color' => 'blue', 'title' => 'status.draft', 'dropdown' => 'info', 'active' => true],
        'status_review' => ['icon' => 'fa-circle-exclamation', 'color' => 'orange', 'title' => 'status.review', 'dropdown' => 'warning', 'active' => true],
        'status_valid' => ['icon' => 'fa-circle-check', 'color' => 'green', 'title' => 'status.valid', 'dropdown' => 'success', 'active' => true],
        'status_hold' => ['icon' => 'fa-circle-h', 'color' => 'red', 'title' => 'status.hold', 'dropdown' => 'danger', 'active' => true],
        'status_invalid' => ['icon' => 'fa-circle-xmark', 'color' => 'red', 'title' => 'status.invalid', 'dropdown' => 'danger', 'active' => true],
    ];

    private const DEFAULT_RELATES_LABELS = [
        'relates_none' => ['icon' => 'fa-border-none', 'color' => 'grey', 'title' => 'relates.none', 'dropdown' => 'default', 'active' => true],
        'relates_customers' => ['icon' => 'fa-users', 'color' => 'green', 'title' => 'relates.customers', 'dropdown' => 'success', 'active' => true],
        'relates_offerings' => ['icon' => 'fa-barcode', 'color' => 'red', 'title' => 'relates.offerings', 'dropdown' => 'danger', 'active' => true],
        'relates_capabilities' => ['icon' => 'fa-pen-ruler', 'color' => 'blue', 'title' => 'relates.capabilities', 'dropdown' => 'info', 'active' => true],
        'relates_financials' => ['icon' => 'fa-money-bill', 'color' => 'yellow', 'title' => 'relates.financials', 'dropdown' => 'warning', 'active' => true],
        'relates_markets' => ['icon' => 'fa-shop', 'color' => 'brown', 'title' => 'relates.markets', 'dropdown' => 'default', 'active' => true],
        'relates_environment' => ['icon' => 'fa-tree', 'color' => 'darkgreen', 'title' => 'relates.environment', 'dropdown' => 'default', 'active' => true],
        'relates_firm' => ['icon' => 'fa-building', 'color' => 'darkblue', 'title' => 'relates.firm', 'dropdown' => 'info', 'active' => true],
    ];

    private const DEFAULT_DATA_LABELS = [
        1 => ['title' => 'label.assumptions', 'field' => 'assumptions', 'active' => true],
        2 => ['title' => 'label.data', 'field' => 'data', 'active' => true],
        3 => ['title' => 'label.conclusion', 'field' => 'conclusion', 'active' => true],
    ];

    /**
     * @param  array<string, mixed>  $data  Parsed YAML data
     */
    public function __construct(array $data)
    {
        $this->slug = $data['slug'];
        $this->icon = $data['icon'] ?? 'fa-x';
        $this->disclaimer = $data['disclaimer'] ?? '';
        $this->minColumns = $data['minColumns'] ?? 2;
        $this->minWidthOffset = $data['minWidthOffset'] ?? 0;
        $this->boxes = $data['boxes'] ?? [];
        $this->layout = $data['layout'] ?? [];
        $this->startContent = isset($data['startContent']) && $data['startContent'] !== ''
            ? (string) $data['startContent']
            : null;

        $this->statusLabels = $this->resolveLabels($data, 'statusLabels', self::DEFAULT_STATUS_LABELS);
        $this->relatesLabels = $this->resolveLabels($data, 'relatesLabels', self::DEFAULT_RELATES_LABELS);
        $this->dataLabels = $this->resolveDataLabels($data);
    }

    /**
     * @return string Database type value (e.g., "swotcanvas")
     */
    public function getDatabaseType(): string
    {
        return $this->slug.'canvas';
    }

    /**
     * @return string Comment module identifier (e.g., "swotcanvasitem")
     */
    public function getCommentModule(): string
    {
        return $this->slug.'canvasitem';
    }

    /**
     * @return string Session key for tracking current board
     */
    public function getSessionKey(): string
    {
        return 'current'.strtoupper($this->slug).'Canvas';
    }

    /**
     * @param  array<string, mixed>  $data  Parsed YAML data
     * @param  string  $key  Label key
     * @param  array<string, mixed>  $defaults  Default labels
     * @return array<string, mixed>
     */
    private function resolveLabels(array $data, string $key, array $defaults): array
    {
        if (! array_key_exists($key, $data)) {
            return $defaults;
        }

        if ($data[$key] === null || $data[$key] === 'default') {
            return $defaults;
        }

        return $data[$key];
    }

    /**
     * @param  array<string, mixed>  $data  Parsed YAML data
     * @return array<int, array<string, mixed>>
     */
    private function resolveDataLabels(array $data): array
    {
        if (! array_key_exists('dataLabels', $data)) {
            return self::DEFAULT_DATA_LABELS;
        }

        if ($data['dataLabels'] === null || $data['dataLabels'] === 'default') {
            return self::DEFAULT_DATA_LABELS;
        }

        return $data['dataLabels'];
    }
}
