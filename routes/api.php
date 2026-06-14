<?php

use Illuminate\Support\Facades\Route;

Route::get('/status', function (): array {
    return [
        'status' => 'ok',
        'framework' => 'Laravel',
        'message' => 'Servidor web funcionando',
    ];
});
