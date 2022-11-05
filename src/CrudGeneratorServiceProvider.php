<?php

namespace adele332\crudgenerator;

use Illuminate\Support\ServiceProvider;

class CrudGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        $commands =
            ['adele332\crudgenerator\Commands\Models\ModelCommand',
                'adele332\crudgenerator\Commands\Controllers\ControllerCommand',
                'adele332\crudgenerator\Commands\Views\ViewCommand'];

        $this->commands($commands);
    }

    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        /*include  __DIR__.'/routes.php';        */

        $this->publishes([

            'mainPath' => base_path('resources/'),

            'pathForRoute' => base_path('routes/web.php'),

            'pathForView' => base_path('resources/views/new')

        ]);

    }
}
