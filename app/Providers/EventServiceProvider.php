<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\Comment;
use App\Models\Conference;
use App\Models\Report;
use App\Observers\CategoryObserver;
use App\Observers\CommentObserver;
use App\Observers\ConferenceObserver;
use App\Observers\ReportObserver;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Event;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event to listener mappings for the application.
     *
     * @var array<class-string, array<int, class-string>>
     */
    protected $listen = [
        Registered::class => [
            SendEmailVerificationNotification::class,
        ],
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

    /**
     * Determine if events and listeners should be automatically discovered.
     *
     * @return bool
     */
    public function shouldDiscoverEvents()
    {
        return false;
    }

    /**
     * The model observers for your application.
     *
     * @var array
     */
    protected $observers = [
        Category::class => [CategoryObserver::class],
        Conference::class => [ConferenceObserver::class],
        Report::class => [ReportObserver::class],
        Comment::class => [CommentObserver::class]
    ];
}
