<?php

namespace App\Models;

use App\Support\EmailHasher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['email', 'email_hash', 'token', 'expires_at'])]
class PendingRegistration extends Model
{
    protected static function booted(): void
    {
        static::saving(function (PendingRegistration $pendingRegistration) {
            if ($pendingRegistration->email && (! $pendingRegistration->email_hash || $pendingRegistration->isDirty('email'))) {
                $pendingRegistration->email_hash = EmailHasher::make($pendingRegistration->email);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'email' => 'encrypted',
            'expires_at' => 'datetime',
        ];
    }
}
