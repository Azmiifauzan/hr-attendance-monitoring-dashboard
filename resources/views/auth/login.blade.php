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
    <div class="flex justify-center mb-6">
      <div class="w-14 h-14 bg-violet-500 rounded-2xl flex items-center justify-center">
        <svg viewBox="-48 -48 96 96" width="44" height="44">
          <circle cx="0" cy="-16" r="11" fill="white"/>
          <path d="M-14 -2 Q-18 18 -14 28 L14 28 Q18 18 14 -2 Z" fill="white"/>
          <rect x="10" y="2" width="16" height="12" rx="3" fill="white" opacity="0.85"/>
          <circle cx="18" cy="8" r="3.5" fill="#7F77DD"/>
          <rect x="20" y="3" width="4" height="3" rx="1" fill="white"/>
          <circle cx="26" cy="0" r="2" fill="#FAC775" opacity="0.9"/>
        </svg>
      </div>
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

        <div class="mb-4">
          <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1.5">Username</label>
          <input id="email" name="email" type="text"
            placeholder="Masukkan username kamu"
            value="{{ old('email') }}"
            required autofocus
            class="w-full px-4 py-3 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent">
          @error('email')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <div class="mb-4">
          <label class="block text-xs font-medium text-gray-400 uppercase tracking-wider mb-1.5">Password</label>
          <input id="password" name="password" type="password"
            placeholder="••••••••"
            required
            class="w-full px-4 py-3 text-sm border border-gray-200 rounded-lg bg-gray-50 focus:outline-none focus:ring-2 focus:ring-violet-400 focus:border-transparent">
          @error('password')
            <p class="mt-1.5 text-xs text-red-500">{{ $message }}</p>
          @enderror
        </div>

        <div class="flex items-center gap-2 mb-5">
          <input id="remember_me" name="remember" type="checkbox"
            class="rounded border-gray-300 text-violet-500 focus:ring-violet-400">
          <label for="remember_me" class="text-sm text-gray-500">Ingat saya</label>
        </div>

        <button type="submit"
          class="w-full py-3 bg-violet-500 hover:bg-violet-600 active:scale-95 text-white text-sm font-medium rounded-lg transition-all">
          Masuk
        </button>

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