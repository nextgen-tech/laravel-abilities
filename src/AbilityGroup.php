<?php

namespace NGT\Laravel\Abilities;

use Illuminate\Support\Arr;

class AbilityGroup
{
    public static function merge($new, $old)
    {
        return [
            'prefix' => static::formatPrefix($new, $old),
            'label'  => static::formatLabel($new, $old),
        ];
    }

    protected static function formatPrefix($new, $old)
    {
        $old = Arr::get($old, 'prefix');
        $new = Arr::get($new, 'prefix');

        $prefix = sprintf('%s.%s', trim($old, '.'), trim($new, '.'));

        return trim($prefix, '.');
    }

    protected static function formatLabel($new, $old)
    {
        $old = Arr::get($old, 'label');
        $new = Arr::get($new, 'label');

        if (empty($old)) {
            return $new;
        }

        if (empty($new)) {
            return $old;
        }

        return $old . ' &gt; ' . $new;
    }
}
