<?php

namespace App\Providers;

use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Listeners\SendEmailVerificationNotification;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Prometheus\CollectorRegistry;

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
     */
    public function boot(): void
    {
        DB::listen(function ($query) {
            $duration = $query->time / 1000;
            $queryType = $this->queryType($query->sql);

            $queryCounter = app(CollectorRegistry::class)->getOrRegisterCounter(
                'tasks',
                'query_count',
                'Count the database queries',
                ['query_type']
            );
            $queryCounter->incBy(1, [$queryType]);

            $queryDuration = app(CollectorRegistry::class)->getOrRegisterHistogram(
                'tasks',
                'query_duration_seconds',
                'Database query duration in seconds',
                ['query_type'],
                [0.01, 0.1, 1, 5]
            );
            $queryDuration->observe($duration, [$queryType]);
        });
    }


    private function queryType($sql)
    {
        $sql = strtolower($sql);
        if (str_starts_with($sql, 'select')) {
            return 'select';
        }else {
            return 'other';
        }
    }

    /**
     * Determine if events and listeners should be automatically discovered.
     */
    public function shouldDiscoverEvents(): bool
    {
        return false;
    }
}
