<x-guest-layout>
    <div class="card border shadow-sm rounded-3" x-data="{ useRecoveryCode: false }">
        <div class="card-header bg-light text-center border-bottom py-3">
            <i class="bi bi-shield-lock-fill text-primary display-5"></i>
            <h4 class="fw-bold mt-2 mb-0">Two-Factor Authentication</h4>
        </div>

        <div class="card-body p-4">
            <p class="small text-secondary mb-3" x-show="!useRecoveryCode">
                Enter the verification code from your authenticator app.
            </p>
            <p class="small text-secondary mb-3" x-show="useRecoveryCode">
                Enter one of your recovery codes.
            </p>

            <form method="POST" action="{{ route('two-factor.login.store') }}">
                @csrf

                <div class="mb-3" x-show="!useRecoveryCode">
                    <label for="code" class="form-label fw-semibold">Authentication Code</label>
                    <input id="code" name="code" type="text" inputmode="numeric" autocomplete="one-time-code"
                        class="form-control @error('code') is-invalid @enderror" :disabled="useRecoveryCode" :required="!useRecoveryCode" autofocus>
                    @error('code')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3" x-show="useRecoveryCode">
                    <label for="recovery_code" class="form-label fw-semibold">Recovery Code</label>
                    <input id="recovery_code" name="recovery_code" type="text" autocomplete="one-time-code"
                        class="form-control @error('recovery_code') is-invalid @enderror" :disabled="!useRecoveryCode" :required="useRecoveryCode">
                    @error('recovery_code')
                    <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2">Verify</button>
                <button type="button" class="btn btn-link w-100 mt-2 text-decoration-none"
                    @click="useRecoveryCode = !useRecoveryCode">
                    <span x-text="useRecoveryCode ? 'Use an authentication code' : 'Use a recovery code'"></span>
                </button>
            </form>
        </div>
    </div>
</x-guest-layout>