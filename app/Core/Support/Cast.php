<?php

namespace Leantime\Core\Support;

use Illuminate\Support\Str;

/**
 * @todo the cast to method needs to be refactored to have better support of constructor params
 **/
class Cast
{
    protected array $mappings;

    /**
     * @param object|array $object
     **/
    public function __construct(private array|object $object)
    {
        if (is_array($this->object)) {
            $this->object = (object) $this->object;
        }
    }

    /**
     * @param string $classDest
     * @param array $constructParams
     * @param array $mappings
     * @return object
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \ReflectionException
     **/
    public function castTo(string $classDest, array $constructParams = [], array $mappings = []): object
    {
        $this->mappings ??= $mappings;

        if (! class_exists($classDest)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not exist.', $classDest));
        }

        $sourceObj = $this->object;
        $classRef = new \ReflectionClass($classDest);
        $properties = $classRef->getProperties();

        if (
            ! empty($reflectedConstructParams = $classRef->getConstructor()?->getParameters() ?? [])
            && empty($constructParams)
        ) {
            foreach ($reflectedConstructParams as $param) {
                if (isset($sourceObj->{$param->getName()})) {
                    $constructParams[] = $sourceObj->{$param->getName()};
                    continue;
                }

                if ($param->isOptional()) {
                    $constructParams[] = $param->getDefaultValue();
                    continue;
                }

                throw new \InvalidArgumentException(sprintf('Missing construct parameter %s.', $param->getName()));
            }
        }

        $returnObj = build(new $classDest(...$constructParams));

        foreach ($properties as $property) {
            $name = $property->getName();

            if (! isset($sourceObj->$name)) {
                if ($property->hasDefaultValue()) {
                    $returnObj->set($name, $property->getDefaultValue());
                    continue;
                }

                throw new \RuntimeException(sprintf('Property %s does not exist in source object.', $name));
            }

            try {
                $type = collect($mappings)->firstOrFail(fn ($mapping, $key) => in_array($key, [$name, '*']));
            } catch (\Illuminate\Support\ItemNotFoundException) {
                $type = ($reflectionType = $property->getType()) ? $reflectionType->getName() : null;
            }

            $returnObj->set($name, match (true) {
                enum_exists($type) => self::castEnum($sourceObj->$name, $type),
                $type !== 'stdClass' && class_exists($type) => (new self($sourceObj->$name))->castTo(
                    classDest: $type,
                    mappings: $this->getMatchingMappings($mappings, $name),
                ),
                in_array($type, ['array', 'object', 'stdClass']) => $this->handleIterator(
                    $sourceObj->$name,
                    $this->getMatchingMappings($mappings, $name)
                ),
                is_null($type) => $sourceObj->$name,
                default => self::castSimple($sourceObj->$name, $type),
            });
        }

        return $returnObj->get();
    }

    /**
     * @param mixed $value
     * @param string $simpleType
     * @return mixed
     * @throws \RuntimeException
     * @throws \InvalidArgumentException
     **/
    public static function castSimple(mixed $value, string $simpleType): mixed
    {
        if (is_null($castedValue = match ($simpleType) {
            'int', 'integer' => filter_var($value, FILTER_VALIDATE_INT, FILTER_NULL_ON_FAILURE),
            'float' => filter_var($value, FILTER_VALIDATE_FLOAT, FILTER_NULL_ON_FAILURE),
            'string', 'str' => is_array($value) || (is_object($value) && ! method_exists($value, '__toString')) ? null : (string) $value,
            'bool', 'boolean' => filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE),
            'object', 'stdClass' => is_array($value) ? (object) $value : null,
            'array' => is_object($value) ? (array) $value : null,
            default => throw new \InvalidArgumentException(sprintf('%s is not a simple type.', $simpleType)),
        })) {
            throw new \RuntimeException(sprintf('Could not cast value to type %s.', $simpleType));
        }

        return $castedValue;
    }

    /**
     * Cast to backed enum
     *
     * @param mixed $value
     * @param string $enumClass
     **/
    public static function castEnum(mixed $value, string $enumClass): mixed
    {
        // For non-backed enums, iterate and match by name.
        if (is_string($value)) {
            foreach ($enumClass::cases() as $case) {
                if ($case->name === $value) {
                    return $case;
                }
            }
        }

        // For backed enums, try to get the case by value
        if (
            is_subclass_of($enumClass, \BackedEnum::class)
            && ($enum = $enumClass::tryFrom($value) ?? false)
        ) {
            return $enum;
        }

        throw new \InvalidArgumentException(sprintf('Value cannot be casted to %s.', $enumClass));
    }

    /**
     * @param array|object $iterator
     * @param array $mappings
     * @return array|object
     **/
    protected function handleIterator(iterable $iterator, array $mappings = []): array|object {
        $result = is_object($iterator) ? new \stdClass : [];

        foreach ($iterator as $key => $value) {
            if (is_numeric($key)) {
                $type = $mappings['*'] ?? false;
            } else {
                if ($type = preg_match('/\<[a-zA-Z0-9\\\\]+\>/', $key)) {
                    $key = preg_replace('/\<[a-zA-Z0-9\\\\]+\>/', '', $key);
                } else {
                    $type = $mappings[$key] ?? $mappings['*'] ?? false;
                }
            }

            $value = match (true) {
                $type && enum_exists($type) => self::castEnum($value, $type),
                $type && class_exists($type) => (new self($value))->castTo($type),
                $type && in_array($type, ['string', 'str', 'int', 'integer', 'float', 'bool', 'boolean']) => self::castSimple($value, $type),
                $type && in_array($type, ['array', 'object', 'stdClass']),
                is_array($value),
                is_object($value) => $this->{__FUNCTION__}($value, $this->getMatchingMappings($mappings, (string) $key)),
                default => $value,
            };

            if (is_object($result)) {
                $result->$key = $value;
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }

    /**
     * @param array $mappings
     * @param string $propName
     * @return array
     **/
    protected function getMatchingMappings(array $mappings, string $propName): array
    {
        return collect($mappings)
            ->filter(fn ($mapping, $key) => Str::startsWith($key, "$propName.") || Str::startsWith($key, '*.'))
            ->mapWithKeys(fn ($mapping, $key) => [Str::after($key, '.') => $mapping])
            ->all();
    }
}
