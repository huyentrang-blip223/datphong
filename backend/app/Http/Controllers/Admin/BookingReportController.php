<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\ExportBookingsRequest;
use App\Services\BookingReportService;

class BookingReportController extends Controller
{
    public function __invoke(ExportBookingsRequest $request, BookingReportService $service)
    {
        $filters = $request->validated();

        return response()->streamDownload(function () use ($service, $filters) {
            $stream = fopen('php://output', 'w');
            $service->writeCsv($stream, $filters);
            fclose($stream);
        }, 'bookings-'.now()->format('Ymd').'.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }
}
