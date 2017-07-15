<?php

namespace App\Providers;

use Illuminate\Support\Facades\Event;
use Illuminate\Foundation\Support\Providers\EventServiceProvider as ServiceProvider;

class EventServiceProvider extends ServiceProvider
{
    /**
     * The event listener mappings for the application.
     *
     * @var array
     */
    protected $listen = [
        // 对Event事件添加监听
        'App\Events\Event' => [
            'App\Listeners\EventListener',
        ],
        // 对WriteStudents事件添加监听
        'App\Events\WriteStudents' => [
            'App\Listeners\WriteStudentsListener',
        ],
    ];

    /**
     * Register any events for your application.
     *
     * @return void
     */
    public function boot()
    {
        parent::boot();

        //
    }
}
