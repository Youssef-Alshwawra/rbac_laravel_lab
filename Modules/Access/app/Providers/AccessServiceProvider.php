<?php

namespace Modules\Access\Providers;

use Nwidart\Modules\Support\ModuleServiceProvider;
use Illuminate\Support\Facades\Gate;
use Modules\Access\Http\Middleware\CheckPermission;

class AccessServiceProvider extends ModuleServiceProvider
{
    /**
     * The name of the module.
     */
    protected string $name = 'Access';

    /**
     * The lowercase version of the module name.
     */
    protected string $nameLower = 'access';

    /**
     * Command classes to register.
     *
     * @var string[]
     */
    // protected array $commands = [];

    /**
     * Provider classes to register.
     *
     * @var string[]
     */
    protected array $providers = [
        EventServiceProvider::class,
        RouteServiceProvider::class,
    ];

    public function boot(): void { 
        parent::boot();
        
        $this->app['router']->aliasMiddleware('permission', CheckPermission::class);

        Gate::before(function ($user, string $ability) {
            return $user->hasPermission($ability) ? true : null;
        });
    }

    /**
     * Define module schedules.
     * 
     * @param $schedule
     */
    // protected function configureSchedules(Schedule $schedule): void
    // {
    //     $schedule->command('inspire')->hourly();
    // }
}
