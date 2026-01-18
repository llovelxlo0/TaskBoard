<?php

namespace Tests\Feature;

use App\Enums\TaskPriority;
use App\Models\TaskActivity;
use App\Models\User;
use App\Services\TaskService;
use Tests\TestCase;
use App\Models\Task;
use App\Enums\TaskStatus;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_created(): void
    {
        $response = $this->postJson('/tasks', [
            'title' => 'New Task',
        ]);

        $response->assertStatus(201);

        $this->assertDatabaseHas('tasks', [
            'title' => 'New Task',
            'status' => 'todo',
        ]);
    }
    public function test_task_can_be_changed(): void
    {
        $task = Task::create([
            'title' => 'Existing Task',
            'status' => TaskStatus::IN_PROGRESS,
        ]);
        $this->patchJson("/tasks/{$task->id}/status", [
            'status' => 'in_progress',
        ]);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'in_progress',
        ]);
    }
    public function test_task_cannot_be_updated_with_invalid_status(): void
    {
        $task = Task::create([
            'title' => 'Task',
            'status' => TaskStatus::TODO,
        ]);

        $response = $this->patchJson("/tasks/{$task->id}/status", [
            'status' => 'banana',
        ]);

        $response->assertStatus(422);
    }
    public function test_can_filter_tasks_by_status(): void
    {
        Task::create(['title' => 'Todo', 'status' => TaskStatus::TODO]);
        Task::create(['title' => 'In progress', 'status' => TaskStatus::IN_PROGRESS]);

        $response = $this->getJson('/tasks?status=in_progress');
        $response->dump();

        $response->assertStatus(200);
        $response->assertJsonCount(1);
        $response->assertJsonFragment([
            'status' => 'in_progress',
        ]);
    }
    public function test_can_get_tasks_list(): void
    {
        Task::create(['title' => 'Task 1', 'status' => TaskStatus::TODO]);
        Task::create(['title' => 'Task 2', 'status' => TaskStatus::IN_PROGRESS]);

        $response = $this->getJson('/tasks');
        $response->assertStatus(200);
        $response->assertJsonCount(2);
    }
    public function test_task_is_soft_deleted(): void
    {
        $task = Task::factory()->create();

        $task->delete();

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }
    public function test_soft_deleted_task_is_not_returned_by_default(): void
    {
        $task = Task::factory()->create();
        $task->delete();

        $this->assertNull(Task::find($task->id));
    }
    public function test_soft_deleted_task_can_be_found_with_trashed(): void
    {
        $task = Task::factory()->create();
        $task->delete();

        $this->assertNotNull(Task::withTrashed()->find($task->id));
    }
    public function test_task_can_be_soft_deleted(): void
    {
        $task = Task::factory()->create();

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertNoContent();

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }
    public function test_soft_deleted_task_can_be_restored(): void
    {
        $task = Task::factory()->create();
        $task->delete();

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
        $response = $this->patch(route('tasks.restore', $task->id));
        $response->assertNoContent();

        // Проверка, что deleted_at теперь null
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'deleted_at' => null,
        ]);
    }
    public function test_status_change_created_activity(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatus::TODO,
        ]);
        app(TaskService::class)->changeStatus($task, TaskStatus::IN_PROGRESS, $user);
        $this->assertDatabaseHas('task_activities', [
            'task_id' => $task->id,
            'actor_id' => $user->id,
            'type' => 'status_changed'
        ]);
    }
    public function test_updating_task_creates_updated_activity_with_diff(): void
    {
        $user = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'Old',
            'short_description' => 'S1',
            'priority' => TaskPriority::MEDIUM,
            'status' => TaskStatus::TODO,
        ]);

        $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Old', // не меняем
            'short_description' => 'S2', // меняем
            'full_description' => null,
            'priority' => TaskPriority::HIGH->value, // меняем
            'status' => TaskStatus::TODO->value,
        ])->assertRedirect(route('tasks.show', $task));

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $task->id,
            'actor_id' => $user->id,
            'type' => 'updated',
        ]);

        $activity = TaskActivity::query()->latest('id')->firstOrFail();

        $this->assertSame('updated', $activity->type);
        $this->assertArrayHasKey('fields', $activity->meta);
        $this->assertArrayHasKey('priority', $activity->meta['fields']);
        $this->assertSame('medium', $activity->meta['fields']['priority']['from']);
        $this->assertSame('high', $activity->meta['fields']['priority']['to']);
    }
}
