<?php

namespace App\Listeners;

use App\Events\TaskCommentAdded;
use App\Models\TaskActivity;
use App\Models\Task;
use App\Models\TaskComment;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class LogTaskCommentAdded
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
    public function handle(TaskCommentAdded $event): void
    {
        TaskActivity::create([
            'task_id' => $event->task->id,
            'actor_id' => $event->actor->id,
            'comment_id' => $event->comment->id,
            'type' => 'comment_added',
            'meta' => null,
        ]);
    }
}
