<?php
// app/Providers/InstaSitesServiceProvider.php
namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;

class InstaSitesServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // points "instasites::" to resources/instasites
        View::addNamespace('instasites', resource_path('instasites'));
    }
}