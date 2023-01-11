<?php

namespace adele332\crudgenerator\Commands\Controllers;

use Exception;
use Illuminate\Console\GeneratorCommand;
use Illuminate\Support\Str;

class ControllerCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     * * @var string
     */

    protected $signature = 'make:controllers
                            {name-of-controller : The name of newly created controller (name should be in plural).}
                            {--model= : The name of model which will be used for new controller.}
                            {--fields-validation= : Description for fields that needs to be validated.}
                            {--dir-to-save-file= : Directory name to which new file will be saved (by default it will be saved in App/Http/Controllers).}
                            ';

    /** The console command description. */

    protected $description = 'For creating a new Controller';

    /** Execute the console command.  */

    protected function getStub()
    {
        return file_get_contents(__DIR__ .'/Stubs/Controller.stub');
    }

    public function handle()
    {
        $input = $this->getData();
        $controllerTemplate = $this->getStub();

        $this->replaceName($input->name,$controllerTemplate);
        $this->replaceModel($input->model,$controllerTemplate);
        $this->replaceNameInLowerCase($input->name,$controllerTemplate);
        $this->replaceNameInSingular($input->name,$controllerTemplate);
        $this->replaceNameInSingularLowerCase($input->name,$controllerTemplate);
        $this->replaceValidation($input->validate,$controllerTemplate);
        $this->putContentToFile($input->newDir, $input->name, $controllerTemplate);
    }

    protected function getData()
    {
        $name = trim($this->argument('name-of-controller'));
        $model = trim($this->option('model'));
        $validate = trim($this->option('fields-validation'));
        $newDir = trim($this->option('dir-to-save-file'));

        return (object) compact(
            'name',
            'model',
            'validate',
            'newDir'
        );
    }

    protected function replaceName($name, &$controllerTemplate)
    {
        $controllerTemplate = str_replace(
            '{{ControllerTemplateClass}}',
            $name,
            $controllerTemplate);
        return $this;
    }

    protected function replaceModel($model, &$controllerTemplate)
    {
        if (!empty($model)) {
            $controllerTemplate = str_replace(
                '{{ModelName}}', $model, $controllerTemplate);
            return $this;
        }else{
            try {
                return throw new Exception("Please use mandatory parameter --model!");
            } catch(Exception $e) {
                echo $e->getMessage();
                exit(1);
            }
        }
    }

    protected function replaceNameInLowerCase($name, &$controllerTemplate)
    {
        $controllerTemplate = str_replace(
            '{{ControllerNameLowerCase}}',
            strtolower($name),
            $controllerTemplate);
        return $this;
    }

    protected function replaceNameInSingular($name, &$controllerTemplate)
    {
        $controllerTemplate = str_replace(
            '{{ControllerNameSingular}}',
            Str::singular($name),
            $controllerTemplate);
        return $this;
    }

    protected function replaceNameInSingularLowerCase($name, &$controllerTemplate)
    {
        $controllerTemplate = str_replace(
            '{{ControllerNameSingularLowerCase}}',
            Str::singular(strtolower($name)),
            $controllerTemplate);
        return $this;
    }

    protected function replaceValidation($validate, &$controllerTemplate)
    {
        if(!empty($validate)) {
            $validateStruct = "\$request->validate( [" . $validate . "]);\n";
            $controllerTemplate = str_replace('{{validationFields}}', $validateStruct, $controllerTemplate);
        }else{
            $controllerTemplate = str_replace(
                '{{validationFields}}', '', $controllerTemplate);
        }
        return $this;
    }

    protected function putContentToFile($newDir, $name, $controllerTemplate)
    {
        if(!file_exists($path = app_path("/Http/Controllers/{$newDir}")))
            mkdir($path, 0777, true);

        if(!empty($newDir)) {
            $controllerTemplate = str_replace(
                '{{DirectoryName}}', '\\'.$newDir, $controllerTemplate);
        }else{$controllerTemplate = str_replace(
            '{{DirectoryName}}', '', $controllerTemplate);}

        if(!file_exists(app_path("/Http/Controllers/{$newDir}/{$name}Controller.php")))  {
            file_put_contents(app_path("/Http/Controllers/{$newDir}/{$name}Controller.php"), $controllerTemplate);
            $this->info("New controller was created!");
        }else{
            try {
                return throw new Exception("This controller file already exists!");
            } catch(Exception $e) {
                echo $e->getMessage();
                exit(1);
            }
        }
    }
}
