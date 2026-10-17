<?php

namespace App\Services;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Schema;
use InvalidArgumentException;

class AccountService
{
    public function profile(User $user, array $data): User
    {
        return DB::transaction(function () use ($user, $data) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if (! Hash::check($data['current_password'], $locked->password_hash)) {
                throw new InvalidArgumentException('Mật khẩu hiện tại không đúng.');
            }
            if ($locked->email !== $data['email']) {
                DB::table('password_resets')->where('email', $locked->email)->delete();
            }
            $locked->update(collect($data)->only(['full_name', 'phone', 'email'])->all());
            app(AuditLogService::class)->record($locked, 'profile_updated', 'users', $locked->id);

            return $locked;
        });
    }

    public function changePassword(User $user, string $current, string $password): User
    {
        return DB::transaction(function () use ($user, $current, $password) {
            $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
            if (! Hash::check($current, $locked->password_hash)) {
                throw new InvalidArgumentException('Mật khẩu hiện tại không đúng.');
            }
            $locked->update(['password_hash' => Hash::make($password)]);
            $this->revokeSessions($locked);
            app(AuditLogService::class)->record($locked, 'password_changed', 'users', $locked->id);

            return $locked;
        });
    }

    public function reset(array $data): string
    {
        return DB::transaction(function () use ($data) {
            DB::table('password_resets')->where('email', $data['email'])->lockForUpdate()->first();

            return Password::reset($data + ['status' => 'active'], function (User $user, string $password) {
                $locked = User::whereKey($user->id)->lockForUpdate()->firstOrFail();
                if ($locked->status !== 'active') {
                    throw new InvalidArgumentException('Tài khoản không hoạt động.');
                }
                $locked->update(['password_hash' => Hash::make($password)]);
                $this->revokeSessions($locked);
                app(AuditLogService::class)->record($locked, 'password_reset', 'users', $locked->id);
                event(new PasswordReset($locked));
            });
        }, 3);
    }

    public function revokeSessions(User $user): void
    {
        if (Schema::hasTable('sessions')) {
            DB::table('sessions')->where('user_id', $user->id)->delete();
        }
        DB::table('password_resets')->where('email', $user->email)->delete();
    }
}
