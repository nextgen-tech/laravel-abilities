<?php

namespace NGT\Laravel\Abilities\Traits;

use NGT\Laravel\Abilities\Ability;
use Illuminate\Support\Facades\Config;

trait UserGroupAbilities
{
    public function abilities()
    {
        return $this->hasMany(
            Config::get('abilities.models.user_group_ability'), 'user_group_id', 'id'
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
            return $groupAbility->is_able === 1;
        }
    }

    public function denies(Ability $ability)
    {
        $groupAbility = $this->ability($ability->name());

        if ($groupAbility) {
            return $groupAbility->is_able === 0;
        }
    }

    public function isAble(Ability $ability)
    {
        $groupAbility = $this->ability($ability->name());

        if (!$groupAbility) {
            return false;
        }

        return (bool) $groupAbility->is_able;
    }
}
