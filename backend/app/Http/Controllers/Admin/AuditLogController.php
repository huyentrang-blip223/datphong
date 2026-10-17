<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
        $logs = AuditLog::with('actor')
            ->when($request->query('entity'), fn($query, $entity) => $query->where('entity', $entity))
            ->when($request->query('action'), fn($query, $action) => $query->where('action', 'like', "%{$action}%"))
            ->latest('id')
            ->paginate(25)
            ->withQueryString();

        return view('admin.audit_logs.index', compact('logs'));
    }
}
