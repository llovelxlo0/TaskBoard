<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Tests\TestCase;
use App\Enums\TaskStatusEnum;
use App\Models\Task;

class TaskCompletedAtTest extends TestCase
{
    use RefreshDatabase;
    public function test_move_to_completed_sets_completed_at(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatusEnum::TODO,
            'completed_at' => null,
        ]);

        $task->moveTo(TaskStatusEnum::COMPLETED);

        $task = $task->fresh();
        $this->assertTrue($task->isCompleted());
        $this->assertNotNull($task->completed_at);
    }

    public function test_move_to_non_completed_clears_completed_at(): void
    {
        $task = Task::factory()->create([
            'status' => TaskStatusEnum::TODO,
        ]);

        $task->moveTo(TaskStatusEnum::COMPLETED);
        $this->assertNotNull($task->fresh()->completed_at);

        $task->moveTo(TaskStatusEnum::IN_PROGRESS);

        $task = $task->fresh();
        $this->assertFalse($task->isCompleted());
        $this->assertNull($task->completed_at);
    }
}
