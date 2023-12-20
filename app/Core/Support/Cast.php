<?php

namespace Leantime\Core\Support;

class Cast
{
    public function __construct(private object $object)
    {
        //
    }

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
                class_exists($type) => (new self($sourceObj->$name))->castTo($type),
                'array', 'object', 'stdClass' => $this->handleIterator($sourceObj->$name),
                is_null($type) => $sourceObj->$name,
                default => settype($sourceObj->$name, $type),
            });
        }

        return $returnObj->get();
    }

    protected function handleIterator(array|object $iterator): array|object {
        $result = is_object($iterator) ? new \stdClass : [];
        foreach ($iterator as $key => $value) {
            $result[$key] = match (true) {
                class_exists(get_class($value)) => (new self($value))->castTo(get_class($value)),
                is_array($value) || is_object($value) => $this->{__FUNCTION__}($value),
                default => $value,
            };
        }
        return $result;
    }
}
