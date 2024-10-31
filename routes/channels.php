<?php

use Illuminate\Support\Facades\Broadcast;

Broadcast::channel('users-updates', function ($user) {
    return auth()->guard('sanctum')->check(); // Solo usuarios autenticados
});
