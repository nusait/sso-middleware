# SSO Middleware
A middleware package for Laravel 5.1 to build an authenticated user using NU WebSSO.

## Installation via Composer
```
composer require "nusait/sso-middleware~1.0"
```
or manually
```json
require: {
  "nusait/sso-middleware": "~1.0"
}
```
Then update composer
```
composer update
```
## Laravel Installation

Add the service provider to `app/config/app.php`
```
Nusait\NuSSO\NuSSOServiceProvider::class,
```
Then add it to your middleware in your `app/Http/Kernel.php`
```
protected $routeMiddleware = [
  'nusso' => \Nusait\NuSSO\NuSSO::class,
];
```
To publish the config file, run `php artisan vendor:publish` in your root folder. This will publish a config file to `config/nusso.php`.

## Configuration
The configuration file published `nusso.php` contains some configuration settings:

```php
return [
    'autoCreate' => false,
    'netidColumn' => 'netid',
    'firstNameColumn' => 'first_name',
    'lastNameColumn' => 'last_name',
    'emailColumn' => 'email',
    'serverVariable' => 'REMOTE_USER'
];
```
* autoCreate - indicated whether to create a new user if one is not found in your database
* netidColumn - the netid column in your users table
* firstNameColumn - the first name column in your users table
* lastNameColumn - the last name column in your users table
* emailColumn - the email column in your users table
* serverVariable - the keyname in the `$_SERVER` variable that indicates the netid for the user

## Usage
As a route middleware:
```php
Route::get('/', ['middleware' => 'nusso', 'uses' =>  function () {
    return view('welcome');
}]);
```