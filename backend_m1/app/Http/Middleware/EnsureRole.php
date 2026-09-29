<?php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (!$user || !in_array($user->role, $roles, true)) {
            Log::warning('Truy cap trai phep', [
                'user_id' => $user?->id,
                'role' => $user?->role,
                'path' => $request->path(),
                'ip' => $request->ip(),
            ]);
            abort(403, 'Không đủ quyền truy cập.');
        }
        return $next($request);
    }
}
