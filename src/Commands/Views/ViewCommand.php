<?php

namespace adele332\crudgenerator\Commands\Views;

use Illuminate\Console\Command;
use Illuminate\Console\GeneratorCommand;

class ViewCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     * * @var string
     */

    protected $signature = 'make:view
                            {name-of-view : The name of newly created view.}
                            {--crud= : Name of created crud for functions.}
                            {--fields-for-view= : Fields that will be used for form in newly created views.}
                            {--file-path= : The place where the views will be saved.}';

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

    protected function getStub()
    {
        // return __DIR__ . '/Stubs/Model.stub';
        return file_get_contents(__DIR__ .'/Stubs/Model.stub');
    }

    public function handle()
    {
        $input = $this->getData();

        //$filePlace = $this->getPath($input->name);
        //if($this->alreadyExists($filePlace)) return false;

        $modelTemplate = $this->getStub();

        $this->replaceName($input->name,$modelTemplate);
        $this->putContentToFile($input->name, $modelTemplate);
        $this->info("New model was created!");

    }

    protected function getData()
    {
        $name = trim($this->argument('name-of-view'));
        $table = trim($this->option('table'));
        $fields = trim($this->option('fields'));
        $relation = trim($this->option('relation'));

        return (object) compact(
            'name',
            'table',
            'fields',
            'relation'
        );
    }

    protected function replaceName($name, &$modelTemplate)
    {
        $modelTemplate = str_replace(
            '{{ModelTemplateClass}}',$name, $modelTemplate);

        return $this;
    }




    protected function putContentToFile($name, $modelTemplate)
    {
        if(!file_exists($path = app_path('/Models')))
            mkdir($path, 0777, true);

        file_put_contents(app_path("/Models/{$name}.php"), $modelTemplate);
    }

}
