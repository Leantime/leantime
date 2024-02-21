<?php

declare(strict_types=1);

namespace Metasyntactical\Composer\LicenseCheck;

final class ComposerConfig
{
    private array $allowList;
    private array $denyList;
    private array $allowedPackages;

    /**
     * @psalm-param array{allow-list?: list<mixed>, deny-list?: list<mixed>, allowed-packages?: list<mixed>} $options
     */
    public function __construct(array $options)
    {
        $this->allowList = array_filter(
            $options['allow-list'] ?? [],
            'is_string',
        );
        $this->denyList = array_filter(
            $options['deny-list'] ?? [],
            'is_string',
        );
        $this->allowedPackages = array_filter(
            $options['allowed-packages'] ?? [],
            'is_string',
        );
    }

    public function allowList(): array
    {
        return $this->allowList;
    }

    public function denyList(): array
    {
        return $this->denyList;
    }

    public function allowePackages(): array
    {
        return $this->allowedPackages;
    }
}
