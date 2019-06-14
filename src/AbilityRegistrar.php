<?php

namespace NGT\Laravel\Abilities;

use ArrayIterator;
use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Support\Arrayable;
use Illuminate\Contracts\Support\Jsonable;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use InvalidArgumentException;
use IteratorAggregate;

class AbilityRegistrar implements Arrayable, Jsonable, IteratorAggregate
{
    private $container;

    protected $groupStack = [];

    protected static $abilities = [];

    public function __construct(Container $container)
    {
        $this->container = $container;
    }

    public function group(array $attributes, callable $abilities)
    {
        $this->updateGroupStack($attributes);

        $abilities($this);

        array_pop($this->groupStack);
    }

    public function resource($prefix, $label, array $options = [])
    {
        $attributes = [
            'prefix' => $prefix,
            'label'  => $label,
        ];

        $this->group($attributes, $this->container->make(AbilityResource::class)->register($options));
    }

    public function define($slug, $label, array $options = [])
    {
        $ability = new Ability($label, $slug, $options, $this->lastGroup());

        static::$abilities[$ability->name()] = $ability;

        return $this;
    }

    protected function updateGroupStack($attributes)
    {
        $attributes = AbilityGroup::merge($attributes, $this->lastGroup());

        $this->groupStack[] = $attributes;
    }

    protected function lastGroup()
    {
        if (!empty($this->groupStack)) {
            return end($this->groupStack);
        }

        return [];
    }

    public function toArray()
    {
        return static::$abilities;
    }

    public function groupBy($column)
    {
        return Collection::make(static::$abilities)
            ->groupBy(function (Ability $ability) use ($column) {
                $value = Arr::get($ability->toArray(), $column, false);

                if ($value !== false) {
                    return $value;
                }

                throw new InvalidArgumentException(sprintf('Key "%s" does not exists in abilties collection.', $column));
            });
    }

    public function toJson($options = 0)
    {
        return json_encode(static::$abilities, $options);
    }

    public function getIterator()
    {
        return new ArrayIterator(static::$abilities);
    }
}
