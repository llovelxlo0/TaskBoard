<h1>Task #{{ $task->id }}</h1>

<p><b>Owner:</b> {{ $task->user->name ?? $task->user_id }}</p>
<p><b>Title:</b> {{ $task->title }}</p>
<p><b>Short:</b> {{ $task->short_description }}</p>
<p><b>Priority:</b> {{ $task->priority->value ?? $task->priority }}</p>
<p><b>Status:</b> {{ $task->status->value ?? $task->status }}</p>
<p><b>Created:</b> {{ $task->created_at }}</p>
<p><b>Completed:</b> {{ $task->completed_at }}</p>

<hr>

<h2>Full description</h2>
<p>{{ $task->full_description }}</p>


<hr>

<h2>History</h2>

@if($activities->isEmpty())
    <p>No activity yet.</p>
@else
    <ul>
        @foreach($activities as $activity)
            <li>
                <strong>{{ $activity->created_at->format('Y-m-d H:i') }}</strong>
                —
                {{ $activity->actor?->name ?? 'System' }}
                @if($activity->type === 'created')
                    created tast
                @elseif($activity->type === 'status_changed')
                    changed status from
                    <b>{{ $activity->meta['from'] }}</b>
                    to
                    <b>{{ $activity->meta['to'] }}</b>
                @else
                    {{$activity->type}}
                @endif
            </li>
        @endforeach
    </ul>
@endif
@if($activity->type === 'updated')
    updated:
    <ul>
        @foreach(($activity->meta['fields'] ?? []) as $field => $change)
            <li>
                {{ $field }}: <b>{{ $change['from'] ?? '-' }}</b> → <b>{{ $change['to'] ?? '-' }}</b>
            </li>
        @endforeach
    </ul>
@endif
<hr>
<h2>Comments</h2>

@auth
    <form method="POST" action="{{ route('tasks.comments.store', $task) }}">
        @csrf
        <textarea name="comment" rows="3" required></textarea><br>
        <button type="submit">Add comment</button>
    </form>
@endauth

@if($task->comments->isEmpty())
    <p>No comments yet.</p>
@else
    <ul>
        @foreach($task->comments as $comment)
            <li>
                <strong>{{ $comment->user->name }}</strong>
                ({{ $comment->created_at->format('Y-m-d H:i') }}):
                <br>
                {{ $comment->comment }}
            </li>
        @endforeach
    </ul>
@endif



<p><a href="{{ route('tasks.edit', $task) }}">edit</a></p>
<p><a href="{{ route('tasks.index') }}">Back to list</a></p>
