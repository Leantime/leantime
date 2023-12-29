<?php

namespace Leantime\Core\Support;

class Cast
{
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
     * @return object
     * @throws \InvalidArgumentException
     * @throws \RuntimeException
     * @throws \ReflectionException
     **/
    public function castTo(string $classDest, array $constructParams = []): object
    {
        if (! class_exists($classDest)) {
            throw new \InvalidArgumentException(sprintf('Class %s does not exist.', $classDest));
        }

        $sourceObj = $this->object;
        $properties = (new \ReflectionClass($classDest))->getProperties();
        $returnObj = build(new $classDest(...$constructParams));

        foreach ($properties as $property) {
            $name = $property->getName();

            if (! isset($sourceObj->$name)) {
                throw new \RuntimeException(sprintf('Property %s does not exist in source object.', $name));
            }

            $type = ($reflectionType = $property->getType()) ? $reflectionType->getName() : null;

            $returnObj->set($name, match ($type) {
                $type !== 'stdClass' && class_exists($type) => (new self($sourceObj->$name))->castTo($type),
                'array', 'object', 'stdClass' => $this->handleIterator($sourceObj->$name, $name),
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
     * @param array|object $iterator
     * @return array|object
     **/
    protected function handleIterator(array|object $iterator, string $propertyName): array|object {
        $result = is_object($iterator) ? new \stdClass : [];

        foreach ($iterator as $key => $value) {
            $type = preg_match('/\<[a-zA-Z0-9\\\\]+\>/', $propertyName);

            if ($type) {
                $key = preg_replace('/\<[a-zA-Z0-9\\\\]+\>/', '', $key);
            }

            $result[$key] = match (true) {
                $type && class_exists($type) => (new self($value))->castTo($type),
                $type && in_array($type, ['string', 'str', 'int', 'integer', 'float', 'bool', 'boolean']) => self::castSimple($value, $type),
                $type && in_array($type, ['array', 'object', 'stdClass']), is_array($value), is_object($value) => $this->{__FUNCTION__}($value, $key),
                default => $value,
            };
        }

        return $result;
    }
}
