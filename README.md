<p align="center"><a href="https://laravel.com" target="_blank"><img src="https://raw.githubusercontent.com/laravel/art/master/logo-lockup/5%20SVG/2%20CMYK/1%20Full%20Color/laravel-logolockup-cmyk-red.svg" width="400" alt="Laravel Logo"></a></p>

<p align="center">
    <h1>Fortify With Multi guards</h1>  
</p>


Multi guard with laravel Forify

**1** - **get views blade structures from larave/ui**

` `and make to folder in views  

a - auth  ->  for admin authentications 

b - auth-user  -> for user authentications  

\- if you want to work with you own blade files get the action form laravel/ui blade for both register and login forms

**2** - **setup laravel Fortify and add it to AppServiceProviders** 

in config/app.php 

**3** - **make Admin model** and let it extends **Authenticatable**

use Illuminate\Foundation\Auth\User as Authenticatable

copy every thing in User model fillable and etc

**4** - **make admin guard**  in **config/auth.php**
```php

'guards' => [

    'admin' => [

    'driver' => 'session',

    'provider' => 'admins',
    
],

'providers' => [

    'admins' => [
    
    'driver' => 'eloquent',

    'model' => App\Models\Admin::class,

],
```
**5** - in **FortifyServiceProviders**

**Register method** 
```php

// **isAdminRoute()  is a helper function in app helper**

if(isAdminRoute()) {

    config([

        'fortify.guard' => 'admin',
        
        'fortify.prefix' => 'admin',
        
        'fortify.home' => 'admin/home'

    ]);

}
```

**#customizing-authentication-redirects**

<https://laravel.com/docs/9.x/fortify#customizing-authentication-redirects>

```php

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
```
**boot method**

add 
```php
**isAdminRoute() ? Fortify::viewPrefix('auth.') : Fortify::viewPrefix('auth-users.');**
```
`	`**or**
```php
Fortify::loginView(function () {

    return isAdminRoute() ?  view('auth.login') : view('auth-users.login');

});

Fortify::registerView(function () {

    return isAdminRoute() ?  view('auth.register') : view('auth-users.register');

});
```
**6** - in **CreateNewUser**   --   namespace App\Actions\Fortify

**in password validation rules** 
```php
isAdminRoute() ? Rule::unique(Admin::class) : Rule::unique(User::class),

**add before user create**

if(isAdminRoute()){

    return Admin::create([

        'name' => $input['name'],

        'email' => $input['email'],

        'password' => Hash::make($input['password']),

    ]);

}
```
**7 - fix** : when you try to access admin/login when admin is logged in** it redirect you to normal /home route 

we need to fix **guest middleware** and modify **handle** method in 

**namespace App\Http\Middleware\RedirectIfAuthenticated;**

```php
foreach ($guards as $guard) {

    if (Auth::guard($guard)->check()) {

        **if($guard == 'admin') {**

            **return redirect('admin/home');**

        **}**

        return redirect(RouteServiceProvider::HOME);

    }

}

```
helpers.php file mentioned 
```php
    function isAdminRoute(){
        return request()->is('admin/*') || request()->is('admin') ? true : false ;
    }
```
