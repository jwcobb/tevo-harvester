## TEvo Harvester
TEvo Harvester is a tool to populate your own local database tables with a cache of the [Ticket Evolution API](http://developer.ticketevolution.com/) data. It allows you to very easily create, populate, and update tables with the cache-able API data and includes the ability to easily schedule for this data to be updated periodically.

This application can and should be separate from whatever project you are creating to utilize the data. This keeps the underlying technologies, dependencies, and requirements separate from your main project and allows you to update this project as necessary without affecting other projects you are building that utilize the data stored by this application.

## Installation
You can install this project via composer:

```bash
composer create-project jwcobb/tevo-harvester /path/to/project --prefer-dist
```

## Configuration
Edit the `.env` file to add your database info as well as your [Ticket Evolution API](https://ticketevolution.atlassian.net/wiki/spaces/API/pages/983121/Overview) credentials and any other necessary changes.

## Run Database Migrations
Migrations are included to create any necessary tables. From the command line run

```bash
php artisan migrate
```

## Scheduling Updates

TEvo Harvester [Laravel Scheduler](https://laravel.com/docs/8.x/scheduling#scheduling-artisan-commands) to run these commands automatically at preset times. Just be sure to [add the Laravel Scheduler to your `crontab`](https://laravel.com/docs/8.x/scheduling#running-the-scheduler).

Each Harvest already has a suggested update interval assigned to it, but using the Dashboard you can edit the schedule and even include URLs to ping before and after updates in case you wish to use a [Dead Man’s Switch](https://en.wikipedia.org/wiki/Dead_man%27s_switch) such as [Pushmon](http://www.pushmon.com/) or [Dead Man’s Snitch](https://deadmanssnitch.com/) to ensure your updates are running as desired.


## Manually Running Updates
Updates of the API information can be run either via the Dashboard or via command line using the [artisan console](https://laravel.com/docs/8.x/artisan) with a command such as

```bash
php artisan harvester:update performers --action=active
```

## Security Vulnerabilities
If you discover any security related issues, please email oss at jcobb dot org instead of using the issue tracker.

## License
TEvo Harvester is open-sourced software licensed under the [MIT license](https://github.com/jwcobb/tevo-harvester/blob/master/LICENSE)
