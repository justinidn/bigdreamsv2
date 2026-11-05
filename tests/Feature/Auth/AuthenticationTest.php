<?php

namespace Tests\Feature\Auth;

use App\Models\Department;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Crypt;
use Spatie\Permission\Models\Permission;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo(Permission::findOrCreate('system.login', 'web'));

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertNotNull($user->fresh()->last_login_at);
        $this->assertSame('127.0.0.1', $user->fresh()->last_login_ip);
        $this->assertDatabaseHas('activity_log', [
            'description' => 'User logged in',
            'subject_id' => $user->id,
            'subject_type' => User::class,
        ]);
    }

    public function test_users_can_authenticate_using_their_nrk(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo(Permission::findOrCreate('system.login', 'web'));

        $this->post('/login', [
            'email' => $user->nrk,
            'password' => 'password',
        ])->assertRedirect(route('dashboard', absolute: false));

        $this->assertAuthenticatedAs($user);
    }

    public function test_two_factor_users_are_redirected_to_the_challenge(): void
    {
        $user = $this->createUser([
            'two_factor_secret' => Crypt::encryptString('test-secret'),
            'two_factor_recovery_codes' => Crypt::encryptString(json_encode(['recovery-code'])),
            'two_factor_confirmed_at' => now(),
        ]);
        $user->givePermissionTo(Permission::findOrCreate('system.login', 'web'));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ])->assertRedirect(route('two-factor.login'));

        $this->assertGuest();
        $this->get(route('two-factor.login'))->assertOk();
    }

    public function test_users_can_start_two_factor_setup_from_their_profile(): void
    {
        $user = $this->createUser();

        $this->actingAs($user)
            ->get(route('profile.edit'))
            ->assertOk()
            ->assertSee('Confirm your password before managing two-factor authentication.');

        $this->get(route('profile.two-factor.confirmed'))
            ->assertRedirect(route('password.confirm'));

        $this->post(route('password.confirm'), ['password' => 'password'])
            ->assertRedirect(route('profile.two-factor.confirmed'));

        $this->get(route('profile.edit'))
            ->assertSee('Enable two-factor authentication');

        $this->post(route('two-factor.enable'))->assertRedirect();

        $this->assertNotNull($user->fresh()->two_factor_secret);
        $this->assertNull($user->fresh()->two_factor_confirmed_at);

        $this->get(route('two-factor.qr-code'))
            ->assertOk()
            ->assertJsonStructure(['svg', 'url']);
    }

    public function test_users_can_not_authenticate_with_invalid_password(): void
    {
        $user = $this->createUser();
        $user->givePermissionTo(Permission::findOrCreate('system.login', 'web'));

        $this->post('/login', [
            'email' => $user->email,
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_users_without_system_login_permission_cannot_authenticate(): void
    {
        Permission::findOrCreate('system.login', 'web');
        $user = $this->createUser();

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('email');
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function createUser(array $attributes = []): User
    {
        $department = Department::query()->create([
            'department_name' => 'Test Department',
            'department_alias' => 'TEST',
        ]);

        return User::factory()->create(array_merge([
            'department_id' => $department->id,
        ], $attributes));
    }

    public function test_users_can_logout(): void
    {
        $user = $this->createUser();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/');
    }
}
