<?php

namespace adele332\crudgenerator\Commands\Migrations;

use Illuminate\Support\Str;
use Exception;
use Illuminate\Console\GeneratorCommand;

class MigrationCommand extends GeneratorCommand
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:migrations
                            {name : Name of newly created migration, provide name for what DB table will be created (example Genres).}
                            {--table-schema= : Database table schema.}
                            {--foreign= : Foreign keys seperated by ",". example - foreign(\'game_id\')->references(\'id\')->on(\'low_games\')->onDelete(\'cascade\')}';


    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'For creating new migration.';

    protected function getStub()
    {
        return file_get_contents(__DIR__ .'/Stubs/Migration.stub');
    }

    public function handle()
    {
        $input = $this->getData();

        $migrationTemplate = $this->getStub();

        $this->replaceClassName($input->name,$migrationTemplate);
        $this->replaceSchemaDown($input->name,$migrationTemplate);
        $this->replaceSchemaUp($input->schema, $input->name, $input->foreign,$migrationTemplate);
        $this->putContentToFile($input->name, $migrationTemplate);
    }

    protected function getData()
    {
        $name = trim($this->argument('name'));
        $schema = trim($this->option('table-schema'));
        $foreign = trim($this->option('foreign'));

        return (object) compact(
            'name',
            'schema',
            'foreign'
        );
    }

    protected function replaceClassName($name, &$migrationTemplate)
    {
        $migrationTemplate = str_replace(
            '{{MigrationName}}',Str::plural($name), $migrationTemplate);
        return $this;
    }

    protected function replaceSchemaDown($name, &$migrationTemplate)
    {
        $drop = "Schema::dropIfExists('".Str::plural(strtolower($name))."');";
        $migrationTemplate = str_replace(
            '{{schemaDown}}',$drop, $migrationTemplate);
        return $this;
    }

    protected function replaceSchemaUp($schema, $name, $foreign, &$migrationTemplate)
    {
       // --schema="name:string, quantity:integer, price:float, userId:integer"
       // php artisan make:migrations Games --table-schema="name:string,quantity:integer,price:float,genre_id:foreignId" --foreign="genre_id,id,genres"

        $schemaField = explode(',', $schema);

        if(!empty($schema)) {
            foreach ($schemaField as $schema) {
                $schemaArray = explode(':', $schema);
                if (count($schemaArray) >= 2) {
                    if(!empty($schemaArray[0]) and !empty($schemaArray[1])) {
                        $function[$schema] = "\$table->" . $schemaArray[1] . "('" . $schemaArray[0] . "')->nullable();
                ";
                    }else{try {
                        return throw new Exception("Please provided schema fields with name and type! For example: --schema=\"name:string\"");
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        exit(1);
                    }}
                }else {
                    try {
                        return throw new Exception("Please provided schema fields with name and type! For example: --schema=\"name:string\"");
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        exit(1);
                    }
                }
            }
        }else {
            try {
                return throw new Exception("Please provided database table schema! Use parameter --table-schema=");
            } catch (Exception $e) {
                echo $e->getMessage();
                exit(1);
            }
        }

        // $table->foreign('userId')->references('id')->on('users')->onDelete('cascade');
        // --foreign="userId,id,users"
        $text = explode(';', $foreign);

        if(!empty($foreign)) {
            foreach ($text as $foreign) {
                $textArray = explode(',', $foreign);
                if (count($textArray) === 3) {
                    $function2[$foreign] = "\$table->foreign('" . $textArray[0] . "')->references('" . $textArray[1] . "')->on('" . $textArray[2] . "');
                ";
                } else {
                    try {
                        return throw new Exception("You have to provide 3 fields for making foreign. For example --foreign=\"userId,id,users\"");
                    } catch (Exception $e) {
                        echo $e->getMessage();
                        exit(1);
                    }
                }
            }
        }else {
            $function2[$foreign] ='';
        }

        $schemaUp =
            "Schema::create('".Str::plural(strtolower($name))."', function(Blueprint \$table) {
                \$table->increments('id');
                ".implode($function)."
                \$table->timestamps();
                \$table->softDeletes();
                ".implode($function2)."
            });
            ";

        $migrationTemplate = str_replace(
            '{{schemaOfTable}}',$schemaUp, $migrationTemplate);
        return $this;
    }

    protected function putContentToFile($name, $migrationTemplate)
    {
        $datePrefix = date('Y_m_d_His');
        $fileName = "".$datePrefix."_create_".Str::plural(strtolower($name))."_table.php";

        file_put_contents(base_path("/database/migrations/".$fileName.""), $migrationTemplate);
        $this->info("New migration was created!");
        $this->info("Do not forget to run 'php artisan migrate' command!");

    }
}
