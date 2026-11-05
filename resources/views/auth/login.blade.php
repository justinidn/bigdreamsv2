<x-guest-layout>
    <div class="card border-0 shadow-lg rounded-4 overflow-hidden">
        <div class="card-body p-4 p-md-5">


            <div class="text-center mb-4">
                <h1 class="h4 fw-bold text-dark mb-1">{{ __('Welcome back') }}</h1>
                <p class="text-muted small mb-0">{{ __('Please enter your details to sign in.') }}</p>
            </div>

            @if (session('status'))
            <div class="alert alert-success alert-dismissible fade show small mb-4" role="alert">
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
            @endif

            <form method="POST" action="{{ route('login') }}" novalidate>
                @csrf

                {{-- Email Field --}}
                <div class="mb-3">
                    <label for="email" class="form-label fw-semibold small text-secondary">{{ __('Email Address') }}</label>
                    <div class="input-group">
                        <span class="input-group-text  border-end-0 text-muted">
                            <i class="bi bi-envelope"></i>
                        </span>
                        <input id="email"
                            class="form-control  border-start-0 ps-0 @error('email') is-invalid @enderror"
                            type="email"
                            name="email"
                            value="{{ old('email') }}"
                            required
                            autofocus
                            autocomplete="username"
                            placeholder="name@example.com">
                        @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Password Field --}}
                <div class="mb-3">
                    <div class="d-flex align-items-center justify-content-between mb-1">
                        <label for="password" class="form-label fw-semibold small text-secondary mb-0">{{ __('Password') }}</label>
                        @if (Route::has('password.request'))
                        <a class="small text-decoration-none fw-semibold" href="{{ route('password.request') }}">{{ __('Forgot password?') }}</a>
                        @endif
                    </div>
                    <div class="input-group">
                        <span class="input-group-text  border-end-0 text-muted">
                            <i class="bi bi-lock"></i>
                        </span>
                        <input id="password" class="form-control   ps-0 @error('password') is-invalid @enderror"
                            type="password"
                            name="password"
                            required
                            autocomplete="current-password"
                            placeholder="••••••••">
                        <button class="btn btn-outline-secondary border-start-0  text-muted" type="button" onclick="togglePasswordVisibility()">
                            <i class="bi bi-eye" id="togglePasswordIcon"></i>
                        </button>
                        @error('password')
                        <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                </div>

                {{-- Remember Me --}}
                <div class="form-check mb-4">
                    <input id="remember_me" type="checkbox" class="form-check-input" name="remember" @checked(old('remember'))>
                    <label class="form-check-label small text-secondary" for="remember_me">{{ __('Remember me on this device') }}</label>
                </div>

                {{-- Submit Button --}}
                <button type="submit" class="btn btn-primary w-100 py-2.5 fw-bold shadow-sm rounded-3">{{ __('Sign In') }}</button>

            </form>
        </div>
    </div>

    {{-- Script ringan untuk Toggle Password Visibility --}}
    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const icon = document.getElementById('togglePasswordIcon');
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                icon.classList.replace('bi-eye', 'bi-eye-slash');
            } else {
                passwordInput.type = 'password';
                icon.classList.replace('bi-eye-slash', 'bi-eye');
            }
        }
    </script>
</x-guest-layout>