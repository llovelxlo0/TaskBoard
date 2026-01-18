<h1>Create task</h1>

@if($errors->any())
    <div style="color: red;">
        <ul>
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="{{ route('tasks.store') }}">
    @csrf

    <div>
        <label>Task name (title)</label><br>
        <input type="text" name="title" value="{{ old('title') }}" required>
    </div>

    <div>
        <label>Short description</label><br>
        <input type="text" name="short_description" value="{{ old('short_description') }}">
    </div>

    <div>
        <label>Full description</label><br>
        <textarea name="full_description" rows="6">{{ old('full_description') }}</textarea>
    </div>

    <div>
        <label>Priority</label><br>
        <select name="priority" required>
            @foreach($priorities as $p)
                <option value="{{ $p }}" @selected(old('priority', 'medium') === $p)>{{ $p }}</option>
            @endforeach
        </select>
    </div>

    <div>
        <label>Status</label><br>
        <select name="status" required>
            @foreach($statuses as $s)
                <option value="{{ $s }}" @selected(old('status', 'todo') === $s)>{{ $s }}</option>
            @endforeach
        </select>
    </div>

    <button type="submit">Create</button>
</form>
