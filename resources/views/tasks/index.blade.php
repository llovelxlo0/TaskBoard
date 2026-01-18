<h1>Tasks</h1>

<p>
    <a href="{{ route('tasks.create') }}">Create task</a>
</p>

<table border="1" cellpadding="8" cellspacing="0">
    <thead>
    <tr>
        <th>ID</th>
        <th>Owner</th>
        <th>Task</th>
        <th>Short</th>
        <th>Priority</th>
        <th>Status</th>
        <th>Created</th>
        <th>Completed</th>
    </tr>
    </thead>
    <tbody>
    @foreach($tasks as $task)
        <tr>
            <td>{{ $task->id }}</td>
            <td>{{ $task->user->name ?? 'Unknown' }}</td>
            <td>
                <a href="{{ route('tasks.show', $task) }}">
                    {{ $task->title }}
                </a>
            </td>

            <td>{{ $task->short_description }}</td>
            <td>{{ $task->priority->value ?? $task->priority }}</td>
            <td>{{ $task->status->value ?? $task->status }}</td>
            <td>{{ $task->created_at }}</td>
            <td>{{ $task->completed_at }}</td>
        </tr>
    @endforeach
    </tbody>
</table>
