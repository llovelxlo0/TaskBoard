<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriorityEnum;
use App\Enums\TaskStatusEnum;
use App\Http\Requests\TaskCommentRequest;
use App\Http\Requests\TaskStoreRequest;
use App\Http\Requests\TaskUpdateRequest;
use App\Models\Task;
use App\Services\TaskService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskController extends Controller
{
    public function __construct(
        private readonly TaskService $taskService
    ) {}

    public function index(Request $request)
    {
        $this->authorize('viewAny', Task::class);

        $tasks = $this->taskService->listForBoard();

        return view('tasks.index', compact('tasks'));
    }
    public function create()
    {
        $priorities = array_map(fn($c)=> $c->value, TaskPriorityEnum::cases());
        $statuses = array_map(fn($c)=> $c->value, TaskStatusEnum::cases());

        return view('tasks.create', compact('priorities', 'statuses'));
    }
    public function show(Task $task)
    {
        $this->authorize('view', $task);

        $task->load(['user', 'activities.actor']);

        return view('tasks.show', [
            'task' => $task,
            'activities' => $task->activities->sortByDesc('id'),
        ]);
    }

    public function store(TaskStoreRequest $request, TaskService $service)
    {
        $task = $service->create(
            actor: $request->user(),
            data: $request->validated()
        );

        return redirect()->route('tasks.show', $task);
    }
    public function update(TaskUpdateRequest $request, Task $task, TaskService $service)
    {
        $this->authorize('update', $task);

        $service->update($task, $request->validated(), $request->user());

        return redirect()->route('tasks.show', $task);
    }
    public function edit(Task  $task)
    {
        $this->authorize('update', $task);

        $priorities = array_map(fn($c) => $c->value, TaskPriorityEnum::cases());
        $statuses = array_map(fn($c) => $c->value, TaskStatusEnum::cases());

        return view('tasks.edit', compact('task', 'priorities', 'statuses'));
    }
    public function destroy(Task $task): Response
    {
        $this->authorize('delete', $task);

        $this->taskService->delete($task);

        return response()->noContent();
    }
    public function restore(int $task): Response
    {
        $taskModel = Task::withTrashed()->findOrFail($task);

        $this->authorize('restore', $taskModel);

        $this->taskService->restore($taskModel);

        return response()->noContent();
    }

    public function commentStore(Task $task, TaskCommentRequest $request)
    {
        //$this->authorize('view', $task);
        $request->validated();

        $this->taskService->addComment(
            $task,
            $request->user(),
            $request->string('comment')->toString()
        );
        return redirect()->route('tasks.show', $task);
    }
}
