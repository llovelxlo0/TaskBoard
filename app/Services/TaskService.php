<?php

namespace App\Services;

use App\Enums\TaskPriorityEnum;
use App\Events\TaskCommentAdded;
use App\Events\TaskCreated;
use App\Events\TaskStatusChanged;
use App\Models\Task;
use App\Enums\TaskStatusEnum;
use App\Models\TaskComment;
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
        $priority = TaskPriorityEnum::from($data['priority']);
        $status = TaskStatusEnum::from($data['status']);

        $task = Task::query()->create([
            'user_id' => $actor->id,
            'title' => $data['title'],
            'short_description' => $data['short_description'] ?? null,
            'full_description' => $data['full_description'] ?? null,
            'priority' => $priority,
            'status' => $status,
        ]);

        event(new TaskCreated(task: $task, actor: $actor));

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
            ];
            $task->title = $data['title'];
            $task->short_description = $data['short_description'] ?? null;
            $task->full_description = $data['full_description'] ?? null;
            $task->priority = TaskPriorityEnum::from($data['priority']);
            $task->save();

            $newStatus = TaskStatusEnum::from($data['status']);
            if ($task->status !== $newStatus){
                $this->changeStatusInsideTransaction($task, $newStatus, $actor);
            }

            $after = [
                'title' => $task->title,
                'short_description' => $task->short_description,
                'full_description' => $task->full_description,
                'priority' => $task->priority->value,
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
    public function changeStatusInsideTransaction(Task $task, TaskStatusEnum $status, User $actor): void
    {
        // Assume we are already in a transaction
        $oldStatus = $task->status;
        if ($oldStatus === $status) {
            return;
        }

        $task->moveTo($status);

        event(new TaskStatusChanged(
            task: $task,
            oldStatus: $oldStatus,
            newStatus: $status,
            actor: $actor,
        ));
    }
    public function addComment(Task $task, User $actor, string $comment): TaskComment
    {
    return DB::transaction(function () use ($task, $actor, $comment){
        $comment = TaskComment::create([
            'task_id' => $task->id,
            'user_id' => $actor->id,
            'comment' => $comment,
        ]);
        event(new TaskCommentAdded(
            task: $task,
            comment: $comment,
            actor: $actor
        ));
        return $comment;
    });
    }
    public function changeStatus(Task $task, TaskStatusEnum $status, User $actor): void
    {
        DB::transaction(function () use ($task, $status, $actor) {
            $oldStatus = $task->status;

            if ($oldStatus === $status) {
                return;
            }

            $task->moveTo($status);

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
