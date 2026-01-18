<div class="container">
    <h1>Register Page</h1>
    <form method="post" action="{{ route('register') }}">
        @csrf
        <label for="name">Name</label>
        <input type="text" id="name" name="name">
        @error('name')
        <span role="alert">{{ $message }}</span>
        @enderror
        <label for="email">Email</label>
        <input type="email" id="email" name="email">
        @error('email')
        <span role="alert">{{ $message }}</span>
        @enderror
        <label for="password">Password</label>
        <input type="password" id="password" name="password">
        @error('password')
        <span role="alert">{{ $message }}</span>
        @enderror
        <label for="password_confirmation">Confirm Password</label>
        <input type="password" id="password_confirmation" name="password_confirmation" placeholder="Confirm Password">
        <div>
            <button type="submit">Register</button>
        </div>
    </form>
</div>
