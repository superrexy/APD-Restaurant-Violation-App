<?php

namespace App\Providers;

use Dedoc\Scramble\Scramble;
use Dedoc\Scramble\Support\Generator\SecurityScheme;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Scramble::configure()
            ->withDocumentTransformers(function ($openApi) {
                $openApi->secure(SecurityScheme::http('bearer'));
            });

        JsonResponse::macro('json', function ($key = null) {
            $content = json_decode($this->getContent(), true);

            if ($key !== null) {
                return $content[$key] ?? null;
            }

            return $content;
        });
    }
}
