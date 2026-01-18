<div class="container">
    <h1>Login Page</h1>
    <form method="post" action="{{ route('login') }}">
        @csrf
        <label for="name">Name</label>
        <input type="text" id="name" name="name">
        @error('name')
        <span role="alert">{{ $message }}</span>
        @enderror
        <label for="password">Password</label>
        <input type="password" id="password" name="password">
        @error('password')
        <span role="alert">{{ $message }}</span>
        @enderror
        <div>
            <button type="submit">Login</button>
        </div>
    </form>
</div>
