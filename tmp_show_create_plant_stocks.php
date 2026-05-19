<?php
require __DIR__ . '/vendor/autoload.php';
$app = require __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$rows = Illuminate\Support\Facades\DB::select('SHOW CREATE TABLE plant_stocks');
foreach ($rows as $row) {
    echo $row->{'Create Table'} . "\n";
}
