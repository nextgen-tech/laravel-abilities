<?php

namespace NGT\Laravel\Abilities;

use Illuminate\Support\Arr;
use Illuminate\Support\HtmlString;

class Ability
{
    protected $label = '';

    protected $slug = '';

    protected $options = [];

    protected $group = [];

    public function __construct($label, $slug, array $options = [], array $group = [])
    {
        $this->label   = $label;
        $this->slug    = $slug;
        $this->options = collect($options);
        $this->group   = collect($group);
    }

    public function label()
    {
        return $this->label;
    }

    public function slug()
    {
        return $this->slug;
    }

    public function name()
    {
        return $this->prefixed($this->slug);
    }

    public function groupName()
    {
        return $this->group->get('prefix');
    }

    public function groupLabel()
    {
        return new HtmlString($this->group->get('label') ?: '');
    }

    public function aliasedActions()
    {
        $actions = $this->options->get('aliases', []);

        return array_map(function ($action) {
            return $this->prefixed($action);
        }, Arr::wrap($actions));
    }

    public function hasCustomCallback()
    {
        return Arr::has($this->options, 'callback');
    }

    public function callCustomCallback($user)
    {
        $callback = $this->options->get('callback');

        if (is_callable($callback)) {
            return call_user_func($callback, $user, $this);
        }

        return false;
    }

    public function toArray()
    {
        return [
            'label'      => $this->label(),
            'slug'       => $this->slug(),
            'name'       => $this->name(),
            'groupName'  => $this->groupName(),
            'groupLabel' => $this->groupLabel(),
        ];
    }

    protected function prefixed($value)
    {
        $prefix = $this->group->get('prefix');

        if (!empty($prefix)) {
            return $prefix . '.' . $value;
        }

        return $value;
    }
}
