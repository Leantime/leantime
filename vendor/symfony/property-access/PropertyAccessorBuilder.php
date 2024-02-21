<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Symfony\Component\PropertyAccess;

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\PropertyInfo\PropertyReadInfoExtractorInterface;
use Symfony\Component\PropertyInfo\PropertyWriteInfoExtractorInterface;

/**
 * A configurable builder to create a PropertyAccessor.
 *
 * @author Jérémie Augustin <jeremie.augustin@pixel-cookers.com>
 */
class PropertyAccessorBuilder
{
    /** @var int */
    private $magicMethods = PropertyAccessor::MAGIC_GET | PropertyAccessor::MAGIC_SET;
    private $throwExceptionOnInvalidIndex = false;
    private $throwExceptionOnInvalidPropertyPath = true;

    /**
     * @var CacheItemPoolInterface|null
     */
    private $cacheItemPool;

    /**
     * @var PropertyReadInfoExtractorInterface|null
     */
    private $readInfoExtractor;

    /**
     * @var PropertyWriteInfoExtractorInterface|null
     */
    private $writeInfoExtractor;

    /**
     * Enables the use of all magic methods by the PropertyAccessor.
     *
     * @return $this
     */
    public function enableMagicMethods(): self
    {
        $this->magicMethods = PropertyAccessor::MAGIC_GET | PropertyAccessor::MAGIC_SET | PropertyAccessor::MAGIC_CALL;

        return $this;
    }

    /**
     * Disable the use of all magic methods by the PropertyAccessor.
     *
     * @return $this
     */
    public function disableMagicMethods(): self
    {
        $this->magicMethods = PropertyAccessor::DISALLOW_MAGIC_METHODS;

        return $this;
    }

    /**
     * Enables the use of "__call" by the PropertyAccessor.
     *
     * @return $this
     */
    public function enableMagicCall()
    {
        $this->magicMethods |= PropertyAccessor::MAGIC_CALL;

        return $this;
    }

    /**
     * Enables the use of "__get" by the PropertyAccessor.
     */
    public function enableMagicGet(): self
    {
        $this->magicMethods |= PropertyAccessor::MAGIC_GET;

        return $this;
    }

    /**
     * Enables the use of "__set" by the PropertyAccessor.
     *
     * @return $this
     */
    public function enableMagicSet(): self
    {
        $this->magicMethods |= PropertyAccessor::MAGIC_SET;

        return $this;
    }

    /**
     * Disables the use of "__call" by the PropertyAccessor.
     *
     * @return $this
     */
    public function disableMagicCall()
    {
        $this->magicMethods &= ~PropertyAccessor::MAGIC_CALL;

        return $this;
    }

    /**
     * Disables the use of "__get" by the PropertyAccessor.
     *
     * @return $this
     */
    public function disableMagicGet(): self
    {
        $this->magicMethods &= ~PropertyAccessor::MAGIC_GET;

        return $this;
    }

    /**
     * Disables the use of "__set" by the PropertyAccessor.
     *
     * @return $this
     */
    public function disableMagicSet(): self
    {
        $this->magicMethods &= ~PropertyAccessor::MAGIC_SET;

        return $this;
    }

    /**
     * @return bool whether the use of "__call" by the PropertyAccessor is enabled
     */
    public function isMagicCallEnabled()
    {
        return (bool) ($this->magicMethods & PropertyAccessor::MAGIC_CALL);
    }

    /**
     * @return bool whether the use of "__get" by the PropertyAccessor is enabled
     */
    public function isMagicGetEnabled(): bool
    {
        return $this->magicMethods & PropertyAccessor::MAGIC_GET;
    }

    /**
     * @return bool whether the use of "__set" by the PropertyAccessor is enabled
     */
    public function isMagicSetEnabled(): bool
    {
        return $this->magicMethods & PropertyAccessor::MAGIC_SET;
    }

    /**
     * Enables exceptions when reading a non-existing index.
     *
     * This has no influence on writing non-existing indices with PropertyAccessorInterface::setValue()
     * which are always created on-the-fly.
     *
     * @return $this
     */
    public function enableExceptionOnInvalidIndex()
    {
        $this->throwExceptionOnInvalidIndex = true;

        return $this;
    }

    /**
     * Disables exceptions when reading a non-existing index.
     *
     * Instead, null is returned when calling PropertyAccessorInterface::getValue() on a non-existing index.
     *
     * @return $this
     */
    public function disableExceptionOnInvalidIndex()
    {
        $this->throwExceptionOnInvalidIndex = false;

        return $this;
    }

    /**
     * @return bool whether an exception is thrown or null is returned when reading a non-existing index
     */
    public function isExceptionOnInvalidIndexEnabled()
    {
        return $this->throwExceptionOnInvalidIndex;
    }

    /**
     * Enables exceptions when reading a non-existing property.
     *
     * This has no influence on writing non-existing indices with PropertyAccessorInterface::setValue()
     * which are always created on-the-fly.
     *
     * @return $this
     */
    public function enableExceptionOnInvalidPropertyPath()
    {
        $this->throwExceptionOnInvalidPropertyPath = true;

        return $this;
    }

    /**
     * Disables exceptions when reading a non-existing index.
     *
     * Instead, null is returned when calling PropertyAccessorInterface::getValue() on a non-existing index.
     *
     * @return $this
     */
    public function disableExceptionOnInvalidPropertyPath()
    {
        $this->throwExceptionOnInvalidPropertyPath = false;

        return $this;
    }

    /**
     * @return bool whether an exception is thrown or null is returned when reading a non-existing property
     */
    public function isExceptionOnInvalidPropertyPath()
    {
        return $this->throwExceptionOnInvalidPropertyPath;
    }

    /**
     * Sets a cache system.
     *
     * @return $this
     */
    public function setCacheItemPool(CacheItemPoolInterface $cacheItemPool = null)
    {
        $this->cacheItemPool = $cacheItemPool;

        return $this;
    }

    /**
     * Gets the used cache system.
     *
     * @return CacheItemPoolInterface|null
     */
    public function getCacheItemPool()
    {
        return $this->cacheItemPool;
    }

    /**
     * @return $this
     */
    public function setReadInfoExtractor(?PropertyReadInfoExtractorInterface $readInfoExtractor)
    {
        $this->readInfoExtractor = $readInfoExtractor;

        return $this;
    }

    public function getReadInfoExtractor(): ?PropertyReadInfoExtractorInterface
    {
        return $this->readInfoExtractor;
    }

    /**
     * @return $this
     */
    public function setWriteInfoExtractor(?PropertyWriteInfoExtractorInterface $writeInfoExtractor)
    {
        $this->writeInfoExtractor = $writeInfoExtractor;

        return $this;
    }

    public function getWriteInfoExtractor(): ?PropertyWriteInfoExtractorInterface
    {
        return $this->writeInfoExtractor;
    }

    /**
     * Builds and returns a new PropertyAccessor object.
     *
     * @return PropertyAccessorInterface
     */
    public function getPropertyAccessor()
    {
        $throw = PropertyAccessor::DO_NOT_THROW;

        if ($this->throwExceptionOnInvalidIndex) {
            $throw |= PropertyAccessor::THROW_ON_INVALID_INDEX;
        }

        if ($this->throwExceptionOnInvalidPropertyPath) {
            $throw |= PropertyAccessor::THROW_ON_INVALID_PROPERTY_PATH;
        }

        return new PropertyAccessor($this->magicMethods, $throw, $this->cacheItemPool, $this->readInfoExtractor, $this->writeInfoExtractor);
    }
}
