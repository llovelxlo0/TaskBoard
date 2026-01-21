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
use App\Enums\TaskStatusEnum;
use App\Services\TaskService;

class TaskEventTest extends TestCase
{
    use RefreshDatabase;

    public function test_activity_is_created_when_task_status_changes(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatusEnum::TODO,
        ]);

        $service = app(TaskService::class);
        $service->changeStatus($task, TaskStatusEnum::IN_PROGRESS,$user);

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $task->id,
            'actor_id' => $user->id,
            'type' => 'status_changed',
            'meta' => json_encode([
                'from' => TaskStatusEnum::TODO->value,
                'to' => TaskStatusEnum::IN_PROGRESS->value,
            ]),
        ]);
    }
    public function test_status_change_is_rolled_back_if_listener_fails(): void
    {
        //Event::fake([TaskStatusChanged::class]);
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatusEnum::TODO,
        ]);

        Event::listen(TaskStatusChanged::class, function (){
            throw new \Exception("Listener failed");
        });

        try {
            app(TaskService::class)->changeStatus($task, TaskStatusEnum::IN_PROGRESS, $user);
        } catch (\Exception) {
            // Exception is expected
        }
        $this->assertEquals(TaskStatusEnum::TODO, $task->fresh()->status, $user);
    }
}
