<?php

use Illuminate\Support\Facades\Facade;

return [
    'aliases' => Facade::defaultAliases()->merge([
        'Number' => Illuminate\Support\Number::class,
    ])->toArray(),
];
