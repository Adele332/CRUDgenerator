<?php

namespace adele332\crudgenerator\Commands\Models;

use Illuminate\Console\GeneratorCommand;
use Exception;

class ModelCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     * * @var strin
     */

    protected $signature = 'make:model
                            {name-of-model : The name of newly created model. PRIVALOMAS}
                            {--table= : Name of the table in database. PRIVALOMAS}
                            {--fields= : Columns names in newly created table. PRIVALOMAS}
                            {--relation= : The relationships between models. PASIRENKAMAS}';

    /** The console command description. */

    protected $description = 'For creating a new Model';

    /** Execute the console command.  */

    protected function getStub()
    {
        return file_get_contents(__DIR__ .'/Stubs/Model.stub');
    }

    public function handle()
    {
        $input = $this->getData();

        //$filePlace = $this->getPath($input->name);
        //if($this->alreadyExists($filePlace)) return false;

        $modelTemplate = $this->getStub();

        $this->replaceName($input->name,$modelTemplate);
        $this->replaceTable($input->table, $modelTemplate);
        $this->replaceFields($input->fields, $modelTemplate);
        $this->replaceRelation($input->relation, $modelTemplate);
        $this->putContentToFile($input->name, $modelTemplate);
    }

    protected function getData()
    {
        $name = trim($this->argument('name-of-model'));
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

    protected function replaceTable($table, &$modelTemplate)
    {
        $modelTemplate = str_replace('{{table}}',
            $table,
            $modelTemplate);
        return $this;
    }

    protected function replaceFields($fields, &$modelTemplate)
    {
        $modelTemplate = str_replace('{{fields}}',
            $fields,
            $modelTemplate);
        return $this;
    }

    protected function replaceRelation($relation, &$modelTemplate)
    {
        $relations = explode(';', $relation);

        if(!empty($relation)) {
            foreach ($relations as $relation) {
                $relationArray = explode(',', $relation);
                if (count($relationArray) > 3) {
                    $function[$relation] = "\n    public function " . $relationArray[1] . "()
        {
            return \$this->" . $relationArray[0] . "(" .
                        $relationArray[1] . "::class,'" .
                        $relationArray[2] . "','" .
                        $relationArray[3] . "');
        }";
                }
            }

            $modelTemplate = str_replace(
                '{{relation}}', implode($function), $modelTemplate
            );
        }else{
            $function="";
            $modelTemplate = str_replace(
                '{{relation}}', $function, $modelTemplate
            );
        }

        return $this;
    }

    protected function putContentToFile($name, $modelTemplate)
    {
        if(!file_exists($path = app_path("/Models/{$name}.php"))){
            //mkdir($path, 0777, true);
            file_put_contents(app_path("/Models/{$name}.php"), $modelTemplate);
            $this->info("New model was created!");
        }else{
            try {
                return throw new Exception("Model $name already exists!");
            } catch(Exception $e) {
                echo $e->getMessage();
            }
        }
    }
}
