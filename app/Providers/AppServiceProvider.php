<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\OrganisationStepService;
use App\Services\PDFService;
use App\Services\DocumentGenerationService;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->app->singleton(OrganisationStepService::class, function () {
            return new OrganisationStepService();
        });

        $this->app->singleton(PDFService::class, function ($app) {
            return new PDFService(
                $app->make(DocumentGenerationService::class)
            );
        });
    }

    public function boot(): void
    {
        //
    }
}
