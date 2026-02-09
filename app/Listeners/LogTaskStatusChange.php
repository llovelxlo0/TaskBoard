<?php

namespace App\Listeners;

use App\Models\TaskActivity;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Events\TaskStatusChanged;
use Illuminate\Support\Facades\Log;
use Illuminate\Contracts\Events\ShouldHandleEvents;
use Illuminate\Event\Attributes\Listen;

// #[Listen(TaskStatusChanged::class)]
final class LogTaskStatusChange
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
    public function handle(TaskStatusChanged $event): void
    {
        TaskActivity::create([
            'task_id' => $event->task->id,
            'actor_id' => $event->actor->id,
            'type' => 'status_changed',
            'meta' =>[
                'from' => $event->oldStatus->value,
                'to' => $event->newStatus->value,
            ],
        ]);
    }
}
