<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown;

class Configuration
{
    /** @var array<string, mixed> */
    protected $config;

    /**
     * @param array<string, mixed> $config
     */
    public function __construct(array $config = [])
    {
        $this->config = $config;

        $this->checkForDeprecatedOptions($config);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function merge(array $config = []): void
    {
        $this->checkForDeprecatedOptions($config);
        $this->config = \array_replace_recursive($this->config, $config);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function replace(array $config = []): void
    {
        $this->checkForDeprecatedOptions($config);
        $this->config = $config;
    }

    /**
     * @param mixed $value
     */
    public function setOption(string $key, $value): void
    {
        $this->checkForDeprecatedOptions([$key => $value]);
        $this->config[$key] = $value;
    }

    /**
     * @param mixed|null $default
     *
     * @return mixed|null
     */
    public function getOption(?string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->config;
        }

        if (! isset($this->config[$key])) {
            return $default;
        }

        return $this->config[$key];
    }

    /**
     * @param array<string, mixed> $config
     */
    private function checkForDeprecatedOptions(array $config): void
    {
        foreach ($config as $key => $value) {
            if ($key === 'bold_style' && $value !== '**') {
                @\trigger_error('Customizing the bold_style option is deprecated and may be removed in the next major version', E_USER_DEPRECATED);
            } elseif ($key === 'italic_style' && $value !== '*') {
                @\trigger_error('Customizing the italic_style option is deprecated and may be removed in the next major version', E_USER_DEPRECATED);
            }
        }
    }
}
