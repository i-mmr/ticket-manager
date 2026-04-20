<?php

namespace App\Support;

use Illuminate\Support\Str;

class EmailHasher
{
    public static function normalize(string $email): string
    {
        return Str::lower(trim($email));
    }

    public static function make(string $email): string
    {
        return hash_hmac('sha256', self::normalize($email), config('app.key'));
    }
}
