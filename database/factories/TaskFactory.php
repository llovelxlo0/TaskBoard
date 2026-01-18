<?php

namespace Database\Factories;

use App\Models\Task;
use Illuminate\Database\Eloquent\Factories\Factory;
use App\Enums\TaskStatus;
use Random\RandomException;
use App\Models\User;
use Carbon\Carbon;
use App\Enums\TaskPriority;

class TaskFactory extends Factory
{
    protected $model = Task::class;

    /**
     * @throws RandomException
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'title'  => 'Task ' . random_int(1, 1_000_000),
            'short_description' => 'Short' . random_int(1, 1000),
            'full_description' => 'Full' . random_int(1, 1000),
            'priority' => TaskPriority::MEDIUM,
            'status' => TaskStatus::TODO,
            'created_at' => Carbon::now(),
            'updated_at' => Carbon::now(),
        ];
    }
}
