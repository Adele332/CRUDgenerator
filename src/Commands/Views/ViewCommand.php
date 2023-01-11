<?php

namespace adele332\crudgenerator\Commands\Views;

use Illuminate\Console\Command;
use Illuminate\Support\Str;
use Exception;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\File;

class ViewCommand extends Command
{
    /**
     * The name and signature of the console command.
     * * @var string
     */
    protected $signature = 'make:view
                            {name-of-view : The name of newly created view, example Genres.}
                            {--crud= : Name of the controller to get crud functionality, will be used to create a route, please provide full name.}
                            {--table= : Name of the database table.}
                            {--columns= : Names of 3 columns to be showed in general table, names should be the same as in DB. Example ID,title,date.}';

    /** The console command description. */

    protected $description = 'For creating a new views.';

    /** Execute the console command.  */

    protected $typeOfFields = [
        'bigint' => 'number',
        'binary' => 'textarea',
        'boolean' => 'radio',
        'char' => 'text',
        'date' => 'date',
        'datetime' => 'datetime',
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
        'timestamp' => 'datetime',
        'time' => 'time',
        'varchar' => 'text',
    ];

    public function handle()
    {
        $input = $this->getData();

        $indexTemplate = file_get_contents(__DIR__ .'/Stubs/index.blade.stub');
        $formTemplate = file_get_contents(__DIR__ .'/Stubs/form.blade.stub');
        $showTemplate = file_get_contents(__DIR__ .'/Stubs/show.blade.stub');

        if (empty($input->dbTable)){
            try {
                return throw new Exception("Please use mandatory parameter --table!");
            } catch (Exception $e) {
                echo $e->getMessage();
                exit(1);
            }
        }

        $this->replaceName($input->name,$indexTemplate);
        $this->replaceColumns($input->columns, $indexTemplate);
        $this->replaceNameLowerCase($input->name, $indexTemplate);
        $this->replaceNameLowerCase($input->name, $showTemplate);
        $this->replaceShowBody($input->dbTable, $showTemplate);
        $this->replaceModel($input->dbTable, $showTemplate);
        $this->replaceModel($input->dbTable, $formTemplate);
        $this->replacePluralName($input->dbTable, $formTemplate);
        $this->replaceFormFields($input->dbTable, $formTemplate);
        $this->putContentToFile($input->name, $input->crud, $input->columns, $indexTemplate, $showTemplate, $formTemplate);
    }

    protected function getData()
    {
        $name = trim($this->argument('name-of-view'));
        $crud = trim($this->option('crud'));
        $dbTable = trim($this->option('table'));
        $columns = trim($this->option('columns'));

        return (object) compact(
            'name',
            'crud',
            'dbTable',
            'columns'
        );
    }

    protected function replaceName($name, &$indexTemplate)
    {
        $indexTemplate = str_replace(
            '{{ViewName}}',$name, $indexTemplate);
        return $this;
    }

    protected function replaceModel($dbTable, &$formTemplate)
    {
        $formTemplate = str_replace(
            '{{ModelName}}',Str::singular(strtolower($dbTable)), $formTemplate);
        return $this;
    }

    protected function replacePluralName($dbTable, &$formTemplate)
    {
        $formTemplate = str_replace(
            '{{PluralNameLower}}',Str::plural(strtolower($dbTable)), $formTemplate);
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
        $col1 = explode(',', $columns);
        if(!empty($columns) and count($col1) == 3) {
            for ($x = 0; $x <= count($col1); $x++) {
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

    protected function replaceShowBody($dbTable, &$showTemplate){
        $columns = Schema::getColumnListing($dbTable);
        $rows = [];
        foreach ($columns as $item) {
            $rows[$item] = "<tr>
                            <td>".$item."</td>
                            <td> {{ $".Str::singular($dbTable)."->".$item." }} </td>
                        </tr>";
        }

        if (!empty($rows)) {
            $showTemplate = str_replace(
                '{{bodyShow}}',  implode($rows), $showTemplate);
        } else {
            try {
                return throw new Exception("Provided database table name do not exists in DB!");
            } catch (Exception $e) {
                echo $e->getMessage();
                exit(1);
            }
        }
    }

    protected function replaceFormFields($dbTable, &$formTemplate){

            $columns = Schema::getColumnListing($dbTable);
            $fieldRows = [];
            foreach ($columns as $item) {
                $type = Schema::getColumnType($dbTable, $item);
                $typeToUse = $this->typeOfFields[$type];
                if($item=="id" or $item=="deleted_at" or $item=="created_at"){
                    $fieldRows[$item] ="";
                }else {
                    $fieldRows[$item] = "
            <div class=\"form-group\">
                {!! Form::label('" . $item . "', '" . $item . ": ', ['class' => 'col-sm-3']) !!}
                    <div class=\"col-sm-6\">
                        {!! Form::" . $typeToUse . "('" . $item . "', null, ['class' => 'form-control', 'required' => 'required']) !!}
                    </div>
            </div>
            ";
                }
            }

        if (!empty($fieldRows)) {
            $formTemplate = str_replace(
                '{{inputFields}}',  implode($fieldRows), $formTemplate);
        } else {
            try {
                return throw new Exception("Provided database table name do not exists in DB!");
            } catch (Exception $e) {
                echo $e->getMessage();
                exit(1);
            }
        }
    }

    protected function putContentToFile($name, $crud, $columns, &$indexTemplate, &$showTemplate, &$formTemplate)
    {
        $lower = Str::plural(strtolower($name));

        if(!file_exists($path = base_path('/resources/views/crudViews'))) {
            mkdir($path, 0777, true);
        }

        if(!file_exists($path = base_path('/resources/views/crudViews/'.$lower))) {
            $col1 = explode(',', $columns);
            if (count($col1) == 3) {
                if(!empty($crud)) {
                    mkdir($path, 0777, true);
                    file_put_contents(base_path("/resources/views/crudViews/{$lower}/index.blade.php"), $indexTemplate);
                    file_put_contents(base_path("/resources/views/crudViews/{$lower}/show.blade.php"), $showTemplate);
                    file_put_contents(base_path("/resources/views/crudViews/{$lower}/form.blade.php"), $formTemplate);
                    File::append(base_path('routes/web.php'), "Route::resource('admin/{$lower}', App\Http\Controllers\Admin\\$crud::class)->middleware('crudAuth');\n");
                    $this->info("New views were created! Saved in /resources/views/crudViews/{$lower} directory!");
                }else {
                    try {
                        return throw new Exception("Please use parameter --crud!");
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        exit(1);
                    }
                }
            } else {
                try {
                    return throw new Exception("You must to provide 3 names for parameter columns!");
                } catch (Exception $e) {
                    echo $e->getMessage();
                    exit(1);
                }
            }
        }else {
            try {
                return throw new Exception("The views for $name already exists, see in /resources/views/{$lower} folder!");
            } catch (Exception $e) {
                echo $e->getMessage();
                exit(1);
            }
        }
    }
}
