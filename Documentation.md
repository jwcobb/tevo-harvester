# TEvo Harvester

## Installation
### Server Requirements
This project is based on Laravel 5.1 and therefore shares the same [server requirements](http://laravel.com/docs/5.1#installation) plus the additional requirement of a MySQL-like database such as [Percona Server](https://www.percona.com/software/mysql-database/percona-server), [MariaDB](https://mariadb.org/), [Amazon Aurora](https://aws.amazon.com/rds/aurora/), or [MySQL](https://www.mysql.com/products/). Versions equivalent to MySQL 5.6 or higher are recommended.

### Installing TEvo Harvester
Like almost all modern PHP software, TEvo Harvester utilizes [Composer](http://getcomposer.org/) to manage its dependencies. So, before using TEvo Harvester, make sure you have Composer installed on your machine.

```bash
$ composer create-project jwcobb/tevo-harvester /path/to/project --prefer-dist
```

## Configuration
### Database
Be sure to create a database and database user and password if necessary.

### TEvo Harvester
TEvo Harvester utilizes the [DotEnv](https://github.com/vlucas/phpdotenv) PHP library by Vance Lucas that comes built in to Laravel. In a fresh TEvo Harvester installation, the root directory of your application will contain a `.env.example` file. If you install TEvo Harvester via Composer, this file will automatically be renamed to `.env`. Otherwise, you should rename the file manually.

Modify your environment variables in this `.env` file as needed for your own local server, as well as your production environment. However, your `.env` file should not be committed to your application’s source control.

#### Run Database Migrations
Migrations are included to create any necessary tables. From the command line run

```bash
$ php artisan migrate
```

#### Create a User
If you wish to use the web based functionality you need to create a User. To do so first uncomment the registration routes in `app/Http/routes.php`.

```php
Route::get('auth/register', 'Auth\AuthController@getRegister');
Route::post('auth/register', 'Auth\AuthController@postRegister');
```

Using your browser go to `/auth/register` and create a User. Once completed you should re-comment those lines to prevent others from registering.

## Running Updates
Updates of the API information can be run either via the Dashboard or via command line using the [artisan console](http://laravel.com/docs/artisan) with a command such as 

```php
$ php artisan harvester:update performers --action=active
```

### Scheduling Updates
You can use the [Laravel Scheduler](http://laravel.com/docs/5.1/scheduling) to run these commands automatically at preset times. Just be sure to [add the Laravel Scheduler to your `crontab`](http://laravel.com/docs/5.1/scheduling#introduction).

Each Harvest already has a suggested update interval assigned to it, but using the Dashboard you can edit the schedule and even include URLs to ping before and after updates in case you wish to use a [Dead Man’s Switch](https://en.wikipedia.org/wiki/Dead_man%27s_switch) such as [Pushmon](http://www.pushmon.com/) or [Dead Man’s Snitch](https://deadmanssnitch.com/) to ensure your updates are running as desired.

