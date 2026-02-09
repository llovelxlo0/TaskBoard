<?php

namespace App\Listeners;

use App\Events\TaskCreated;
use App\Models\TaskActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogTaskCreated
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
    public function handle(TaskCreated $event): void
    {
        $task = $event->task;

        TaskActivity::create([
            'task_id' => $task->id,
            'actor_id' => $event->actor->id,
            'type' => 'created',
            'meta' => [
                'title' => $task->title,
                'short_description' => $task->short_description,
                'priority' => $task->priority?->value ?? $task->priority,
                'status' => $task->status?->value ?? $task->status,
            ],
        ]);
    }
}
