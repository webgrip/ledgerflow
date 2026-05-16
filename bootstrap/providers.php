<?php

use App\Providers\AppServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\HorizonServiceProvider;
use App\Providers\PulseServiceProvider;
use App\Providers\TelescopeServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    HorizonServiceProvider::class,
    PulseServiceProvider::class,
    TelescopeServiceProvider::class,
];
