<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown\Converter;

use League\HTMLToMarkdown\Configuration;
use League\HTMLToMarkdown\ConfigurationAwareInterface;
use League\HTMLToMarkdown\ElementInterface;

class HeaderConverter implements ConverterInterface, ConfigurationAwareInterface
{
    public const STYLE_ATX    = 'atx';
    public const STYLE_SETEXT = 'setext';

    /** @var Configuration */
    protected $config;

    public function setConfig(Configuration $config): void
    {
        $this->config = $config;
    }

    public function convert(ElementInterface $element): string
    {
        $level = (int) \substr($element->getTagName(), 1, 1);
        $style = $this->config->getOption('header_style', self::STYLE_SETEXT);

        if (\strlen($element->getValue()) === 0) {
            return "\n";
        }

        if (($level === 1 || $level === 2) && ! $element->isDescendantOf('blockquote') && $style === self::STYLE_SETEXT) {
            return $this->createSetextHeader($level, $element->getValue());
        }

        return $this->createAtxHeader($level, $element->getValue());
    }

    /**
     * @return string[]
     */
    public function getSupportedTags(): array
    {
        return ['h1', 'h2', 'h3', 'h4', 'h5', 'h6'];
    }

    private function createSetextHeader(int $level, string $content): string
    {
        $length    = \function_exists('mb_strlen') ? \mb_strlen($content, 'utf-8') : \strlen($content);
        $underline = $level === 1 ? '=' : '-';

        return $content . "\n" . \str_repeat($underline, $length) . "\n\n";
    }

    private function createAtxHeader(int $level, string $content): string
    {
        $prefix = \str_repeat('#', $level) . ' ';

        return $prefix . $content . "\n\n";
    }
}
