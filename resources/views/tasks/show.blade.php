<h1>Task #{{ $task->id }}</h1>

<p><b>Owner:</b> {{ $task->user?->name ?? $task->user_id }}</p>
<p><b>Title:</b> {{ $task->title }}</p>
<p><b>Short:</b> {{ $task->short_description ?? '-' }}</p>
<p><b>Priority:</b> {{ $task->priority->value ?? $task->priority }}</p>
<p><b>Status:</b> {{ $task->status->value ?? $task->status }}</p>
<p><b>Created:</b> {{ $task->created_at }}</p>
<p><b>Completed:</b> {{ $task->completed_at ?? '-' }}</p>

<hr>

<h2>Full description</h2>
<p>{{ $task->full_description ?? '-' }}</p>


<hr>
<h2>Last Activity</h2>
@include('tasks.partials.activity-log')
<hr>
<h2>Comments</h2>

@auth
    <form method="POST" action="{{ route('tasks.comments.store', $task) }}">
        @csrf
        <textarea name="comment" rows="3" required>{{ old('comment') }}</textarea><br>
        @error('comment')
            <div style="color:red;">{{ $message }}</div>
        @enderror
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
