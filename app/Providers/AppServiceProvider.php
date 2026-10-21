<?php

namespace App\Providers;

use App\Models\Event;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Illuminate\View\View as ViewInstance;

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
        Model::preventLazyLoading(! $this->app->isProduction());

        Route::resourceVerbs(['create' => 'neu', 'edit' => 'bearbeiten']);

        // Sidebar navigation of the web app: all events of the signed-in host.
        View::composer('layouts.app', function (ViewInstance $view) {
            $user = auth()->user();

            $navEvents = $user
                ? Event::query()->whereBelongsTo($user)->orderBy('date')->get(['id', 'title', 'date'])
                : collect();

            $view->with(compact('navEvents'));
        });
    }
}
