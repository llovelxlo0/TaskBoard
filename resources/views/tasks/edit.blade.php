<h1>Edit task #{{ $task->id }}</h1>

@if($errors->any())
    <div style="color:red;">
        <ul>
            @foreach($errors->all() as $e)
                <li>{{ $e }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('tasks.update', $task) }}">
    @csrf
    @method('PUT')

    <div>
        <label>Title</label><br>
        <input name="title" value="{{ old('title', $task->title) }}" required>
    </div>

    <div>
        <label>Short description</label><br>
        <input name="short_description" value="{{ old('short_description', $task->short_description) }}">
    </div>

    <div>
        <label>Full description</label><br>
        <textarea name="full_description" rows="6">{{ old('full_description', $task->full_description) }}</textarea>
    </div>

    <div>
        <label>Priority</label><br>
        <select name="priority" required>
            @foreach($priorities as $p)
                <option value="{{ $p }}" @selected(old('priority', $task->priority->value) === $p)>
                    {{ $p }}
                </option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Status</label><br>
        <select name="status" required>
            @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(old('status', $task->status->value) === $s)>
                    {{ $s }}
                </option>
            @endforeach
        </select>
    </div>

    <button type="submit">Save</button>
</form>
