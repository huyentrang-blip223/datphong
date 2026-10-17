<?php

if (PHP_SAPI !== 'cli') {
    http_response_code(404);
    exit;
}

// Used only as a subprocess of db-maintenance.py; never log this output.
require __DIR__.'/../backend/vendor/autoload.php';
$app = require __DIR__.'/../backend/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
echo json_encode(config('database.connections.'.config('database.default')));
