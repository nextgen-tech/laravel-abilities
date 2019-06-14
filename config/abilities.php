<?php

return [
    'path'   => base_path('routes/abilities.php'),

    'models' => [
        'user'               => App\User::class,
        'user_ability'       => App\UserAbility::class,

        'user_group'         => App\UserGroup::class,
        'user_group_ability' => App\UserGroupAbility::class,
    ],
];
