<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="min-h-screen bg-gray-50 flex items-center justify-center px-4">

  <div class="w-full max-w-sm">

    {{-- Logo --}}
    <div class="text-center mb-8">
      <div class="w-14 h-14 bg-violet-500 rounded-2xl flex items-center justify-center mx-auto mb-4">
        <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
          <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm-2 15l-5-5 1.41-1.41L10 14.17l7.59-7.59L19 8l-9 9z"/>
        </svg>
      </div>
      <h1 class="text-xl font-medium text-gray-900">Selamat datang</h1>
      <p class="text-sm text-gray-400 mt-1">Masuk ke sistem absensi</p>
    </div>

    {{-- Card --}}
    <div class="bg-white border border-gray-200 rounded-xl p-6 shadow-sm">

      @if (session('status'))
        <div class="mb-4 text-sm text-green-600 bg-green-50 px-3 py-2 rounded-lg">
          {{ session('status') }}
        </div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf

        {{-- Username --}}
        <div class="mb-4">
          <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1.5">
            Username
          </label>
          <input id="email" name="email" type="text"
            placeholder="Masukkan username kamu"
            value="{{ old('email') }}"
            required autofocus
            class="w-full px-4 py-3 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent">
          @error('email')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Password --}}
        <div class="mb-4">
          <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1.5">
            Password
          </label>
          <input id="password" name="password" type="password"
            placeholder="••••••••"
            required
            class="w-full px-4 py-3 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent">
          @error('password')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
          @enderror
        </div>

        {{-- Remember Me --}}
        <div class="flex items-center gap-2 mb-5">
          <input id="remember_me" name="remember" type="checkbox"
            class="rounded border-gray-300 text-violet-500 focus:ring-violet-400">
          <label for="remember_me" class="text-sm text-gray-500">Ingat saya</label>
        </div>

        {{-- Submit --}}
        <button type="submit"
          class="w-full py-3 bg-violet-500 hover:bg-violet-600 active:scale-95 text-white text-sm font-medium rounded-lg transition-all">
          Masuk
        </button>

        {{-- Forgot Password --}}
        @if (Route::has('password.request'))
          <div class="mt-4 pt-4 border-t border-gray-100 text-center">
            <a href="{{ route('password.request') }}"
              class="text-xs text-gray-400 hover:text-violet-500 transition-colors">
              Lupa password?
            </a>
          </div>
        @endif

      </form>
    </div>

  </div>

</body>
</html>