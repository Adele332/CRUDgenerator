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

    protected $description = 'For creating a main View, to use CRUD functionality.';

    /** Execute the console command.  */

    protected function getStub()
    {
        return file_get_contents(__DIR__ .'/Stubs/main.blade.stub');
    }

    public function handle()
    {
        $input = $this->getData();
        $mainTemplate = $this->getStub();

        $path = __DIR__ . '/..';
        $migrationTemplate = file_get_contents($path.'/Migrations/Stubs/Migration.stub');
        $modelTemplate = file_get_contents($path.'/Models/Stubs/Model.stub');
        $crudAuthTemplate = file_get_contents(__DIR__.'/Stubs/middlewares.stub');
        $sessionAuthTemplate = file_get_contents(__DIR__.'/Stubs/middlewares.stub');

        $this->replaceName($input->name,$mainTemplate);
        $this->addToKernel();
        $this->crudUserMigration($migrationTemplate);
        $this->crudUserModel($modelTemplate);
        $this->middlewareCrudAuth($crudAuthTemplate);
        $this->middlewareSessionAuth($sessionAuthTemplate);
        $this->putContentToFile($mainTemplate, $migrationTemplate, $modelTemplate, $crudAuthTemplate, $sessionAuthTemplate);
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

    protected function crudUserMigration(&$migrationTemplate){

        $drop = "Schema::dropIfExists('crudusers');";
        $schema = "
        Schema::create('crudusers', function (Blueprint \$table) {
            \$table->increments('id');
            \$table->string('name')->nullable();
            \$table->string('email')->unique();
            \$table->string('password')->nullable();
            \$table->boolean('isAdmin')->nullable();

            \$table->timestamps();
            \$table->softDeletes();
        });

        \$password = 'adm1nCrud2023';
        \$hashedPassword = Hash::make(\$password);
        DB::table('crudusers')->insert(
            array(
                'name' => 'Admin',
                'email' => 'admin@gmail.com',
                'password' => \$hashedPassword,
                'isAdmin' => '1'
            ));

        \$password2 = 'UserCrud2023';
        \$hashedPassword2 = Hash::make(\$password2);
        DB::table('crudusers')->insert(
            array(
                'name' => 'UserCrud',
                'email' => 'user@gmail.com',
                'password' => \$hashedPassword2,
                'isAdmin' => '0'
            ));";

        $migrationTemplate = str_replace(
            '{{schemaDown}}',$drop, $migrationTemplate);

        $migrationTemplate = str_replace(
            '{{schemaOfTable}}',$schema, $migrationTemplate);
    }

    protected function crudUserModel(&$modelTemplate){

        $modelTemplate = str_replace(
            '{{ModelTemplateClass}}','Cruduser', $modelTemplate);

        $modelTemplate = str_replace(
            '{{table}}','crudusers', $modelTemplate);

        $modelTemplate = str_replace(
            '{{fields}}',"['name','password']", $modelTemplate);

        $modelTemplate = str_replace(
            '{{relation}}','', $modelTemplate);
    }

    protected function middlewareCrudAuth(&$crudAuthTemplate){

        $crudAuthTemplate = str_replace(
            '{{ClassName}}','AuthCheck', $crudAuthTemplate);

        $condition = '
        if(!Session()->has(\'loginId\')){
            return redirect("login")->with(\'fail\',"You have to login first");
        }

        return $next($request);';

        $crudAuthTemplate = str_replace(
            '{{Condition}}',$condition, $crudAuthTemplate);
    }

    protected function middlewareSessionAuth(&$sessionAuthTemplate){

        $sessionAuthTemplate = str_replace(
            '{{ClassName}}','SessionCheck', $sessionAuthTemplate);

        $condition = '
        if(Session::has(\'loginId\') && (url(\'login\')==$request->url() || url(\'crud\')==$request->url() || url(\'registration\') == $request->url())){
            return back();
        }
        return $next($request);';

        $sessionAuthTemplate = str_replace(
            '{{Condition}}',$condition, $sessionAuthTemplate);
    }

    protected function addToKernel()
    {
        $f = fopen(app_path("/Http/Kernel.php"), "r+");

        $oldstr = file_get_contents(app_path("/Http/Kernel.php"));
        $str_to_insert = "  'crudSessionAuth' => \App\Http\Middleware\SessionCheck::class,
        'crudAuth' => \App\Http\Middleware\AuthCheck::class,
        ";
        $specificLine = "protected \$routeMiddleware = [";

        while (($buffer = fgets($f)) !== false) {
            if (str_contains($buffer, $specificLine)) {
                $pos = ftell($f);
                $newstr = substr_replace($oldstr, $str_to_insert, $pos, 0);
                file_put_contents(app_path("/Http/Kernel.php"), $newstr);
                break;
            }
        }
        fclose($f);
    }

    protected function putContentToFile($mainTemplate, $migrationTemplate, $modelTemplate, $crudAuthTemplate, $sessionAuthTemplate)
    {
        $userController = file_get_contents(__DIR__ .'/Stubs/crudUserController.stub');
        $extends = file_get_contents(__DIR__ .'/Stubs/extendMain.blade.stub');
        $login = file_get_contents(__DIR__ .'/Stubs/login.blade.stub');
        $register = file_get_contents(__DIR__ .'/Stubs/register.blade.stub');
        $crudMain = file_get_contents(__DIR__ .'/Stubs/crud.blade.stub');
        $fileName = "".date('Y_m_d_His')."_crudusers.php";

        $routes = "
Route::get('/login',[App\Http\Controllers\CrudUserController::class,'login'])->middleware('crudSessionAuth');
Route::get('/registration',[App\Http\Controllers\CrudUserController::class,'registration'])->middleware('crudSessionAuth');
Route::post('/register-user',[App\Http\Controllers\CrudUserController::class,'registerUser'])->name('register-user');
Route::post('/login-user',[App\Http\Controllers\CrudUserController::class,'loginUser'])->name('login-user');
Route::get('/admin',[App\Http\Controllers\CrudUserController::class,'layout'])->middleware('crudAuth');
Route::get('/logout',[App\Http\Controllers\CrudUserController::class,'logout']);
Route::get('/crud', function () {return view('crud');})->middleware('crudSessionAuth');\n";

        if(file_exists(base_path("/resources/views/layout.blade.php")) or
            file_exists(base_path("/resources/views/extendLayout.blade.php")) or
            file_exists(base_path("/resources/views/crud.blade.php")) or
            file_exists(base_path("/resources/views/login.blade.php")) or
            file_exists(base_path("/resources/views/register.blade.php")) or
            file_exists(app_path("/Http/Controllers/CrudUserController.php")) or
            file_exists(app_path("/Http/Middleware/AuthCheck.php")) or
            file_exists(app_path("/Http/Middleware/SessionCheck.php")) or
            file_exists(app_path("/Http/Models/Cruduser.php"))
        )  {
            try {
                return throw new Exception("One of files (layout.blade.php or extendLayout.blade.php, crud.blade.php,
                login.blade.php, register.blade.php, CrudUserController.php, AuthCheck.php, SessionCheck.php, Cruduser.php)
                already exists! This command should be used only once to create main view for system!");
            } catch(Exception $e) {
                echo $e->getMessage();
                exit(1);
            }

        }else{
            file_put_contents(base_path("/resources/views/layout.blade.php"), $mainTemplate);
            file_put_contents(base_path("/resources/views/extendLayout.blade.php"), $extends);
            file_put_contents(base_path("/resources/views/login.blade.php"), $login);
            file_put_contents(base_path("/resources/views/register.blade.php"), $register);
            file_put_contents(base_path("/resources/views/crud.blade.php"), $crudMain);

            file_put_contents(app_path("/Models/Cruduser.php"), $modelTemplate);
            file_put_contents(app_path("/Http/Controllers/CrudUserController.php"), $userController);
            file_put_contents(base_path("/database/migrations/".$fileName.""), $migrationTemplate);

            file_put_contents(app_path("/Http/Middleware/AuthCheck.php"), $crudAuthTemplate);
            file_put_contents(app_path("/Http/Middleware/SessionCheck.php"), $sessionAuthTemplate);


            File::append(base_path('routes/web.php'), $routes);
            $this->info("New routes were added to routes/web.php file!");
            $this->info("You're CRUD app is ready!");
            $this->info("You can view it no http://localhost/crud");
            $this->info("Please first run 'php artisan migrate' command!");
            $this->info("New user with administrator privileges - email: admin@gmail.com password: adm1nCrud2023");
            $this->info("New user without administrator privileges - email: user@gmail.com password: UserCrud2023");
            $this->info("To start the app run 'php artisan serve' command!");
        }
    }
}
