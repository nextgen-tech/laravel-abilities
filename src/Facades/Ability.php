<?php

namespace NGT\Laravel\Abilities\Facades;

use NGT\Laravel\Abilities\AbilityRegistrar;
use Illuminate\Support\Facades\Facade;

/**
 * @method static \NGT\Laravel\Abilities\AbilityRegistrar define($prefix, $label, array $options)
 */
class Ability extends Facade
{
    protected static function getFacadeAccessor()
    {
        return AbilityRegistrar::class;
    }
}
