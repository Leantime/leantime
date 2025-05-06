<?php

namespace Leantime\Core\Domains;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

abstract class DTO
{
    /**
     * @param  Response|array  $data  The data to map to the DTO
     **/
    public function __construct(private Response|array $data)
    {
        $data = Arr::dot($data instanceof Response ? $data->json() : $data);
        $builder = build($this);
        $propertyAttributes = collect((new \ReflectionClass($this))->getProperties())->mapWithKeys(function ($property) {
            $property->setAccessible(true);

            return [$property->getName() => collect($property->getAttributes())->mapWithKeys(fn ($attr) => [$attr->getName() => $attr->getArguments()])];
        })->all();
        $propertyAttributes = Arr::dot($propertyAttributes);

        foreach ($data as $placement => $value) {
            $propertyPath = explode('.', Str::beforeLast($placement, '.'));
            $propertyName = array_shift($propertyPath);

            if (
                ($propKey = array_search($placement, $propertyAttributes))
                && Str::afterLast($propKey, '.') == 'Map'
            ) {
                $propertyPath = explode('.', Str::beforeLast($propKey, '.'));
                $propertyName = array_shift($propertyPath);
            }

            $placement = implode('.', array_filter([$propertyPath, $propertyName]));
            $attributes = array_filter(
                $propertyAttributes,
                fn ($key) => Str::beforeLast('.', $key) == $placement && Str::afterLast('.', $key) !== 'Map',
                ARRAY_FILTER_USE_KEY
            );

            foreach ($attributes as $key => $attrValue) {
                $attrName = Str::afterLast($key, '.');
                $value = $this->{Str::camel($attrName)}(params: $attrValue, value: $value);
            }

            $builder->set($placement, $value);
        }
    }

    /**
     * Validates values.
     *
     * @param  string[]  $params  validations rules to apply to the value
     *
     * @todo Implement. May use illuminate/validation later on.
     *
     * @see https://github.com/mattstauffer/Torch/tree/master/components/validation
     **/
    private function validate(array $params, mixed $value): mixed
    {
        return $value;
    }

    /**
     * Gets the DTO data as multidimensional an array
     **/
    public function toArray(): array
    {
        $props = get_class_vars($this::class);
        unset($props['data']);

        return collect($props)->map(fn ($defaultVal, $key) => $this->{$key} ?? $defaultVal)->all();
    }

    /**
     * Gets the DTO data as multidimensional an array
     **/
    public function all(): array
    {
        return $this->toArray();
    }
}
