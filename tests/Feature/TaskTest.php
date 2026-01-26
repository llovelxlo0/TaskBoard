<?php

namespace Tests\Feature;

use App\Enums\TaskPriorityEnum;
use App\Models\TaskActivity;
use App\Models\TaskComment;
use App\Models\User;
use App\Services\TaskService;
use Tests\TestCase;
use App\Models\Task;
use App\Enums\TaskStatusEnum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    public function test_task_can_be_created(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $response = $this->post(route('tasks.store'), [
            'title' => 'New Task',
            'priority' => TaskPriorityEnum::MEDIUM->value,
            'status' => TaskStatusEnum::TODO->value,
            'short_description' => null,
            'full_description' => null,
        ]);
        $task = Task::query()->latest('id')->first();
        $this->assertNotNull($task);

        $response->assertRedirect(route('tasks.show', $task->id));
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'title' => 'New Task',
            'user_id' => $user->id,
            'status' => TaskStatusEnum::TODO->value,
        ]);
    }
    public function test_task_can_be_changed(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatusEnum::TODO,
        ]);

        $response = $this->put(route('tasks.update', $task), [
            'title' => $task->title,
            'priority' => $task->priority->value,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
            'short_description' => $task->short_description,
            'full_description' => $task->full_description,
        ]);

        // web: обычно redirect, api: 200/204
        $response->assertStatus(302);

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => TaskStatusEnum::IN_PROGRESS->value,
        ]);
    }
    public function test_task_cannot_be_updated_with_invalid_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatusEnum::TODO,
        ]);

        $response = $this->put(route('tasks.update', $task), [
            'title' => $task->title,
            'priority' => $task->priority->value,
            'status' => 'banana',
            'short_description' => $task->short_description,
            'full_description' => $task->full_description,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['status']);
    }
    public function test_can_filter_tasks_by_status(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Task::factory()->create([
            'user_id' => $user->id,
            'title' => 'In progress',
            'status' => TaskStatusEnum::IN_PROGRESS,
        ]);
        $response = $this->get('/tasks?status=' . TaskStatusEnum::IN_PROGRESS->value);
        $response->assertOk();
        $response->assertSee('In progress');
        $response->assertDontSee('Todo');
    }
    public function test_can_get_tasks_list(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        Task::factory()->count(2)->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get('/tasks');

        $response->assertOk();
        $response->assertSee(2);
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
        $user = User::factory()->create();
        $this->actingAs($user);

        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->delete(route('tasks.destroy', $task));

        $response->assertNoContent();

        $this->assertSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }
    public function test_soft_deleted_task_can_be_restored(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $task = Task::factory()->create([
            'user_id' => $user->id,
        ]);

        $task->delete();

        $this->assertSoftDeleted('tasks', ['id' => $task->id]);

        $response = $this->patch(route('tasks.restore', $task->id));

        $response->assertNoContent();

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
            'status' => TaskStatusEnum::TODO,
        ]);
        app(TaskService::class)->changeStatus($task, TaskStatusEnum::IN_PROGRESS, $user);
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
            'priority' => TaskPriorityEnum::MEDIUM,
            'status' => TaskStatusEnum::TODO,
        ]);

        $this->actingAs($user)->put(route('tasks.update', $task), [
            'title' => 'Old', // не меняем
            'short_description' => 'S2', // меняем
            'full_description' => null,
            'priority' => TaskPriorityEnum::HIGH->value, // меняем
            'status' => TaskStatusEnum::TODO->value,
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
    public function test_user_can_add_comment_to_task(): void
    {
        $user = User::factory()->create();
        $task = Task::factory()->create([
            'user_id' => $user->id,
            'status' => TaskStatusEnum::TODO,
            'priority' => TaskPriorityEnum::MEDIUM,
        ]);

        $this->actingAs($user);

        $response = $this->post(route('tasks.comments.store', $task), [
            'comment' => 'First comment',
        ]);
        $comment = TaskComment::query()->latest('id')->first();

        $response->assertRedirect(route('tasks.show', $task));

        $this->assertDatabaseHas('task_comments', [
            'task_id' => $task->id,
            'user_id' => $user->id,
            'comment' => 'First comment',
        ]);

        $this->assertDatabaseHas('task_activities', [
            'task_id' => $task->id,
            'actor_id' => $user->id,
            'comment_id' => $comment->id,
            'type' => 'comment_added',
        ]);
    }
}
