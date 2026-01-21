<?php
namespace Tests\Unit;

use App\Enums\TaskStatusEnum;
use App\Events\TaskCreated;
use App\Listeners\LogTaskCreated;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\User;
use App\Services\TaskService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskActivities extends TestCase
{
    use RefreshDatabase;

    public function test_created_task_created_activity(): void
    {
        $user = User::factory()->create();

        $task = app(TaskService::class)->create($user, [
            'title' => 'New Task',
            'short_description' => 'Short',
            'full_description' => 'Full',
            'priority' => 'medium',
            'status' => 'todo',
        ]);
        $this->assertDatabaseHas('task_activities', [
            'task_id' => $task->id,
            'actor_id' => $user->id,
            'type' => 'created',
        ]);
    }
    public function test_task_created_listener_writes_meta(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'T1'
        ]);
        $event = new TaskCreated(task: $task, actor: $user);

        new LogTaskCreated()->handle($event);

        $activity = TaskActivity::query()->latest('id')->firstOrFail();

        $this->assertSame('created', $activity->type);
        $this->assertSame($user->id, $activity->actor_id);
        $this->assertSame($task->id, $activity->task_id);

        $this->assertSame('T1', $activity->meta['title']);
    }
}
