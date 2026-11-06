<?php

namespace Tests\Feature;

use App\Models\Organization;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class MasterDataTest extends TestCase
{
    use RefreshDatabase;

    public function test_general_employees_cannot_access_administrator_screens(): void
    {
        $employee = User::factory()->create(['system_role' => User::ROLE_GENERAL]);

        $this->actingAs($employee)
            ->get(route('admin.employees.index'))
            ->assertForbidden();
    }

    public function test_an_administrator_can_create_a_leader_with_a_hashed_password(): void
    {
        $organization = Organization::factory()->create();
        $administrator = User::factory()->for($organization)->create([
            'login_id' => 'E1000',
            'system_role' => User::ROLE_ADMIN,
        ]);

        $this->actingAs($administrator)
            ->post(route('admin.employees.store'), [
                'name' => '鈴木 花子',
                'login_id' => 'e1025',
                'organization_id' => $organization->id,
                'system_role' => User::ROLE_GENERAL,
                'is_leader' => '1',
                'is_active' => '1',
                'password' => 'new-password',
            ])
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'login_id' => 'E1025',
            'name' => '鈴木 花子',
            'system_role' => User::ROLE_GENERAL,
            'is_leader' => true,
            'organization_id' => $organization->id,
        ]);

        $employee = User::query()->where('login_id', 'E1025')->firstOrFail();
        $this->assertTrue(Hash::check('new-password', $employee->password));
    }

    public function test_administrators_cannot_assign_an_organization_to_the_wrong_level(): void
    {
        $board = Organization::factory()->create([
            'name' => '取締役会',
            'level' => Organization::LEVEL_BOARD,
        ]);
        $administrator = User::factory()->create(['system_role' => User::ROLE_ADMIN]);

        $this->actingAs($administrator)
            ->from(route('admin.organizations.create'))
            ->post(route('admin.organizations.store'), [
                'name' => '営業1G',
                'level' => Organization::LEVEL_GROUP,
                'parent_id' => $board->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.organizations.create'))
            ->assertSessionHasErrors(['parent_id' => '選択した階層のひとつ上にあたる組織を指定してください。']);

        $this->assertDatabaseMissing('organizations', ['name' => '営業1G']);
    }

    public function test_organization_cannot_be_moved_under_its_own_descendant(): void
    {
        $board = Organization::factory()->create([
            'name' => '取締役会',
            'level' => Organization::LEVEL_BOARD,
        ]);
        $headquarters = Organization::factory()->create([
            'name' => '本部',
            'level' => Organization::LEVEL_HEADQUARTERS,
            'parent_id' => $board->id,
        ]);
        $department = Organization::factory()->create([
            'name' => '営業部',
            'level' => Organization::LEVEL_DEPARTMENT,
            'parent_id' => $headquarters->id,
        ]);
        $administrator = User::factory()->create(['system_role' => User::ROLE_ADMIN]);

        $this->actingAs($administrator)
            ->from(route('admin.organizations.edit', $headquarters))
            ->put(route('admin.organizations.update', $headquarters), [
                'name' => '本部',
                'level' => Organization::LEVEL_HEADQUARTERS,
                'parent_id' => $department->id,
                'is_active' => '1',
            ])
            ->assertRedirect(route('admin.organizations.edit', $headquarters))
            ->assertSessionHasErrors('parent_id');

        $this->assertSame($board->id, $headquarters->fresh()->parent_id);
    }

    public function test_deactivating_an_employee_marks_them_inactive(): void
    {
        $organization = Organization::factory()->create();
        $administrator = User::factory()->for($organization)->create(['system_role' => User::ROLE_ADMIN]);
        $employee = User::factory()->for($organization)->create(['login_id' => 'E1024']);

        $this->actingAs($administrator)
            ->delete(route('admin.employees.destroy', $employee))
            ->assertRedirect(route('admin.employees.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('users', [
            'id' => $employee->id,
            'is_active' => false,
        ]);
    }
}
