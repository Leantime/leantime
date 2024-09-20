<?php

namespace Leantime\Domain\Plugins\Contracts;

interface PluginDisplayStrategy
{
    /**
     * @return string
     */
    public function getCardDesc(): string;

    /**
     * @return string
     */
    public function getPluginImageData(): string;

    /**
     * @return array
     */
    public function getMetadataLinks(): array;

    /**
     * @return string
     */
    public function getControlsView(): string;
}
