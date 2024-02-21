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

/**
 * Writes and reads values to/from an object/array graph.
 *
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
interface PropertyAccessorInterface
{
    /**
     * Sets the value at the end of the property path of the object graph.
     *
     * Example:
     *
     *     use Symfony\Component\PropertyAccess\PropertyAccess;
     *
     *     $propertyAccessor = PropertyAccess::createPropertyAccessor();
     *
     *     echo $propertyAccessor->setValue($object, 'child.name', 'Fabien');
     *     // equals echo $object->getChild()->setName('Fabien');
     *
     * This method first tries to find a public setter for each property in the
     * path. The name of the setter must be the camel-cased property name
     * prefixed with "set".
     *
     * If the setter does not exist, this method tries to find a public
     * property. The value of the property is then changed.
     *
     * If neither is found, an exception is thrown.
     *
     * @param object|array                 $objectOrArray The object or array to modify
     * @param string|PropertyPathInterface $propertyPath  The property path to modify
     * @param mixed                        $value         The value to set at the end of the property path
     *
     * @throws Exception\InvalidArgumentException If the property path is invalid
     * @throws Exception\AccessException          If a property/index does not exist or is not public
     * @throws Exception\UnexpectedTypeException  If a value within the path is neither object nor array
     */
    public function setValue(&$objectOrArray, $propertyPath, $value);

    /**
     * Returns the value at the end of the property path of the object graph.
     *
     * Example:
     *
     *     use Symfony\Component\PropertyAccess\PropertyAccess;
     *
     *     $propertyAccessor = PropertyAccess::createPropertyAccessor();
     *
     *     echo $propertyAccessor->getValue($object, 'child.name');
     *     // equals echo $object->getChild()->getName();
     *
     * This method first tries to find a public getter for each property in the
     * path. The name of the getter must be the camel-cased property name
     * prefixed with "get", "is", or "has".
     *
     * If the getter does not exist, this method tries to find a public
     * property. The value of the property is then returned.
     *
     * If none of them are found, an exception is thrown.
     *
     * @param object|array                 $objectOrArray The object or array to traverse
     * @param string|PropertyPathInterface $propertyPath  The property path to read
     *
     * @return mixed
     *
     * @throws Exception\InvalidArgumentException If the property path is invalid
     * @throws Exception\AccessException          If a property/index does not exist or is not public
     * @throws Exception\UnexpectedTypeException  If a value within the path is neither object
     *                                            nor array
     */
    public function getValue($objectOrArray, $propertyPath);

    /**
     * Returns whether a value can be written at a given property path.
     *
     * Whenever this method returns true, {@link setValue()} is guaranteed not
     * to throw an exception when called with the same arguments.
     *
     * @param object|array                 $objectOrArray The object or array to check
     * @param string|PropertyPathInterface $propertyPath  The property path to check
     *
     * @return bool
     *
     * @throws Exception\InvalidArgumentException If the property path is invalid
     */
    public function isWritable($objectOrArray, $propertyPath);

    /**
     * Returns whether a property path can be read from an object graph.
     *
     * Whenever this method returns true, {@link getValue()} is guaranteed not
     * to throw an exception when called with the same arguments.
     *
     * @param object|array                 $objectOrArray The object or array to check
     * @param string|PropertyPathInterface $propertyPath  The property path to check
     *
     * @return bool
     *
     * @throws Exception\InvalidArgumentException If the property path is invalid
     */
    public function isReadable($objectOrArray, $propertyPath);
}
