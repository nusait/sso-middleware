<?php namespace Nusait\NuSSO;

use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Support\ServiceProvider;
use Nusait\Nuldap\NuLdap;

class NuSSOServiceProvider extends ServiceProvider {

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

	/**
	 * Bootstrap the application services.
	 *
	 * @return void
	 */
	public function boot(Guard $auth, ResponseFactory $responseFactory)
	{
        $this->publishes([
            __DIR__ . '/../config/nusso.php' => config_path('nusso.php'),
        ]);

        // make singleton?
        $this->app->bind('Nusait\NuSSO\NuSSO', function($app) use ($auth, $responseFactory) {
            $config = $this->app['config']->get('nusso');
            $config['model'] = $app['config']->get('auth.model');
            $ldap = new NuLdap(
                $app['config']->get('ldap.rdn'),
                $app['config']->get('ldap.password'),
                $app['config']->get('ldap.host'),
                $app['config']->get('ldap.port')
            );
            return new NuSSO($auth, $responseFactory, $ldap, $config);
        });
	}

	/**
	 * Register the application services.
	 *
	 * @return void
	 */
	public function register()
	{
		//
	}

}
