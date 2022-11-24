<?php

namespace adele332\crudgenerator\Commands\Main;

use Exception;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Facades\File;

class MainViewCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     * * @var string
     */

    protected $signature = 'make:main
                            {name : The name of main layout (best to provide your DB name).}';

    /** The console command description. */

    protected $description = 'For creating a main View, to use CRUD';

    /** Execute the console command.  */

    protected function getStub()
    {
        return file_get_contents(__DIR__ .'/Stubs/main.blade.stub');
    }

    public function handle()
    {
        $input = $this->getData();

        $mainTemplate = $this->getStub();

        $this->replaceName($input->name,$mainTemplate);
        $this->putContentToFile($mainTemplate);
    }

    protected function getData()
    {
        $name = trim($this->argument('name'));

        return (object) compact(
            'name'
        );
    }

    protected function replaceName($name, &$mainTemplate)
    {
        $mainTemplate = str_replace(
            '{{NameOfDB}}',$name, $mainTemplate);
        return $this;
    }

    protected function putContentToFile( $mainTemplate)
    {
        $extends = file_get_contents(__DIR__ .'/Stubs/extendMain.blade.stub');

        if(file_exists(base_path("/resources/views/layout.blade.php")) or file_exists(base_path("/resources/views/extendLayout.blade.php")))  {
            try {
                return throw new Exception("One of files (layout.blade.php or extendLayout.blade.php) already exists! Please check /resources/views!");
            } catch(Exception $e) {
                echo $e->getMessage();
            }

        }else{
            file_put_contents(base_path("/resources/views/layout.blade.php"), $mainTemplate);
            file_put_contents(base_path("/resources/views/extendLayout.blade.php"), $extends);

            //Parasyti salyga kad tikrintu ar toks route jau pridetas pries pridedant

            File::append(base_path('routes/web.php'), "Route::get('/admin', function () {
            return view('extendLayout');
            });\n");
            $this->info("New route was added to routes/web.php file!");
            $this->info("You're CRUD app is ready!");
            $this->info("You can view it no http://127.0.0.1:8000/admin");
            $this->info("Do not forget to run 'php artisan serve' command first!");
        }
    }
}
