<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Route; // Tambahkan ini

class AppServiceProvider extends ServiceProvider
{
    // ... existing code ...

    public function boot(): void
    {
        Route::middleware('api')
            ->prefix('api')
                ->group(base_path('routes/api.php'));
    }

    }
