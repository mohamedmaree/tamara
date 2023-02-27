<?php
namespace Maree\Tamara;

use Illuminate\Support\ServiceProvider;

class TamaraServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $this->publishes([
            __DIR__.'/config/tamara.php' => config_path('tamara.php'),
        ],'tamara');
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        $this->mergeConfigFrom(
            __DIR__.'/config/tamara.php', 'tamara'
        );
    }
}
