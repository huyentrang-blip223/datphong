<?php

namespace App\Services;

use App\Models\Homestay;
use App\Models\HomestayVerification;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;

class HomestaySubmissionService
{
    public function save(User $host, array $data, ?Homestay $homestay = null): Homestay
    {
        abort_unless($host->role === 'host' && (! $homestay || (int) $homestay->owner_id === (int) $host->id), 403);
        $document = $data['document'] ?? null;
        $path = $document ? $document->store('verifications', 'local') : null;
        try {
            return DB::transaction(function () use ($host, $data, $homestay, $path) {
                unset($data['document']);
                $type = $data['document_type'];
                unset($data['document_type']);
                $locked = $homestay ? Homestay::whereKey($homestay->id)->lockForUpdate()->firstOrFail() : new Homestay();
                $locked->fill($data + ['owner_id' => $host->id]);
                if ($locked->status === 'suspended') {
                    throw new InvalidArgumentException('Cơ sở bị đình chỉ; vui lòng liên hệ quản trị viên.');
                }
                $previous = HomestayVerification::where('homestay_id', $locked->id)->latest('id')->first();
                if (! $path && ! $previous) {
                    throw new InvalidArgumentException('Cần bổ sung giấy tờ xác minh trước khi gửi duyệt.');
                }
                $locked->status = 'pending';
                $locked->save();
                HomestayVerification::where('homestay_id', $locked->id)->where('status', 'pending')
                    ->update(['status' => 'rejected', 'note' => 'Đã thay bằng hồ sơ mới', 'reviewed_at' => now()]);
                HomestayVerification::create(['homestay_id' => $locked->id, 'document_type' => $type,
                    'document_ref' => $path ?: $previous->document_ref, 'submitted_at' => now(), 'status' => 'pending']);
                app(AuditLogService::class)->record($host, 'homestay_submitted', 'homestays', $locked->id);

                return $locked;
            });
        } catch (\Throwable $e) {
            if ($path) {
                Storage::disk('local')->delete($path);
            }
            throw $e;
        }
    }

    public function review(HomestayVerification $verification, User $admin, array $data): void
    {
        abort_unless($admin->role === 'admin', 403);
        DB::transaction(function () use ($verification, $admin, $data) {
            $homestay = Homestay::whereKey($verification->homestay_id)->lockForUpdate()->firstOrFail();
            $locked = HomestayVerification::whereKey($verification->id)->lockForUpdate()->firstOrFail();
            if ($locked->status !== 'pending' || $homestay->status !== 'pending') {
                throw new InvalidArgumentException('Hồ sơ không còn chờ duyệt.');
            }
            $locked->update($data + ['reviewed_by' => $admin->id, 'reviewed_at' => now()]);
            $homestay->update(['status' => $data['status']]);
            app(AuditLogService::class)->record($admin, 'homestay_reviewed', 'homestays', $homestay->id, null, $data);
            app(NotificationService::class)->send((int) $homestay->owner_id, 'homestay_reviewed', 'Kết quả duyệt homestay', $data['status'].': '.($data['note'] ?? ''), 'homestay', $homestay->id);
        });
    }
}
