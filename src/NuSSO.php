<?php namespace Nusait\NuSSO;

use Closure;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Routing\Middleware;
use Illuminate\Contracts\Routing\ResponseFactory;
use Nusait\Nuldap\NuLdap;

class NuSSO implements Middleware
{

    protected $auth;
    protected $responseFactory;
    protected $ldap;
    protected $config;

    public function __construct(Guard $auth, ResponseFactory $responseFactory, NuLdap $ldap, $config)
    {
        $this->auth = $auth;
        $this->responseFactory = $responseFactory;
        $this->ldap = $ldap;
        $this->config = $config;
    }

    public function handle($request, Closure $next)
    {
        $serverVariable = $this->config['serverVariable'];
        $remoteUser = isset($_SERVER[$serverVariable]) ? $_SERVER[$serverVariable] : null;
        $netidKey = $this->config['netidColumn'];
        if (is_null($remoteUser)) {
            return $this->responseFactory->make('Unauthorized - User has not been authenticated', 401, []);
        }
        // make sure the SSO user and Auth user are in sync
        if($this->auth->check() and $remoteUser != $this->auth->user()->$netidKey) {
            $this->auth->logout();
        }
        if ($this->auth->guest()) {
            $netid = trim(strtolower($remoteUser));
            $query = $this->createModel()->newQuery()->where($this->config['netidColumn'], $netid);
            $user = $query->first();

            // build user if user not found in database and autoCreate is true
            if (is_null($user) and $this->config['autoCreate']) {
                $user = $this->buildNewUser($netid);
            }
            // user not found in ldap
            if(is_null($user)) {
                return $this->responseFactory->make('Unauthorized - User could not be found', 401, []);
            }
            $this->auth->login($user);
        }

        return $next($request);
    }

    private function createModel()
    {
        $class = '\\' . ltrim($this->config['model'], '\\');

        return new $class;
    }

    private function findUser($netid)
    {
        $metadata = $this->ldap->searchNetid($netid);

        return $this->ldap->parseUser($metadata);
    }

    private function buildNewUser($netid)
    {
        $ldapUser = $this->findUser($netid);
        if(is_null($ldapUser)) {
            return null;
        }

        $user = $this->createModel();
        $firstNameKey = $this->config['firstNameColumn'];
        $lastNameKey = $this->config['lastNameColumn'];
        $emailKey = $this->config['emailColumn'];
        $netidKey = $this->config['netidColumn'];

        $user->$firstNameKey = $ldapUser['first_name'];
        $user->$lastNameKey = $ldapUser['last_name'];
        $user->$netidKey = trim(strtolower($ldapUser['netid']));
        $user->$emailKey = $ldapUser['email'];

        $user->save();

        return $user;
    }

}