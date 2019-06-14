<?php

namespace NGT\Laravel\Abilities;

use Illuminate\Contracts\Translation\Translator;
use Illuminate\Support\Arr;
use NGT\Laravel\Abilities\Facades\Ability;

class AbilityResource
{
    protected static $baseActions = [
        'index' => [
            'label' => 'abilities::abilities.index',
        ],
        'show' => [
            'label' => 'abilities::abilities.show',
        ],
        'create' => [
            'label'   => 'abilities::abilities.create',
            'aliases' => ['store'],
        ],
        'edit' => [
            'label'   => 'abilities::abilities.edit',
            'aliases' => ['update'],
        ],
        'destroy' => [
            'label' => 'abilities::abilities.destroy',
        ],
    ];

    private $translator;

    public function __construct(Translator $translator)
    {
        $this->translator = $translator;
    }

    public function register(array $options = [])
    {
        $actions = $this->filteredActions($options);

        return function () use ($actions) {
            foreach ($actions as $slug => $options) {
                Ability::define($slug, $this->translator->get($options['label']), Arr::only($options, 'aliases'));
            }
        };
    }

    protected function filteredActions($options)
    {
        if ($only = Arr::get($options, 'only')) {
            return Arr::only(static::$baseActions, Arr::wrap($only));
        }

        if ($except = Arr::get($options, 'except')) {
            return Arr::except(static::$baseActions, Arr::wrap($except));
        }

        return static::$baseActions;
    }
}
