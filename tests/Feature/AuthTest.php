<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Auth\Notifications\ResetPassword;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_login(): void
    {
        $this->get('/events')->assertRedirect(route('login'));
        $this->get('/')->assertRedirect(route('login'));
    }

    public function test_login_page_renders(): void
    {
        $this->get(route('login'))
            ->assertStatus(200)
            ->assertSee('Anmelden')
            ->assertSee('Passwort vergessen?');
    }

    public function test_user_can_register(): void
    {
        $this->post(route('register'), [
            'name' => 'Mira Keller',
            'email' => 'mira@example.com',
            'password' => 'geheim-1234',
            'password_confirmation' => 'geheim-1234',
        ])->assertRedirect(route('events.index'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'mira@example.com', 'name' => 'Mira Keller']);
    }

    public function test_registration_requires_confirmed_password(): void
    {
        $this->post(route('register'), [
            'name' => 'Mira',
            'email' => 'mira@example.com',
            'password' => 'geheim-1234',
            'password_confirmation' => 'anders-1234',
        ])->assertSessionHasErrors('password');

        $this->assertGuest();
    }

    public function test_user_can_login_and_logout(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'password'])
            ->assertRedirect(route('events.index'));
        $this->assertAuthenticatedAs($user);

        $this->post(route('logout'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_login_fails_with_wrong_password(): void
    {
        $user = User::factory()->create();

        $this->post(route('login'), ['email' => $user->email, 'password' => 'falsch'])
            ->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_reset_link_can_be_requested(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email])
            ->assertSessionHas('status');

        Notification::assertSentTo($user, ResetPassword::class);
    }

    public function test_reset_link_response_does_not_reveal_unknown_addresses(): void
    {
        $this->post(route('password.email'), ['email' => 'unbekannt@example.com'])
            ->assertSessionHasNoErrors()
            ->assertSessionHas('status');
    }

    public function test_password_can_be_reset_with_valid_token(): void
    {
        Notification::fake();
        $user = User::factory()->create();

        $this->post(route('password.email'), ['email' => $user->email]);

        Notification::assertSentTo($user, ResetPassword::class, function (ResetPassword $notification) use ($user) {
            $this->get(route('password.reset', ['token' => $notification->token, 'email' => $user->email]))
                ->assertStatus(200)
                ->assertSee('Neues Passwort festlegen');

            $this->post(route('password.store'), [
                'token' => $notification->token,
                'email' => $user->email,
                'password' => 'neues-passwort-1',
                'password_confirmation' => 'neues-passwort-1',
            ])->assertRedirect(route('login'));

            return true;
        });

        $this->assertTrue(Hash::check('neues-passwort-1', $user->fresh()->password));
    }

    public function test_user_can_update_profile(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->patch(route('profile.update'), ['name' => 'Mira K.', 'email' => 'neu@example.com'])
            ->assertRedirect(route('profile.edit'));

        $this->assertSame('neu@example.com', $user->fresh()->email);
    }

    public function test_user_can_change_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'password',
                'password' => 'ganz-neu-1234',
                'password_confirmation' => 'ganz-neu-1234',
            ])->assertSessionHasNoErrors();

        $this->assertTrue(Hash::check('ganz-neu-1234', $user->fresh()->password));
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->put(route('profile.password'), [
                'current_password' => 'falsch',
                'password' => 'ganz-neu-1234',
                'password_confirmation' => 'ganz-neu-1234',
            ])->assertSessionHasErrorsIn('updatePassword', 'current_password');

        $this->assertTrue(Hash::check('password', $user->fresh()->password));
    }

    public function test_profile_page_renders(): void
    {
        $user = User::factory()->create(['name' => 'Mira Keller']);

        $this->actingAs($user)->get(route('profile.edit'))
            ->assertStatus(200)
            ->assertSee('Passwort ändern')
            ->assertSee('Mira Keller');
    }
}
