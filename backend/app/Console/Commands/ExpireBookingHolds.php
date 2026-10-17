<?php

namespace App\Console\Commands;

use App\Services\BookingLifecycleService;
use Illuminate\Console\Command;

class ExpireBookingHolds extends Command
{
    protected $signature = 'bookings:expire';

    protected $description = 'Release expired room holds safely';

    public function handle(BookingLifecycleService $service): int
    {
        $this->info('Expired holds: '.$service->expireDue());

        return self::SUCCESS;
    }
}
