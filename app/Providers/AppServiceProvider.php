<?php

namespace App\Providers;

use App\Models\Permissions\Permission;
use App\Models\Permissions\Role;
use App\Models\User;
use App\Policies\PermissionPolicy;
use App\Policies\RolePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Permission::class => PermissionPolicy::class,
        Role::class => RolePolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Give Super Admin and Developer full access to everything
        Gate::before(function (User $user, string $ability) {
            if ($user->hasAnyRole(['Super Admin', 'Developer'])) {
                return true;
            }
        });
    }
}
