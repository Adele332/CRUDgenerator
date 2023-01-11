# adele332/crudgenerator laravel package
[![MIT License](https://img.shields.io/badge/License-MIT-green.svg)](https://choosealicense.com/licenses/mit/)
[![MIT License](https://github.styleci.io/repos/7548986/shield?style=plastic)](https://github.com/RafalKLab/crud)

This Laravel package will give you an opportunity to create CRUD functionality faster just simply using command line.
You will be able from command line:
- Create Models
- Create Migrations
- Create Controllers
- Create View for particular tables
- Create Main website views with login, registration and CRUD functionality

### Requirements
This package requires:
- Laravel >= 9.33.0 
- PHP >= 8.0.2
- Composer >= 2.4.2

### Installation

1. Run command
```
  composer require adele332/crudgenerator
```
2. Run command 
```
  php artisan list
```
3. Check if in the list you see newly added commands
```
    If you see new commands that means that you successfuly installed this package! :)
```

### Newly added commands to the project
#### There will be 5 new commands which you will see after running command 'php artisan list':
- php artisan make:model
- php artisan make:controllers
- php artisan make:migrations
- php artisan make:view
- php artisan make:main

#### Examples of suitable commands:
1. Command for creating Model
```
    php artisan make:model Genre --table="genres" --fields="['id','title','created_at','updated_at']"
```
2. Command for creating Controller
```
    php artisan make:controllers Genres --model="Genre" --fields-validation="'title' => 'required|alpha|min:2'" --dir-to-save-file=admin
```
3. Command for creating Migration
```
    php artisan make:migrations Genres --table-schema="title:string"
```
4. Command for creating Views (you should use it only then you have data tables in database)
```
    php artisan make:model Genre --table="genres" --fields="['id','title','created_at','updated_at']"
```
5. Command for creating Main views (this command must be used only once)
```
    php artisan make:main MyCrudGenerator
```
