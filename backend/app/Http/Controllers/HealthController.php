<?php
namespace App\Http\Controllers;

use App\Services\PythonDataService;
use Illuminate\Support\Facades\DB;

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

        $python = $pythonDataService->seasonality()['source'] === 'python' ? 'ok' : 'degraded';

        return response()->json([
            'application' => 'ok',
            'database' => $database,
            'python_service' => $python,
        ], $database === 'ok' ? 200 : 503);
    }
}
