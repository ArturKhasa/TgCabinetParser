<?php

namespace App\Providers;

use App\Events\TgParseAdCreated;
use App\Events\TgParseAdUpdated;
use App\Listeners\AdOrdSignup;
use App\Listeners\TgParseAdCreatedListener;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
        TgParseAdCreated::class => [
            TgParseAdCreatedListener::class
        ],
        TgParseAdUpdated::class => [
//            TgParseAdCreatedListener::class,
//            AdOrdSignup::class
        ]
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        //
    }
}
