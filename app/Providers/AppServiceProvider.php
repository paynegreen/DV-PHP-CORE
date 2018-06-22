<?php

namespace App\Providers;

use Blade;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
         Blade::directive(
             'datetime',
                function ($expression) {
                    return "<?php echo with{$expression}->format('m/d/Y H:i'); ?>";
                }
         );
        
        // Force SSL in production
        if ($this->app->environment() != 'development') {
            URL::forceScheme('https');
        }
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
}
