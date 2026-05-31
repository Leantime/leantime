<?php

namespace Leantime\Core\Support;

class Build
{
    public function __construct(private object $object) {}

    /**
     * @param  string  $key  The property name
     * @param  mixed  $value  The property value or a callable to set the nested property value
     **/
    public function set(string $key, mixed $value): self
    {
        $keys = explode('.', $key);
        $lastKey = array_pop($keys);
        $currentElement = &$this->object;
        $this->handleDotNotation($currentElement, $keys);

        if (is_callable($value)) {
            // Apply closure to a new builder for the nested element (object or array)
            $nestedElement = is_array($currentElement) ? ($currentElement[$lastKey] ?? []) : ($currentElement->$lastKey ?? new \stdClass);
            $value(build($nestedElement));
            $this->setValue($currentElement, $lastKey, $nestedElement);

            return $this;
        }

        $this->setValue($currentElement, $lastKey, $value);

        return $this;
    }

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
     * @param  string  $method
     * @param  array  $params
     **/
    public function __call($method, $params): mixed
    {
        if (
            ! str_starts_with($method, 'set')
            && ! str_starts_with($method, 'tap')
            && ! str_starts_with($method, 'get')
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
                        is_object($currentElement) && ! property_exists($currentElement, $propName),
                        is_array($currentElement) && ! isset($currentElement[$propName]),
                    ])
                ) {
                    continue;
                }

                $property = $propName;
                $isset = true;
                break;
            }

            if (! $isset) {
                if ($baseMethod !== 'get') {
                    throw new \Exception('You must use properties that already exist when using dynamic set methods (E.G. "setPropertyname")');
                }

                return null;
            }
        }

        $property = implode('.', $properties);

        return $this->{$baseMethod}($property, ...$params);
    }

    private function handleDotNotation(mixed &$currentElement, array $keys): void
    {
        foreach ($keys as $nestedKey) {
            if (! is_array($currentElement) && ! is_object($currentElement)) {
                throw new \Exception('Can\'t set value on non array/object');
            }

            if (! isset($currentElement[$nestedKey]) && ! isset($currentElement->$nestedKey)) {
                if (
                    version_compare(PHP_VERSION, '8.2.0', '>=')
                    && is_object($currentElement)
                    && empty((new \ReflectionClass($currentElement))->getAttributes('AllowDynamicProperties'))
                ) {
                    throw new \Exception('This property doesn\'t support dynamic property setting');
                }

                $this->setValue($currentElement, $nestedKey, is_array($currentElement) ? [] : new \stdClass);
            }
        }
    }

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
            // Coerce external/API data to the declared property type before
            // assigning. Models built from the marketplace API (e.g.
            // MarketplacePlugin) declare non-nullable typed properties, but the
            // API can return null or a mismatched type (e.g. a string for an
            // `array $categories`), which PHP 8 rejects with a TypeError and 500s
            // the whole request. Coercing here protects every Build-hydrated
            // model, not just one. (#3207, #3342)
            if (property_exists($property, $key)) {
                $reflection = new \ReflectionProperty($property, $key);
                $type = $reflection->getType();
                if ($type instanceof \ReflectionNamedType && $type->isBuiltin()) {
                    $value = $this->coerceToBuiltinType($value, $type);
                }
            }
            $property->$key = $value;
        }
    }

    /**
     * Coerces a value to a builtin (scalar/array) property type so external/API
     * data can't trip a TypeError on a typed property. Casts compatible scalars;
     * when a value can't be safely coerced (e.g. a non-numeric string for an int,
     * or a scalar for an array) it falls back to null for nullable types or the
     * type's zero-value otherwise — never the raw, mismatched value.
     */
    private function coerceToBuiltinType(mixed $value, \ReflectionNamedType $type): mixed
    {
        $typeName = $type->getName();
        $allowsNull = $type->allowsNull();

        if ($value === null) {
            return $allowsNull ? null : $this->builtinTypeDefault($typeName);
        }

        // Fallback for a value that can't be coerced to the declared type.
        $fallback = fn () => $allowsNull ? null : $this->builtinTypeDefault($typeName);

        return match ($typeName) {
            'string' => is_string($value) ? $value : (is_scalar($value) ? (string) $value : $fallback()),
            'int' => is_int($value) ? $value : (is_numeric($value) ? (int) $value : $fallback()),
            'float' => is_float($value) ? $value : (is_numeric($value) ? (float) $value : $fallback()),
            'bool' => is_bool($value) ? $value : (bool) $value,
            // Don't wrap scalars into a single-element array — array-typed model
            // properties are consumed as lists of associative rows downstream, so
            // a wrapped scalar would only defer the crash to the template.
            'array' => is_array($value) ? $value : $fallback(),
            default => $value,
        };
    }

    /**
     * The zero-value for a non-nullable builtin type.
     */
    private function builtinTypeDefault(string $typeName): mixed
    {
        return match ($typeName) {
            'string' => '',
            'int' => 0,
            'float' => 0.0,
            'bool' => false,
            'array' => [],
            default => null,
        };
    }

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

    public function getAndTap(string $key = '', ?callable $callback = null): mixed
    {
        $result = $this->get($key);

        return tap($result, $callback);
    }
}
