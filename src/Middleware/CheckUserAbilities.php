<?php

namespace NGT\Laravel\Abilities\Middleware;

use Closure;
use NGT\Laravel\Abilities\Exceptions\GateNotExists;
use Illuminate\Auth\Access\AuthorizationException;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Routing\Router;
use Illuminate\Support\Arr;

class CheckUserAbilities
{
    public $gate;

    public $router;

    public function __construct(Gate $gate, Router $router)
    {
        $this->gate   = $gate;
        $this->router = $router;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $this->checkAbility();

        return $next($request);
    }

    protected function checkAbility()
    {
        if ($ability = $this->getAbility()) {
            if (!$this->gate->has($ability)) {
                throw new GateNotExists($ability);
            }

            if (!$this->gate->allows($ability)) {
                throw new AuthorizationException('This action is unauthorized.');
            }
        }
    }

    protected function getAbility()
    {
        $route = $this->router->getCurrentRoute();

        /**
         * Action is closure and has no controller.
         * @see \Illuminate\Routing\Route@isControllerAction
         */
        if (!is_string($route->action['uses'])) {
            return;
        }

        $controller = $route->getController();

        if (!isset($controller->actionAbilities)) {
            return;
        }

        return Arr::get($controller->actionAbilities, $route->getActionMethod());
    }
}
