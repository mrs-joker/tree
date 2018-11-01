<?php

namespace MrsJoker\Tree;

use Illuminate\Support\ServiceProvider;

class TreeServiceProviderLaravel extends ServiceProvider
{

    /**
     * Bootstrap the application events.
     *
     * @return void
     */
    public function boot()
    {
        $this->publishes([
            __DIR__.'/../../config/config.php' => config_path('tree.php')
        ]);

    }

    /**
     * Register the service provider.
     *
     * @return void
     */
    public function register()
    {
        $app = $this->app;

        // merge default config
        $this->mergeConfigFrom(
            __DIR__.'/../../config/config.php',
            'tree'
        );

        // create image
        $app->singleton('tree', function ($app) {
            return new TreeManager($app['config']->get('tree'));
        });

        $app->alias('tree', 'MrsJoker\Tree\TreeManager');
    }

}
