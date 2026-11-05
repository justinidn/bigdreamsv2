<?php

namespace App\Listeners;

use App\Models\User;
use Illuminate\Auth\Events\Login;

class RecordUserLogin
{
    public function handle(Login $event): void
    {
        if (! ($event->user instanceof User)) {
            return;
        }

        $user = $event->user;
        $ipAddress = request()->ip();

        $user->forceFill([
            'last_login_at' => now(),
            'last_login_ip' => $ipAddress,
        ])->saveQuietly();

        activity('auth')
            ->causedBy($user)
            ->performedOn($user)
            ->withProperties(['ip_address' => $ipAddress])
            ->log('User logged in');
    }
}
