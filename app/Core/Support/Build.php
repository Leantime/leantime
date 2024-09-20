<?php

namespace Leantime\Core\Support;

class Build
{
    /**
     * @param object $object
     **/
    public function __construct(private object $object)
    {
    }

    /**
     * @param string $key   The property name
     * @param mixed  $value The property value or a callable to set the nested property value
     *
     * @return self
     **/
    public function set(string $key, mixed $value): self
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $currentElement = &$this->object;
        $this->handleDotNotation($currentElement, $keys);

        if (is_callable($value)) {
            // Apply closure to a new builder for the nested element (object or array)
            $nestedElement = is_array($currentElement) ? ($currentElement[$lastKey] ?? []) : ($currentElement->$lastKey ?? new \stdClass());
            $value(build($nestedElement));
            $this->setValue($currentElement, $lastKey, $nestedElement);

            return $this;
        }

        $this->setValue($currentElement, $lastKey, $value);

        return $this;
    }

    /**
     * @param string   $key
     * @param callable $configurator
     *
     * @return self
     **/
    public function tap(string $key, callable $configurator): self
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $currentElement = &$this->object;
        $this->handleDotNotation($currentElement, $keys);
        $configurator($currentElement->$lastKey ?? $currentElement[$lastKey]);

        return $this;
    }

    /**
     * @param string $method
     * @param array  $params
     *
     * @return mixed
     **/
    public function __call($method, $params): mixed
    {
        if (
            !str_starts_with($method, 'set')
            && !str_starts_with($method, 'tap')
            && !str_starts_with($method, 'get')
        ) {
            throw new \BadMethodCallException("Method $method does not exist");
        }

        $baseMethod = substr($method, 0, 3); // 'set', 'tap', or 'get'
        $property = substr($method, 3);

        // Convert camelCase to dot.notation
        $properties = explode('.', preg_replace('/(?<!^)[A-Z]/', '.$0', $property));

        $currentElement = $this->object;
        foreach ($properties as &$property) {
            $isset = false;
            foreach ([$property, lcfirst($property)] as $propName) {
                if (
                    in_array(true, [
                        is_object($currentElement) && !property_exists($currentElement, $propName),
                        is_array($currentElement) && !isset($currentElement[$propName]),
                    ])
                ) {
                    continue;
                }

                $property = $propName;
                $isset = true;
                break;
            }

            if (!$isset) {
                if ($baseMethod !== 'get') {
                    throw new \Exception('You must use properties that already exist when using dynamic set methods (E.G. "setPropertyname")');
                }

                return null;
            }
        }

        $property = join('.', $properties);

        return $this->{$baseMethod}($property, ...$params);
    }

    /**
     * @param mixed $currentElement
     * @param array $keys
     *
     * @return void
     **/
    private function handleDotNotation(mixed &$currentElement, array $keys): void
    {
        foreach ($keys as $nestedKey) {
            if (!is_array($currentElement) && !is_object($currentElement)) {
                throw new \Exception('Can\'t set value on non array/object');
            }

            if (!isset($currentElement[$nestedKey]) && !isset($currentElement->$nestedKey)) {
                if (
                    version_compare(PHP_VERSION, '8.2.0', '>=')
                    && is_object($currentElement)
                    && empty((new \ReflectionClass($currentElement))->getAttributes('AllowDynamicProperties'))
                ) {
                    throw new \Exception('This property doesn\'t support dynamic property setting');
                }

                $this->setValue($currentElement, $nestedKey, is_array($currentElement) ? [] : new \stdClass());
            }
        }
    }

    /**
     * @param object|array $property
     * @param string       $key
     * @param mixed        $value
     *
     * @return void
     **/
    private function setValue(object|array &$property, string $key, mixed $value): void
    {
        if (is_array($property)) {
            $property[$key] = $value;

            return;
        }

        if (method_exists($property, 'set'.ucfirst($key))) {
            $property->{'set'.ucfirst($key)}($value);
        } elseif (method_exists($this->object, 'set'.$key)) {
            $property->{'set'.$key}($value);
        } else {
            $property->$key = $value;
        }
    }

    /**
     * @param string $key
     *
     * @return mixed
     **/
    public function get(string $key = ''): mixed
    {
        if ($key === '') {
            return $this->object;
        }

        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $currentElement = &$this->object;
        $this->handleDotNotation($currentElement, $keys);

        return $currentElement->$lastKey ?? $currentElement[$lastKey] ?? null;
    }

    /**
     * @param string $key
     *
     * @return mixed
     **/
    public function getAndTap(string $key = '', ?callable $callback = null): mixed
    {
        $result = $this->get($key);

        return tap($result, $callback);
    }
}
