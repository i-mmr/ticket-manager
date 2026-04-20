<?php

use App\Support\EmailHasher;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        $this->prepareUsersTable();
        $this->preparePendingRegistrationsTable();
        $this->prepareAccountDeletionFeedbackTable();
    }

    public function down(): void
    {
        //
    }

    private function prepareUsersTable(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        if (! Schema::hasColumn('users', 'email_hash')) {
            Schema::table('users', function ($table) {
                $table->string('email_hash', 64)->nullable()->after('email');
            });
        }

        $this->dropIndexIfExists('users', 'users_email_unique');
        $this->widenTextColumns('users', ['name', 'email']);

        DB::table('users')
            ->select(['id', 'name', 'email', 'email_hash'])
            ->orderBy('id')
            ->each(function ($user) {
                if ($user->email_hash) {
                    return;
                }

                $email = $this->plainValue($user->email);
                $name = $this->plainValue($user->name);

                DB::table('users')->where('id', $user->id)->update([
                    'name' => Crypt::encryptString($name),
                    'email' => Crypt::encryptString(EmailHasher::normalize($email)),
                    'email_hash' => EmailHasher::make($email),
                ]);
            });

        $this->createUniqueIndexIfMissing('users', 'email_hash', 'users_email_hash_unique');
    }

    private function preparePendingRegistrationsTable(): void
    {
        if (! Schema::hasTable('pending_registrations')) {
            return;
        }

        if (! Schema::hasColumn('pending_registrations', 'email_hash')) {
            Schema::table('pending_registrations', function ($table) {
                $table->string('email_hash', 64)->nullable()->after('email');
            });
        }

        $this->dropIndexIfExists('pending_registrations', 'pending_registrations_email_unique');
        $this->widenTextColumns('pending_registrations', ['email']);

        DB::table('pending_registrations')
            ->select(['id', 'email', 'email_hash'])
            ->orderBy('id')
            ->each(function ($pendingRegistration) {
                if ($pendingRegistration->email_hash) {
                    return;
                }

                $email = $this->plainValue($pendingRegistration->email);

                DB::table('pending_registrations')->where('id', $pendingRegistration->id)->update([
                    'email' => Crypt::encryptString(EmailHasher::normalize($email)),
                    'email_hash' => EmailHasher::make($email),
                ]);
            });

        $this->createUniqueIndexIfMissing(
            'pending_registrations',
            'email_hash',
            'pending_registrations_email_hash_unique',
        );
    }

    private function prepareAccountDeletionFeedbackTable(): void
    {
        if (! Schema::hasTable('account_deletion_feedback')) {
            return;
        }

        if (! Schema::hasColumn('account_deletion_feedback', 'user_email_hash')) {
            Schema::table('account_deletion_feedback', function ($table) {
                $table->string('user_email_hash', 64)->nullable()->after('user_email');
            });
        }

        $this->widenTextColumns('account_deletion_feedback', ['user_email', 'user_name']);

        DB::table('account_deletion_feedback')
            ->select(['id', 'user_email', 'user_email_hash', 'user_name'])
            ->orderBy('id')
            ->each(function ($feedback) {
                if ($feedback->user_email_hash) {
                    return;
                }

                $email = $this->plainValue($feedback->user_email);
                $name = $this->plainValue($feedback->user_name);

                DB::table('account_deletion_feedback')->where('id', $feedback->id)->update([
                    'user_email' => Crypt::encryptString(EmailHasher::normalize($email)),
                    'user_email_hash' => EmailHasher::make($email),
                    'user_name' => Crypt::encryptString($name),
                ]);
            });

        $this->createIndexIfMissing(
            'account_deletion_feedback',
            'user_email_hash',
            'account_deletion_feedback_user_email_hash_index',
        );
    }

    private function plainValue(?string $value): string
    {
        if ($value === null) {
            return '';
        }

        try {
            return Crypt::decryptString($value);
        } catch (Throwable) {
            return $value;
        }
    }

    private function widenTextColumns(string $table, array $columns): void
    {
        if (DB::getDriverName() !== 'mysql') {
            return;
        }

        foreach ($columns as $column) {
            DB::statement("ALTER TABLE `{$table}` MODIFY `{$column}` TEXT NOT NULL");
        }
    }

    private function dropIndexIfExists(string $table, string $index): void
    {
        if (! $this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function ($blueprint) use ($index) {
            $blueprint->dropIndex($index);
        });
    }

    private function createUniqueIndexIfMissing(string $table, string $column, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function ($blueprint) use ($column, $index) {
            $blueprint->unique($column, $index);
        });
    }

    private function createIndexIfMissing(string $table, string $column, string $index): void
    {
        if ($this->indexExists($table, $index)) {
            return;
        }

        Schema::table($table, function ($blueprint) use ($column, $index) {
            $blueprint->index($column, $index);
        });
    }

    private function indexExists(string $table, string $index): bool
    {
        return collect(Schema::getIndexes($table))->contains(
            fn (array $existingIndex) => $existingIndex['name'] === $index,
        );
    }
};
