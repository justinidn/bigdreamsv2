<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Two-factor authentication') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Add an extra layer of security to your account using an authenticator app.') }}
        </p>
    </header>

    @if (! $twoFactorPasswordConfirmed)
        <p class="mt-4 text-sm text-gray-600">
            {{ __('Confirm your password before managing two-factor authentication.') }}
        </p>
        <a class="mt-2 inline-block underline text-sm text-gray-600 hover:text-gray-900"
           href="{{ route('profile.two-factor.confirmed') }}">
            {{ __('Confirm password') }}
        </a>
    @elseif ($user->hasEnabledTwoFactorAuthentication())
        <p class="mt-4 text-sm text-green-700">
            {{ __('Two-factor authentication is enabled.') }}
        </p>

        <div class="mt-4" x-data="{ codes: [], error: '' }">
            <button type="button"
                    class="underline text-sm text-gray-600 hover:text-gray-900"
                    @click="fetch('{{ route('two-factor.recovery-codes') }}', { headers: { Accept: 'application/json' } })
                        .then(response => {
                            if (! response.ok) throw new Error();
                            return response.json();
                        })
                        .then(data => { codes = data; error = ''; })
                        .catch(() => error = '{{ __('Could not load recovery codes. Confirm your password and try again.') }}')">
                {{ __('Show recovery codes') }}
            </button>

            <p class="mt-2 text-sm text-red-600" x-text="error"></p>

            <ul class="mt-2 list-disc list-inside font-mono text-sm text-gray-700" x-show="codes.length">
                <template x-for="code in codes" :key="code">
                    <li x-text="code"></li>
                </template>
            </ul>

            <form method="POST" action="{{ route('two-factor.regenerate-recovery-codes') }}" class="mt-4">
                @csrf
                <x-secondary-button type="submit">
                    {{ __('Regenerate recovery codes') }}
                </x-secondary-button>
            </form>
        </div>

        <form method="POST" action="{{ route('two-factor.disable') }}" class="mt-4">
            @csrf
            @method('DELETE')
            <x-danger-button>
                {{ __('Disable two-factor authentication') }}
            </x-danger-button>
        </form>
    @elseif ($user->two_factor_secret)
        <div class="mt-4" x-data="{ svg: '', error: '' }"
             x-init="fetch('{{ route('two-factor.qr-code') }}', { headers: { Accept: 'application/json' } })
                .then(response => {
                    if (! response.ok) throw new Error();
                    return response.json();
                })
                .then(data => svg = data.svg)
                .catch(() => error = '{{ __('Could not load the QR code. Confirm your password and reload this page.') }}')">
            <p class="text-sm text-gray-600">
                {{ __('Scan this QR code with your authenticator app, then enter the generated code to finish setup.') }}
            </p>
            <div class="mt-4" x-html="svg"></div>
            <p class="mt-2 text-sm text-red-600" x-text="error"></p>
        </div>

        <form method="POST" action="{{ route('two-factor.confirm') }}" class="mt-4 space-y-4">
            @csrf
            <div>
                <x-input-label for="two_factor_code" :value="__('Authentication code')" />
                <x-text-input id="two_factor_code" name="code" type="text" class="mt-1 block w-full" required autocomplete="one-time-code" />
                <x-input-error class="mt-2" :messages="$errors->get('code')" />
            </div>

            <x-primary-button>
                {{ __('Confirm two-factor authentication') }}
            </x-primary-button>
        </form>
    @else
        <form method="POST" action="{{ route('two-factor.enable') }}" class="mt-4">
            @csrf
            <x-primary-button>
                {{ __('Enable two-factor authentication') }}
            </x-primary-button>
        </form>
    @endif

    @if (session('status') === Laravel\Fortify\Fortify::TWO_FACTOR_AUTHENTICATION_CONFIRMED)
        <p class="mt-4 text-sm text-green-700">
            {{ __('Two-factor authentication has been confirmed.') }}
        </p>
    @endif

    <x-input-error class="mt-2" :messages="$errors->get('code')" />
</section>
