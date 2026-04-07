<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Login &mdash; {{ config('app.name', 'OrangeApps Finance ERP') }}</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <link rel="stylesheet" href="{{ asset('css/app.css') }}">
</head>
<body class="bg-gray-100 antialiased min-h-screen flex items-center justify-center">

    <div class="w-full max-w-md px-4">
        {{-- Login Card --}}
        <div class="bg-white rounded-2xl shadow-xl overflow-hidden">
            {{-- Header --}}
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 px-8 py-8 text-center">
                <div class="flex items-center justify-center mb-3">
                    <div class="flex items-center justify-center w-14 h-14 bg-white/20 rounded-xl backdrop-blur-sm">
                        <svg class="w-8 h-8 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4.26 10.147a60.438 60.438 0 0 0-.491 6.347A48.62 48.62 0 0 1 12 20.904a48.62 48.62 0 0 1 8.232-4.41 60.46 60.46 0 0 0-.491-6.347m-15.482 0a50.636 50.636 0 0 0-2.658-.813A59.906 59.906 0 0 1 12 3.493a59.903 59.903 0 0 1 10.399 5.84c-.896.248-1.783.52-2.658.814m-15.482 0A50.717 50.717 0 0 1 12 13.489a50.702 50.702 0 0 1 7.74-3.342M6.75 15v-3.75m0 0 5.25 3 5.25-3" />
                        </svg>
                    </div>
                </div>
                <h1 class="text-2xl font-bold text-white">OrangeApps</h1>
                <p class="text-primary-100 text-sm mt-1">Finance ERP</p>
            </div>

            {{-- Form --}}
            <div class="px-8 py-8">
                {{-- Error alert (for server-side errors) --}}
                @if ($errors->any())
                    <div class="mb-4 p-3 bg-red-50 border border-red-200 rounded-lg">
                        <ul class="text-sm text-red-600 list-disc list-inside">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @if (session('status'))
                    <div class="mb-4 p-3 bg-green-50 border border-green-200 rounded-lg text-sm text-green-600">
                        {{ session('status') }}
                    </div>
                @endif

                {{-- AJAX alert container --}}
                <div id="login-alert" class="mb-4 p-3 rounded-lg text-sm hidden"></div>

                <form id="login-form" method="POST">
                    @csrf

                    {{-- Email --}}
                    <div class="mb-5">
                        <label for="email" class="block text-sm font-medium text-gray-700 mb-1.5">Email / Employee ID</label>
                        <input
                            type="text"
                            id="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="email"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm"
                            placeholder="you@orangeapps.edu.ph or EMP-001"
                        >
                    </div>

                    {{-- Password --}}
                    <div class="mb-5">
                        <label for="password" class="block text-sm font-medium text-gray-700 mb-1.5">Password</label>
                        <input
                            type="password"
                            id="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            class="w-full px-4 py-2.5 rounded-lg border border-gray-300 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 transition text-sm"
                            placeholder="Enter your password"
                        >
                    </div>

                    {{-- Remember Me --}}
                    <div class="flex items-center justify-between mb-6">
                        <label class="flex items-center gap-2 cursor-pointer">
                            <input
                                type="checkbox"
                                name="remember"
                                id="remember"
                                {{ old('remember') ? 'checked' : '' }}
                                class="w-4 h-4 rounded border-gray-300 text-primary-600 focus:ring-primary-500"
                            >
                            <span class="text-sm text-gray-600">Remember Me</span>
                        </label>
                    </div>

                    {{-- Submit --}}
                    <button
                        type="submit"
                        id="login-btn"
                        class="w-full bg-primary-600 hover:bg-primary-700 text-white font-semibold py-2.5 px-4 rounded-lg transition duration-200 text-sm focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2"
                    >
                        Sign In
                    </button>
                </form>
            </div>
        </div>

        {{-- Footer --}}
        <p class="text-center text-xs text-gray-400 mt-6">
            OrangeApps Finance ERP v1.0
        </p>
    </div>

    <script>
        document.getElementById('login-form').addEventListener('submit', function(e) {
            e.preventDefault();

            var btn = document.getElementById('login-btn');
            var alert = document.getElementById('login-alert');
            var email = document.getElementById('email').value;
            var password = document.getElementById('password').value;
            var token = document.querySelector('input[name="_token"]').value;

            // Disable button
            btn.disabled = true;
            btn.textContent = 'Signing in...';
            alert.classList.add('hidden');

            fetch('{{ route("multi_login") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    email: email,
                    password: password
                })
            })
            .then(function(response) { return response.json(); })
            .then(function(data) {
                if (data.status === 1 && data.redirect) {
                    // Success
                    alert.className = 'mb-4 p-3 rounded-lg text-sm bg-green-50 border border-green-200 text-green-600';
                    alert.textContent = data.text || 'Logged in successfully!';
                    alert.classList.remove('hidden');
                    window.location.href = data.redirect;
                } else {
                    // Error
                    alert.className = 'mb-4 p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-600';
                    alert.textContent = data.text || 'Invalid credentials!';
                    alert.classList.remove('hidden');
                    btn.disabled = false;
                    btn.textContent = 'Sign In';
                }
            })
            .catch(function(err) {
                alert.className = 'mb-4 p-3 rounded-lg text-sm bg-red-50 border border-red-200 text-red-600';
                alert.textContent = 'Something went wrong. Please try again.';
                alert.classList.remove('hidden');
                btn.disabled = false;
                btn.textContent = 'Sign In';
            });
        });
    </script>

</body>
</html>
