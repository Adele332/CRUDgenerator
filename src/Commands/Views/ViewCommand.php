<?php

namespace adele332\crudgenerator\Commands\Views;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Exception;

class ViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     * * @var string
     */

    protected $signature = 'make:view
                            {name-of-view : The name of newly created view. example Genres}
                            {--crud= : Name of created crud for functions, controller will be used to create a route, privalomas.}
                            {--columns= : Name 3 columns to be showed. example ID,title,date, names should be the same as in DB, privalomas.}';

    /** The console command description. */

    protected $description = 'For creating a new views.';

    /** Execute the console command.  */

    protected $typeOfFields = [
        'bigint' => 'number',
        'binary' => 'textarea',
        'boolean' => 'radio',
        'char' => 'text',
        'date' => 'date',
        'datetime' => 'datetime-local',
        'decimal' => 'number',
        'double' => 'number',
        'email' => 'email',
        'enum' => 'select',
        'file' => 'file',
        'float' => 'number',
        'integer' => 'number',
        'json' => 'textarea',
        'jsonb' => 'textarea',
        'longtext' => 'textarea',
        'mediumint' => 'number',
        'mediumtext' => 'textarea',
        'number' => 'number',
        'password' => 'password',
        'radio' => 'radio',
        'string' => 'text',
        'smallint' => 'number',
        'select' => 'select',
        'text' => 'textarea',
        'tinyint' => 'number',
        'timestamp' => 'datetime-local',
        'time' => 'time',
        'varchar' => 'text',
    ];

    public function handle()
    {
        $input = $this->getData();

        $indexTemplate = file_get_contents(__DIR__ .'/Stubs/index.blade.stub');
        //$editTemplate = file_get_contents(__DIR__ .'/Stubs/edit.blade.stub');
        //$showTemplate = file_get_contents(__DIR__ .'/Stubs/show.blade.stub');

        $this->replaceName($input->name,$indexTemplate);
        $this->replaceColumns($input->columns, $indexTemplate);
        $this->replaceNameLowerCase($input->name, $indexTemplate);
        $this->putContentToFile($input->name, $input->crud, $input->columns,$indexTemplate);
    }

    protected function getData()
    {
        $name = trim($this->argument('name-of-view'));
        $crud = trim($this->option('crud'));
        $columns = trim($this->option('columns'));

        return (object) compact(
            'name',
            'crud',
            'columns'
        );
    }

    protected function replaceName($name, &$indexTemplate)
    {
        $indexTemplate = str_replace(
            '{{ViewName}}',$name, $indexTemplate);
        return $this;
    }

    protected function replaceNameLowerCase($name, &$indexTemplate)
    {
        $indexTemplate = str_replace(
            '{{NameLowerCase}}',Str::plural(strtolower($name)), $indexTemplate);
        return $this;
    }

    protected function replaceColumns($columns, &$indexTemplate)
    {
        //nelabai gerai tikrint dviejose vietose ta pati, kiek parametre columns yra reiksmiu
        $col1 = explode(',', $columns);
        if(!empty($columns) and count($col1) == 3) {
            for ($x = 0; $x <= 3; $x++) {
                $col = explode(',', $columns);
                $indexTemplate = str_replace('{{Col1}}', strtoupper($col[0]), $indexTemplate);
                $indexTemplate = str_replace('{{Col2}}', strtoupper($col[1]), $indexTemplate);
                $indexTemplate = str_replace('{{Col3}}', strtoupper($col[2]), $indexTemplate);
                $indexTemplate = str_replace('{{col1}}', strtolower($col[0]), $indexTemplate);
                $indexTemplate = str_replace('{{col2}}', strtolower($col[1]), $indexTemplate);
                $indexTemplate = str_replace('{{col3}}', strtolower($col[2]), $indexTemplate);
            }
        }
    }

    protected function putContentToFile($name, $crud, $columns, &$indexTemplate)
    {
        $lower = Str::plural(strtolower($name));

        if(!file_exists($path = base_path('/resources/views/'.$lower))) {
            mkdir($path, 0777, true);
            $col1 = explode(',', $columns);
            if (count($col1) == 3) {
                file_put_contents(base_path("/resources/views/{$lower}/index.blade.php"), $indexTemplate);
                //file_put_contents(base_path("/resources/views/show.blade.php"), $showTemplate);
                //file_put_contents(base_path("/resources/views/form.blade.php"), $formTemplate);
                //file_put_contents(base_path("/resources/views/edit.blade.php"), $editTemplate);
                $this->info("New views were created! Saved in /resources/views/{$lower} directory!");
            } else {
                try {
                    return throw new Exception("You must to provide 3 names for parameter columns!");
                } catch (Exception $e) {
                    echo $e->getMessage();
                }
            }

            File::append(base_path('routes/web.php'), "Route::get('/admin/{$lower}', [App\Http\Controllers\Admin\\$crud::class, 'index']);\n");
            File::append(base_path('routes/web.php'), "Route::get('/admin/{$lower}', [App\Http\Controllers\Admin\\$crud::class, 'show']);\n");
            File::append(base_path('routes/web.php'), "Route::get('/admin/{$lower}', [App\Http\Controllers\Admin\\$crud::class, 'form']);\n");
        }else {
            try {
                return throw new Exception("The views for $name already exists, see in /resources/views/{$lower} folder!");
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}

//php artisan make:view Books --crud="BooksController" --columns="id,title,date"
