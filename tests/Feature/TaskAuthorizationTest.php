<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Models\Task;
use App\Models\User;
use Illuminate\Contracts\Auth\Authenticatable;

class TaskAuthorizationTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_cannot_delete_someone_else_task(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $owner->id,
        ]);

        $response = $this->actingAs($otherUser instanceof Authenticatable ? $otherUser : null)->delete(route('tasks.destroy', $task));
        $response->assertForbidden();

        $this->assertNotSoftDeleted('tasks', [
            'id' => $task->id,
        ]);
    }
    public function test_user_can_not_delete_someone_else_task_with_owner(): void
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();

        $task = Task::factory()->create([
            'user_id' => $owner->id,
        ]);
        $task->delete();

        $response = $this->actingAs($otherUser instanceof Authenticatable ? $otherUser : null)->patch(route('tasks.restore', $task->id));

        $response->assertForbidden();

        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
        ]);
    }
}
