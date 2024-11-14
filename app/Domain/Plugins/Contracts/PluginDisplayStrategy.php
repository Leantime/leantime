<?php

namespace Leantime\Domain\Plugins\Contracts;

interface PluginDisplayStrategy
{
    public function getCardDesc(): string;

    public function getPluginImageData(): string;

    public function getMetadataLinks(): array;

    public function getControlsView(): string;
}
