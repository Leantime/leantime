<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown;

/**
 * Interface for an HTML-to-Markdown converter.
 *
 * @author Colin O'Dell <colinodell@gmail.com>
 *
 * @link https://github.com/thephpleague/html-to-markdown/ Latest version on GitHub.
 *
 * @license http://www.opensource.org/licenses/mit-license.php MIT
 */
interface HtmlConverterInterface
{
    /**
     * Convert the given $html to Markdown
     *
     * @return string The Markdown version of the html
     *
     * @throws \InvalidArgumentException
     */
    public function convert(string $html): string;
}
