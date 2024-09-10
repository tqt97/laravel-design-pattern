<?php

namespace App\Providers;

use App\Events\ProductCreated;
use App\Listeners\SendProductCreatedNotification;
use App\Repositories\Products\ProductRepository;
use App\Repositories\Products\ProductRepositoryInterface;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        /*
        Register the ProductRepositoryInterface with its implementation ProductRepository in the IoC container
        This will allow us to inject the ProductRepositoryInterface in the ProductController
        */
        $this->app->bind(
            ProductRepositoryInterface::class,
            ProductRepository::class
        );
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Event::listen(
            ProductCreated::class,
            SendProductCreatedNotification::class,
        );
    }
}
