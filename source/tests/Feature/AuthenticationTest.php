<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_guests_are_redirected_to_the_login_page(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('日報システムへログイン');
        $this->get(route('reports.index'))->assertRedirect(route('login'));
    }

    public function test_an_active_employee_can_log_in_and_log_out(): void
    {
        $organization = Organization::factory()->create();
        $employee = User::factory()->for($organization)->create([
            'login_id' => 'E1024',
            'password' => 'employee-secret',
        ]);

        $this->post(route('login.store'), ['login_id' => 'E1024', 'password' => 'employee-secret'])
            ->assertRedirect(route('dashboard'))
            ->assertSessionHasNoErrors();
        $this->assertAuthenticatedAs($employee);

        $this->post(route('logout'))
            ->assertRedirect(route('login'))
            ->assertSessionHasNoErrors();
        $this->assertGuest();
    }

    public function test_invalid_credentials_do_not_authenticate_and_show_a_japanese_error(): void
    {
        $organization = Organization::factory()->create();
        User::factory()->for($organization)->create(['login_id' => 'E1024']);

        $this->from(route('login'))
            ->post(route('login.store'), ['login_id' => 'E1024', 'password' => 'incorrect'])
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors([
                'login_id' => 'ログインIDまたはパスワードが正しくありません。',
            ]);

        $this->assertGuest();
    }

    public function test_inactive_employees_cannot_log_in(): void
    {
        $organization = Organization::factory()->create();
        User::factory()->for($organization)->create([
            'login_id' => 'E1024',
            'is_active' => false,
        ]);

        $this->post(route('login.store'), ['login_id' => 'E1024', 'password' => 'password'])
            ->assertSessionHasErrors('login_id');

        $this->assertGuest();
    }

    public function test_disabling_an_authenticated_employee_ends_their_session(): void
    {
        $organization = Organization::factory()->create();
        $employee = User::factory()->for($organization)->create(['is_active' => false]);

        $this->actingAs($employee)
            ->get(route('dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }
}
