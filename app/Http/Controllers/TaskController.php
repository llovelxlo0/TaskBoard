<?php

namespace App\Http\Controllers;

use App\Enums\TaskPriority;
use Illuminate\Http\Response;
use Illuminate\Http\Request;
use App\Models\Task;
use App\Enums\TaskStatus;
use App\Services\TaskService;
use Illuminate\Validation\Rule;

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
        $priorities = array_map(fn($c)=> $c->value, TaskPriority::cases());
        $statuses = array_map(fn($c)=> $c->value, TaskStatus::cases());

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

    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'full_description' => ['nullable', 'string'],
            'priority' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);

        $task = $this->taskService->create($request->user(), $data);
        return redirect()->route('tasks.show', $task->id);
    }
    public function update(Request $request, Task $task)
    {
        $this->authorize('update', $task);

        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'short_description' => ['nullable', 'string', 'max:255'],
            'full_description' => ['nullable', 'string'],
            'priority' => ['required', 'string'],
            'status' => ['required', 'string'],
        ]);
        $this->taskService->update($task, $data, $request->user());

        return redirect()->route('tasks.show', $task);
    }
    public function edit(Task  $task)
    {
        $this->authorize('update', $task);

        $priorities = array_map(fn($c) => $c->value, TaskPriority::cases());
        $statuses = array_map(fn($c) => $c->value, TaskStatus::cases());

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
}
