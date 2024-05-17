<?php

namespace App\Providers;

use Filament\FilamentManager;
use Filament\Models\Contracts\HasName;
use Illuminate\Support\ServiceProvider;

class CustomFilamentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot()
    {
        $this->app->extend(FilamentManager::class, function ($manager) {
            $manager->macro('getUserName', function ($user) {
                if ($user instanceof HasName) {
                    return $user->getFilamentName();
                }
                return $user->getAttributeValue('full_name');
            });

            return $manager;
        });
    }
}
