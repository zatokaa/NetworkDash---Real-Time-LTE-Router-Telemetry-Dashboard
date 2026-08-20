<?php

namespace App\Auth;

use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Support\Facades\Hash;

class JsonUserProvider implements UserProvider
{
    public function retrieveById($identifier): ?Authenticatable
    {
        return User::find($identifier);
    }

    public function retrieveByToken($identifier, $token): ?Authenticatable
    {
        $user = User::find($identifier);
        return ($user && $user->getRememberToken() === $token) ? $user : null;
    }

    public function updateRememberToken(Authenticatable $user, $token): void
    {
        $user->setRememberToken($token);
    }

    public function retrieveByCredentials(array $credentials): ?Authenticatable
    {
        if (empty($credentials) || ! isset($credentials['email'])) {
            return null;
        }

        return User::findByEmail($credentials['email']);
    }

    public function validateCredentials(Authenticatable $user, array $credentials): bool
    {
        $plain = $credentials['password'] ?? '';
        return Hash::check($plain, $user->getAuthPassword());
    }

    public function rehashPasswordIfRequired(Authenticatable $user, array $credentials, bool $force = false): void
    {
        // No-op for JSON storage
    }
}
