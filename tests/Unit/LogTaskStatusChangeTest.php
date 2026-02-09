<?php

namespace Tests\Unit;

use App\Enums\TaskStatusEnum;
use App\Events\TaskStatusChanged;
use App\Listeners\LogTaskStatusChange;
use App\Models\Task;
use App\Models\TaskActivity;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LogTaskStatusChangeTest extends TestCase
{
    use RefreshDatabase;

   public function testLogTaskStatusChange() {
       $user = User::factory()->create();
       $task = Task::factory()->create([
              'user_id' => $user->id,
              'status' => TaskStatusEnum::TODO,
       ]);

       $event = new TaskStatusChanged(
                task: $task,
                oldStatus: TaskStatusEnum::TODO,
                newStatus: TaskStatusEnum::IN_PROGRESS,
                actor: $user,
       );

       $listener = new LogTaskStatusChange();
       $listener->handle($event);

       $activity = TaskActivity::query()->where([
           'task_id' => $task->id,
           'actor_id' => $user->id,
           'type' => 'status_changed',
       ])->latest()->first();

       $this->assertNotNull($activity);

       $this->assertSame(TaskStatusEnum::TODO->value, $activity->meta['from'] ?? null);
       $this->assertSame(TaskStatusEnum::IN_PROGRESS->value, $activity->meta['to'] ?? null);
   }
   public function test_listener_created_activity(): void
   {
       $user = User::factory()->create();
       $task = Task::factory()->create([
           'user_id' => $user->id,
       ]);
       $event = new TaskStatusChanged(
           task : $task,
           oldStatus: TaskStatusEnum::TODO,
           newStatus: TaskStatusEnum::COMPLETED,
           actor: $user,
       );

       new LogTaskStatusChange()->handle($event);

       $activity = TaskActivity::first();

       $this->assertSame('status_changed', $activity->type);
       $this->assertSame(
           ['from' => 'todo', 'to' => 'done'],$activity->meta
       );
   }
}
