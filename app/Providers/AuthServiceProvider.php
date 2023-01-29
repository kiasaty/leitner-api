<?php

namespace App\Providers;

use App\Models\User;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\ExpiredException;
// use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

        Auth::viaRequest('jwt', function (Request $request) {
            if ($token = $request->bearerToken()) {
                $key = config('app.key');

                try {
                    $payload = JWT::decode($token, $key, ['HS256']);
                } catch (ExpiredException $e) {
                    abort(401, $e->getMessage());
                } catch (Exception $e) {
                    return null;
                }

                $userID = $payload->sub;

                return User::find($userID);
            }
        });
    }
}
