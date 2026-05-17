<?php

namespace App\Support;

class RegistrationTokenHasher
{
    public static function make(string $token): string
    {
        return hash_hmac('sha256', $token, config('app.key'));
    }
}
