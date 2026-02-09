<?php

namespace App\Listeners;

use App\Events\TaskUpdated;
use App\Models\TaskActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogTaskUpdated
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(TaskUpdated $event): void
    {
        TaskActivity::create([
            'task_id' => $event->task->id,
            'actor_id' => $event->actor->id,
            'type' => 'updated',
            'meta' => $event->diff,
        ]);
    }
}
