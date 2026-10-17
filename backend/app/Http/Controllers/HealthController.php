<?php
namespace App\Http\Controllers;

use App\Services\PythonDataService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class HealthController extends Controller
{
    public function __invoke(PythonDataService $pythonDataService)
    {
        $database = 'ok';
        try {
            DB::select('select 1');
        } catch (\Throwable $exception) {
            $database = 'error';
        }

        $python = $pythonDataService->health();

        return response()->json([
            'application' => 'ok',
            'database' => $database,
            'python' => $python,
            'timestamp' => Carbon::now()->toIso8601String(),
        ], $database === 'ok' ? 200 : 503);
    }
}
