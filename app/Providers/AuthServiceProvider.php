<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\DataAccessRequest;
use App\Models\DataAccessGrant;
use App\Models\ApiAccessToken;
use App\Models\DataAccessLog;
use App\Policies\DataAccessPolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        DataAccessRequest::class => DataAccessPolicy::class,
        DataAccessGrant::class => DataAccessPolicy::class,
        ApiAccessToken::class => DataAccessPolicy::class,
        DataAccessLog::class => DataAccessPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();
    }
}