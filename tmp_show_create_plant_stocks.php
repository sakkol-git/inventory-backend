<?php

use Illuminate\Contracts\Console\Kernel;
use Illuminate\Support\Facades\DB;

require __DIR__.'/vendor/autoload.php';
$app = require __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Kernel::class);
$kernel->bootstrap();
$rows = DB::select('SHOW CREATE TABLE plant_stocks');
foreach ($rows as $row) {
    echo $row->{'Create Table'}."\n";
}
