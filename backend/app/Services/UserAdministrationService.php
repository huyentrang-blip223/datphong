<?php

namespace App\Services;

use App\Models\ExperienceBooking;
use App\Models\LocalProduct;
use App\Models\ProductOrder;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use InvalidArgumentException;

class UserAdministrationService
{
    public function save(User $admin, array $data, ?User $target = null): User
    {
        abort_unless($admin->role === 'admin' && $admin->status === 'active', 403);

        return DB::transaction(function () use ($admin, $data, $target) {
            $administrators = User::where('role', 'admin')->where('status', 'active')->orderBy('id')->lockForUpdate()->get();
            $user = $target ? User::whereKey($target->id)->lockForUpdate()->firstOrFail() : new User();
            $before = $user->exists ? $user->only(['email', 'role', 'status', 'full_name', 'phone']) : null;
            $roleChanges = $user->exists && $user->role !== $data['role'];
            if ($user->exists && (int) $user->id === (int) $admin->id && ($roleChanges || $data['status'] !== 'active')) {
                throw new InvalidArgumentException('Không được tự khóa hoặc tự đổi vai trò.');
            }
            if ($user->role === 'admin' && $user->status === 'active' && ($roleChanges || $data['status'] !== 'active') && $administrators->count() <= 1) {
                throw new InvalidArgumentException('Phải giữ ít nhất một quản trị viên hoạt động.');
            }
            if ($roleChanges && ($user->homestays()->exists() || LocalProduct::where('seller_id', $user->id)->exists()
                || $user->bookings()->exists() || ProductOrder::where('buyer_id', $user->id)->exists() || ExperienceBooking::where('guest_id', $user->id)->exists())) {
                throw new InvalidArgumentException('Tài khoản đang có dữ liệu nghiệp vụ; cần bàn giao trước khi đổi vai trò. Có thể khóa tài khoản.');
            }
            $password = $data['password'] ?? null;
            unset($data['password'], $data['password_confirmation']);
            if ($user->exists) {
                app(AccountService::class)->revokeSessions($user);
            }
            $user->fill($data);
            if ($password) {
                $user->password_hash = Hash::make($password);
            }
            $user->save();
            app(AuditLogService::class)->record($admin, 'user_managed', 'users', $user->id, $before, $user->only(['email', 'role', 'status', 'full_name', 'phone']));

            return $user;
        }, 3);
    }
}
