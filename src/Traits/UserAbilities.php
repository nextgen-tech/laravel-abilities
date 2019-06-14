<?php

namespace NGT\Laravel\Abilities\Traits;

use Illuminate\Support\Facades\Config;
use NGT\Laravel\Abilities\Ability;

trait UserAbilities
{
    public function group()
    {
        return $this->belongsTo(
            Config::get('abilities.models.user_group'),
            'user_group_id',
            'id',
            'group'
        );
    }

    public function abilities()
    {
        return $this->hasMany(
            Config::get('abilities.models.user_ability'),
            'user_id',
            'id'
        );
    }

    public function ability($abilityName)
    {
        return $this->abilities->where('name', $abilityName)->first();
    }

    public function allows(Ability $ability)
    {
        $groupAbility = $this->ability($ability->name());

        if ($groupAbility) {
            return $groupAbility->is_able === 0;
        }
    }

    public function denies(Ability $ability)
    {
        $groupAbility = $this->ability($ability->name());

        if ($groupAbility) {
            return $groupAbility->is_able === 1;
        }
    }

    public function inherits(Ability $ability)
    {
        $groupAbility = $this->ability($ability->name());

        if ($groupAbility) {
            return $groupAbility->is_able === null;
        }

        return true;
    }

    public function isAble(Ability $ability)
    {
        $userAbility = $this->ability($ability->name());

        // If user has group and user ability is not defined or user ability is "inherited"
        if ($this->group && (!$userAbility || $userAbility->is_able === null)) {
            return $this->group->isAble($ability);
        }

        // If user ability is defined
        if ($userAbility) {
            return (bool) $userAbility->is_able;
        }

        // Otherwise deny
        return false;
    }
}
