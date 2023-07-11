<?php

namespace App\Http\Controllers;

use App\Models\Task;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Prometheus\CollectorRegistry;
use Prometheus\RenderTextFormat;

class PrometheusController extends Controller
{
    public function myMetrics(Request $request)
    {
        DB::connection()->enableQueryLog();
        $collectorRegistry = app(CollectorRegistry::class);

        //memory usage metric
        $memoryUsage = memory_get_usage(true);
        $gauge = $collectorRegistry->getOrRegisterGauge('app', 'memory_usage_bytes', 'Memory usage in bytes');
        $gauge->set($memoryUsage);

        // Count the number of registered users
        $usersRegistered = User::count();
        $gauge = $collectorRegistry->getOrRegisterGauge(
            'tasks',
            'users_registered_count',
            'Count the registered users'
        );
        $gauge->set($usersRegistered);


        // Count the number of created tasks
        $usersRegistered = Task::count();
        $gauge = $collectorRegistry->getOrRegisterGauge(
            'tasks',
            'tasks_created_count',
            'Count the created tasks'
        );
        $gauge->set($usersRegistered);



        // Count the number of created categories
        $usersRegistered = Task::count();
        $gauge = $collectorRegistry->getOrRegisterGauge(
            'tasks',
            'categories_created_count',
            'Count the created categories'
        );
        $gauge->set($usersRegistered);

        // Track cache hits
        $cacheHits = Cache::get('cache_hits', 0);
        $cacheHitsCounter = $collectorRegistry->getOrRegisterCounter(
            'tasks',
            'cache_hits_total',
            'count of cache hits'
        );
        $cacheHitsCounter->incBy($cacheHits);



        $renderer = new RenderTextFormat();
        $result = $renderer->render($collectorRegistry->getMetricFamilySamples());

        return response($result, 200)->header('Content-Type', RenderTextFormat::MIME_TYPE);
    }
}
