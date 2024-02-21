<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown;

interface ElementInterface
{
    public function isBlock(): bool;

    public function isText(): bool;

    public function isWhitespace(): bool;

    public function getTagName(): string;

    public function getValue(): string;

    public function hasParent(): bool;

    public function getParent(): ?ElementInterface;

    public function getNextSibling(): ?ElementInterface;

    public function getPreviousSibling(): ?ElementInterface;

    /**
     * @param string|string[] $tagNames
     */
    public function isDescendantOf($tagNames): bool;

    public function hasChildren(): bool;

    /**
     * @return ElementInterface[]
     */
    public function getChildren(): array;

    public function getNext(): ?ElementInterface;

    public function getSiblingPosition(): int;

    public function getChildrenAsString(): string;

    public function setFinalMarkdown(string $markdown): void;

    public function getListItemLevel(): int;

    public function getAttribute(string $name): string;
}
