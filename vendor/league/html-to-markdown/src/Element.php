<?php

declare(strict_types=1);

namespace League\HTMLToMarkdown;

class Element implements ElementInterface
{
    /** @var \DOMNode */
    protected $node;

    /** @var ElementInterface|null */
    private $nextCached;

    /** @var \DOMNode|null */
    private $previousSiblingCached;

    public function __construct(\DOMNode $node)
    {
        $this->node = $node;

        $this->previousSiblingCached = $this->node->previousSibling;
    }

    public function isBlock(): bool
    {
        switch ($this->getTagName()) {
            case 'blockquote':
            case 'body':
            case 'div':
            case 'h1':
            case 'h2':
            case 'h3':
            case 'h4':
            case 'h5':
            case 'h6':
            case 'hr':
            case 'html':
            case 'li':
            case 'p':
            case 'ol':
            case 'ul':
                return true;
            default:
                return false;
        }
    }

    public function isText(): bool
    {
        return $this->getTagName() === '#text';
    }

    public function isWhitespace(): bool
    {
        return $this->getTagName() === '#text' && \trim($this->getValue()) === '';
    }

    public function getTagName(): string
    {
        return $this->node->nodeName;
    }

    public function getValue(): string
    {
        return $this->node->nodeValue ?? '';
    }

    public function hasParent(): bool
    {
        return $this->node->parentNode !== null;
    }

    public function getParent(): ?ElementInterface
    {
        return $this->node->parentNode ? new self($this->node->parentNode) : null;
    }

    public function getNextSibling(): ?ElementInterface
    {
        return $this->node->nextSibling !== null ? new self($this->node->nextSibling) : null;
    }

    public function getPreviousSibling(): ?ElementInterface
    {
        return $this->previousSiblingCached !== null ? new self($this->previousSiblingCached) : null;
    }

    public function hasChildren(): bool
    {
        return $this->node->hasChildNodes();
    }

    /**
     * @return ElementInterface[]
     */
    public function getChildren(): array
    {
        $ret = [];
        foreach ($this->node->childNodes as $node) {
            $ret[] = new self($node);
        }

        return $ret;
    }

    public function getNext(): ?ElementInterface
    {
        if ($this->nextCached === null) {
            $nextNode = $this->getNextNode($this->node);
            if ($nextNode !== null) {
                $this->nextCached = new self($nextNode);
            }
        }

        return $this->nextCached;
    }

    private function getNextNode(\DomNode $node, bool $checkChildren = true): ?\DomNode
    {
        if ($checkChildren && $node->firstChild) {
            return $node->firstChild;
        }

        if ($node->nextSibling) {
            return $node->nextSibling;
        }

        if ($node->parentNode) {
            return $this->getNextNode($node->parentNode, false);
        }

        return null;
    }

    /**
     * @param string[]|string $tagNames
     */
    public function isDescendantOf($tagNames): bool
    {
        if (! \is_array($tagNames)) {
            $tagNames = [$tagNames];
        }

        for ($p = $this->node->parentNode; $p !== false; $p = $p->parentNode) {
            if ($p === null) {
                return false;
            }

            if (\in_array($p->nodeName, $tagNames, true)) {
                return true;
            }
        }

        return false;
    }

    public function setFinalMarkdown(string $markdown): void
    {
        if ($this->node->ownerDocument === null) {
            throw new \RuntimeException('Unowned node');
        }

        if ($this->node->parentNode === null) {
            throw new \RuntimeException('Cannot setFinalMarkdown() on a node without a parent');
        }

        $markdownNode = $this->node->ownerDocument->createTextNode($markdown);
        $this->node->parentNode->replaceChild($markdownNode, $this->node);
    }

    public function getChildrenAsString(): string
    {
        return $this->node->C14N();
    }

    public function getSiblingPosition(): int
    {
        $position = 0;

        $parent = $this->getParent();
        if ($parent === null) {
            return $position;
        }

        // Loop through all nodes and find the given $node
        foreach ($parent->getChildren() as $currentNode) {
            if (! $currentNode->isWhitespace()) {
                $position++;
            }

            // TODO: Need a less-buggy way of comparing these
            // Perhaps we can somehow ensure that we always have the exact same object and use === instead?
            if ($this->equals($currentNode)) {
                break;
            }
        }

        return $position;
    }

    public function getListItemLevel(): int
    {
        $level  = 0;
        $parent = $this->getParent();

        while ($parent !== null && $parent->hasParent()) {
            if ($parent->getTagName() === 'li') {
                $level++;
            }

            $parent = $parent->getParent();
        }

        return $level;
    }

    public function getAttribute(string $name): string
    {
        if ($this->node instanceof \DOMElement) {
            return $this->node->getAttribute($name);
        }

        return '';
    }

    public function equals(ElementInterface $element): bool
    {
        if ($element instanceof self) {
            return $element->node === $this->node;
        }

        return false;
    }
}
