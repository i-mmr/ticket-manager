<?php

namespace App\Models;

use App\Support\EmailHasher;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['user_email', 'user_email_hash', 'user_name', 'reason', 'comment'])]
class AccountDeletionFeedback extends Model
{
    protected static function booted(): void
    {
        static::saving(function (AccountDeletionFeedback $feedback) {
            if ($feedback->user_email && (! $feedback->user_email_hash || $feedback->isDirty('user_email'))) {
                $feedback->user_email_hash = EmailHasher::make($feedback->user_email);
            }
        });
    }

    protected function casts(): array
    {
        return [
            'user_email' => 'encrypted',
            'user_name' => 'encrypted',
        ];
    }
}
