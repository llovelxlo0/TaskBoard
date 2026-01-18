<?php

namespace App\Services;

use App\Enums\TaskPriority;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use App\Enums\TaskStatus;
use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use App\Events\TaskUpdated;
use Illuminate\Validation\ValidationException;
use InvalidArgumentException;

class TaskService
{
    public function listForBoard(): Collection
    {
        return Task::query()->with('user')->latest()->get();
    }
    public function create(User $actor, array $data): Task
    {
        $priority = TaskPriority::from($data['priority']);
        $status = TaskStatus::from($data['status']);

        $task = Task::query()->create([
            'user_id' => $actor->id,
            'title' => $data['title'],
            'short_description' => $data['short_description'],
            'full_description' => $data['full_description'],
            'priority' => $priority,
            'status' => $status,
        ]);

        event(new TaskCreated(task: $task, user: $actor));

        return $task;
    }

    /**
     * @throws \Throwable
     */
    public function update(Task $task, array $data, User $actor): void
    {
        DB::transaction(function () use ($task, $data, $actor){
            $before = [
                'title' => $task->title,
                'short_description' => $task->short_description,
                'full_description' => $task->full_description,
                'priority' => $task->priority->value,
                'status' => $task->status->value,
            ];
            $task->title = $data['title'];
            $task->short_description = $data['short_description'] ?? null;
            $task->full_description = $data['full_description'] ?? null;

            $task->priority = TaskPriority::from($data['priority']);

            // статус лучше менять через changeStatus что бы complete_at обновлялся корректно
            $newStatus = TaskStatus::from($data['status']);
            if ($task->status !== $newStatus) {
                $task->changeStatus($newStatus);
            } else {
                $task->save();
            }
            $after = [
                'title' => $task->title,
                'short_description' => $task->short_description,
                'full_description' => $task->full_description,
                'priority' => $task->priority->value,
                'status' => $task->status->value,
            ];

            $diff = $this->buildDiff($before, $after);

            if ($diff === []) {
                return;
            }
            event(new TaskUpdated(
                task: $task,
                actor: $actor,
                diff: $diff
            ));
        });
    }
    private function buildDiff(array $before, array $after): array
    {
        $fields = [];

        foreach ($after as $key => $newValue) {
            $oldValue = $before[$key] ?? null;

            if ($oldValue !== $newValue) {
                $fields[$key] = [
                    'from' => $oldValue,
                    'to' => $newValue,
                ];
            }
        }
        return $fields;
    }
//    public function list(?string $status, string $sort = 'desc')
//    {
//        $query = Task::query();
//        if ($status) {
//            $statusEnum = TaskStatus::tryFrom($status);
//
//            if ($statusEnum) {
//                $query->where('status', $statusEnum->value);
//            }
//        }
//        return $query->orderBy('created_at', $sort)->get();
//    }
//    public function create(string $title): Task
//    {
//        return Task::create([
//            'title' => $title,
//            'status' => TaskStatus::TODO,
//        ]);
//    }
    public function changeStatus(Task $task, TaskStatus $status, User $actor): void
    {
        DB::transaction(function () use ($task, $status, $actor) {
            $oldStatus = $task->status;

            if ($oldStatus === $status) {
                return;
            }

            $task->changeStatus($status);

            event(new TaskStatusChanged(
                task: $task,
                oldStatus: $oldStatus,
                newStatus: $status,
                actor: $actor,
            ));
        });
    }
    public function delete(Task $task): void
    {
        $task->delete(); // Soft delete
    }
    public function restore(Task $task): void
    {
        $task->restore(); // Restore soft-deleted task
    }
}
