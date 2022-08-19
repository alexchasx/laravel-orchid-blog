<?php

namespace App\Providers;

use App\Events\ArticleCreated;
use App\Listeners\ClearSidebarCache;
use App\Models\Article;
use App\Models\Rubric;
use App\Models\Tag;
use App\Observers\ArticleObserver;
use App\Observers\RubricObserver;
use App\Observers\TagObserver;
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
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        Rubric::observe(RubricObserver::class);
        Tag::observe(TagObserver::class);
        Article::observe(ArticleObserver::class);
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
}
