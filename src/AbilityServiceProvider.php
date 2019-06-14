<?php

namespace NGT\Laravel\Abilities;

use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AbilityServiceProvider extends ServiceProvider
{
    /**
     * Register the application services and config.
     *
     * @return void
     */
    public function register()
    {
        $this->mergeConfig();

        $this->app->singleton(AbilityRegistrar::class, function ($app) {
            return new AbilityRegistrar($app);
        });
    }

    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        $this->loadTranslations();

        $this->publishConfig();
        $this->publishTranslations();
        $this->publishModels();
        $this->publishMigrations();
        $this->publishAbilities();

        $this->loadAbilities();
        $this->defineGates();
    }

    /**
     * Merges package config into application.
     *
     * @return void
     */
    protected function mergeConfig()
    {
        $this->mergeConfigFrom(
            __DIR__ . '/../config/abilities.php',
            'abilities'
        );
    }

    /**
     * Loads package translations into application.
     *
     * @return void
     */
    protected function loadTranslations()
    {
        $this->loadTranslationsFrom(
            __DIR__ . '/../translations',
            'abilities'
        );
    }

    /**
     * Publish the config to application.
     *
     * @return void
     */
    protected function publishConfig()
    {
        $this->publishes(
            [__DIR__ . '/../config/abilities.php' => $this->app->configPath('abilities.php')],
            'config'
        );
    }

    /**
     * Publish translations to application.
     *
     * @return void
     */
    protected function publishTranslations()
    {
        $this->publishes(
            [__DIR__ . '/../translations' => $this->app->resourcePath('lang/vendor/abilities')],
            'translations'
        );
    }

    /**
     * Publish models to application.
     *
     * @return void
     */
    protected function publishModels()
    {
        $models = [
            __DIR__ . '/../stubs/models/UserAbility.stub'      => $this->app->basePath('app' . DIRECTORY_SEPARATOR . 'UserAbility.php'),
            __DIR__ . '/../stubs/models/UserGroup.stub'        => $this->app->basePath('app' . DIRECTORY_SEPARATOR . 'UserGroup.php'),
            __DIR__ . '/../stubs/models/UserGroupAbility.stub' => $this->app->basePath('app' . DIRECTORY_SEPARATOR . 'UserGroupAbility.php'),
        ];

        $this->publishes($models, 'models');
    }

    /**
     * Publish migrations to application.
     *
     * @return void
     */
    protected function publishMigrations()
    {
        $stubs = [
            'create_user_groups_table.stub'           => '2015_01_01_000000_create_user_groups_table.php',
            'alter_user_group_id_to_users_table.stub' => '2015_01_01_100000_alter_user_group_id_to_users_table.php',
            'create_user_abilities_table.stub'        => '2015_01_01_200000_create_user_abilities_table.php',
            'create_user_group_abilities_table.stub'  => '2015_01_01_300000_create_user_group_abilities_table.php',
        ];
        $migrations = [];

        foreach ($stubs as $key => $value) {
            $fromPath = __DIR__ . '/../stubs/migrations/' . $key;

            $migrations[$fromPath] = $this->app->databasePath('migrations/' . $value);
        }

        $this->publishes($migrations, 'migrations');
    }

    /**
     * Publish abilities list to application.
     *
     * @return void
     */
    protected function publishAbilities()
    {
        $this->publishes([__DIR__ . '/../stubs/abilities.stub' => $this->app->basePath('routes/abilities.php')]);
    }

    /**
     * Load defined abilites
     *
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @return void
     */
    protected function loadAbilities()
    {
        $file = $this->app['config']->get('abilities.path');

        if (!file_exists($file) && !$this->app->runningInConsole()) {
            throw new FileNotFoundException(sprintf('File "%s" does not exists.', $file));
        }

        // Required to not throw errors in console.
        if (file_exists($file)) {
            include $file;
        }
    }

    /**
     * Define gates based on loaded abilities.
     *
     * @return void
     */
    protected function defineGates()
    {
        $abilities = $this->app->make(AbilityRegistrar::class);

        foreach ($abilities as $ability) {
            $this->defineGate($ability);
            $this->defineAliasedGates($ability);
        }
    }

    /**
     * Define single gate.
     *
     * @param  \NGT\Laravel\Abilities\Ability  $ability
     * @return void
     */
    protected function defineGate(Ability $ability)
    {
        Gate::define($ability->name(), function ($user) use ($ability) {
            if ($ability->hasCustomCallback()) {
                return $ability->callCustomCallback($user);
            }

            return $user->isAble($ability);
        });
    }

    /**
     * Define gates related to base gate.
     *
     * @param  \NGT\Laravel\Abilities\Ability $ability
     * @return void
     */
    protected function defineAliasedGates(Ability $ability)
    {
        foreach ($ability->aliasedActions() as $related) {
            Gate::define($related, function ($user) use ($ability) {
                return Gate::allows($ability->name());
            });
        }
    }
}
