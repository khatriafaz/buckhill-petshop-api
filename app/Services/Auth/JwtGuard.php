<?php

namespace App\Services\Auth;

use App\Models\JwtToken;
use App\Services\JwtService;
use Illuminate\Auth\Events\Login;
use Illuminate\Auth\GuardHelpers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Contracts\Auth\Authenticatable;

class JwtGuard implements Guard
{
    use GuardHelpers;

    public string $name = 'api';

    private JwtService $jwt;

    public function __construct(
        UserProvider $provider
    ) {
        $this->provider = $provider;
        $this->jwt = app(JwtService::class);
    }

    /**
     * Get the currently authenticated user.
     *
     * @return \Illuminate\Contracts\Auth\Authenticatable|null
     */
    public function user()
    {
        if (! is_null($this->user)) {
            return $this->user;
        }

        $user = null;

        if (! $token = request()->bearerToken()) {
            return;
        }

        if (! $token = $this->jwt->validateToken($token)) {
            return;
        }

        $claims = $token->claims();

        if (! JwtToken::isValidToken($claims->get('jti'))) {
            return;
        }

        $uuid = $claims->get('uid');

        if (! empty($uuid)) {
            $user = $this->provider->retrieveByCredentials([
                'uuid' => $uuid,
            ]);
        }

        return $this->user = $user;
    }

    public function attempt(array $credentials = [], $remember = false)
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        // If an implementation of UserInterface was returned, we'll ask the provider
        // to validate the user against the given credentials, and if they are in
        // fact valid we'll log the users into the application and return true.
        if ($this->hasValidCredentials($user, $credentials)) {
            $this->login($user, $remember);

            return true;
        }

        return false;
    }

    /**
     * Determine if the user matches the credentials.
     *
     * @param  mixed  $user
     * @param  array  $credentials
     * @return bool
     */
    protected function hasValidCredentials($user, $credentials)
    {
        $validated = ! is_null($user) && $this->provider->validateCredentials($user, $credentials);

        return $validated;
    }


    public function login(Authenticatable $user, $remember = false)
    {
        // If we have an event dispatcher instance set we will fire an event so that
        // any listeners will hook into the authentication events and run actions
        // based on the login and logout events fired from the guard instances.
        event(new Login($this->name, $user, $remember));

        $this->setUser($user);
    }

    /**
     * Validate a user's credentials.
     *
     * @param  array  $credentials
     * @return bool
     */
    public function validate(array $credentials = [])
    {
        $user = $this->provider->retrieveByCredentials($credentials);

        return $this->hasValidCredentials($user, $credentials);
    }

    public function logout()
    {
        $this->clearPersistentToken();

        $this->user = null;
    }

    public function clearPersistentToken()
    {
        $user = $this->user();

        if (! $token = $this->jwt->validateToken(request()->bearerToken())) {
            return;
        }

        $claims = $token->claims();

        $jwtToken = $user->jwtTokens()->where('unique_id', $claims->get('jti'))->first();

        if ($jwtToken) {
            $jwtToken->delete();
        }
    }
}
