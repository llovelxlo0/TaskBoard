<?php

namespace Tests\Feature;

use App\Events\TaskStatusChanged;
use App\Listeners\LogTaskStatusChange;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;
use App\Models\Task;
use App\Enums\TaskStatus;
use App\Services\TaskService;

class TaskEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_is_created_when_task_status_changes(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::TODO,
        ]);

        $service = app(TaskService::class);
        $service->changeStatus($task, TaskStatus::IN_PROGRESS,$user);

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $task->id,
            'actor_id' => $user->id,
            'type' => 'status_changed',
            'meta' => json_encode([
                'from' => TaskStatus::TODO->value,
                'to' => TaskStatus::IN_PROGRESS->value,
            ]),
//            'from_status' => TaskStatus::TODO->value,
//            'to_status' => TaskStatus::IN_PROGRESS->value,
        ]);
    }
    public function test_status_change_is_rolled_back_if_listener_fails(): void
    {
        //Event::fake([TaskStatusChanged::class]);
        $actor = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $actor->id,
            'status' => TaskStatus::TODO,
        ]);

        Event::listen(TaskStatusChanged::class, function (){
            throw new \Exception("Listener failed");
        });

        try {
            app(TaskService::class)->changeStatus($task, TaskStatus::IN_PROGRESS, $actor);
        } catch (\Exception) {
            // Exception is expected
        }
        $this->assertEquals(TaskStatus::TODO, $task->fresh()->status, $actor);
    }
}
