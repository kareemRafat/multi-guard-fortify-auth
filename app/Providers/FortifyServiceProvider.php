<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Laravel\Fortify\Fortify;
use App\Actions\Fortify\CreateNewUser;
use Illuminate\Support\ServiceProvider;
use Illuminate\Cache\RateLimiting\Limit;
use App\Actions\Fortify\ResetUserPassword;
use App\Actions\Fortify\UpdateUserPassword;
use Illuminate\Support\Facades\RateLimiter;
use Laravel\Fortify\Contracts\LoginResponse;
use Laravel\Fortify\Contracts\LogoutResponse;
use App\Actions\Fortify\UpdateUserProfileInformation;

class FortifyServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        // isAdminRoute is a helper function in app helper
        if(isAdminRoute()) {
            config([
                'fortify.guard' => 'admin',
                'fortify.prefix' => 'admin',
                'fortify.home' => 'admin/home'
            ]);
        }

        $this->app->instance(LogoutResponse::class, new class implements LogoutResponse {
            public function toResponse($request)
            {
                return isAdminRoute() ?  redirect('/admin/login') : redirect('/login');
            }
        });

        $this->app->instance(LoginResponse::class, new class implements LoginResponse {
            public function toResponse($request)
            {
                return isAdminRoute() ?  redirect('/admin/home') : redirect('/home');
            }
        });
    }

    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        Fortify::createUsersUsing(CreateNewUser::class);
        Fortify::updateUserProfileInformationUsing(UpdateUserProfileInformation::class);
        Fortify::updateUserPasswordsUsing(UpdateUserPassword::class);
        Fortify::resetUserPasswordsUsing(ResetUserPassword::class);

        RateLimiter::for('login', function (Request $request) {
            $email = (string) $request->email;

            return Limit::perMinute(5)->by($email.$request->ip());
        });

        RateLimiter::for('two-factor', function (Request $request) {
            return Limit::perMinute(5)->by($request->session()->get('login.id'));
        });

        isAdminRoute() ? Fortify::viewPrefix('auth.') : Fortify::viewPrefix('auth-users.');

        // Fortify::loginView(function () {
        //     return isAdminRoute() ?  view('auth.login') : view('auth-users.login');
        // });

        // Fortify::registerView(function () {
        //     return isAdminRoute() ?  view('auth.register') : view('auth-users.register');
        // });
    }
}
