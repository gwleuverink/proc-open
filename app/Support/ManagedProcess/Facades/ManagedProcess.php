<?php

namespace App\Support\ManagedProcess\Facades;

use App\Support\ManagedProcess\Factory;
use Closure;
use Illuminate\Support\Facades\Facade;

class ManagedProcess extends Facade
{
    protected static function getFacadeAccessor()
    {
        return Factory::class;
    }

    public static function fake(Closure|array|null $callback = null)
    {
        return tap(static::getFacadeRoot(), function ($fake) use ($callback) {
            static::swap($fake->fake($callback));
        });
    }
}
