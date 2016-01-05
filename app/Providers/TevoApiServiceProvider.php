<?php

namespace TevoHarvester\Providers;

use GuzzleHttp\Subscriber\Log\LogSubscriber;
use Illuminate\Support\Facades\Log;
use Monolog\Handler\RotatingFileHandler;
use TicketEvolution\Client;
use TicketEvolution\Laravel\TEvoServiceProvider;

/**
 * Extend the ServiceProvider that ships with the ticketevolution-php
 * package so that we can add some functionality to the Guzzle client
 * such as logging API calls.
 */
class TevoApiServiceProvider extends TEvoServiceProvider
{
    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton('tevo', function () {

            // Setup Logger
            $log = Log::getMonolog();
            $log->pushHandler(new RotatingFileHandler(
                storage_path('logs/tevo-api-client-log.txt'),
                0,
                constant('\Monolog\Logger::' . env('TEVO_API_CLIENT_LOG_LEVEL', 'INFO'))
            ));

            if (env('TEVO_API_CLIENT_LOG_LEVEL') === 'DEBUG') {
                $subscriberLogLevel = 'DEBUG';
            } else {
                $subscriberLogLevel = 'CLF';
            }
            $logSubscriber = new LogSubscriber(
                $log,
                constant('\GuzzleHttp\Subscriber\Log\Formatter::' . $subscriberLogLevel)
            );

            $apiClient = new Client(config('ticketevolution'));
            $apiClient->getEmitter()->attach($logSubscriber);

            return $apiClient;

        });

        $this->app->alias('Tevo', self::class);
    }
}
