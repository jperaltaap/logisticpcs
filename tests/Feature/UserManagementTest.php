<?php

namespace Tests\Feature;

use App\Models\Personal;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        // Crear roles Spatie básicos
        Role::firstOrCreate(['name' => 'ADMINISTRADOR']);
        Role::firstOrCreate(['name' => 'LOGISTICO']);
        Role::firstOrCreate(['name' => 'SUPERVISOR']);

        $this->admin = User::factory()->create([
            'rol' => 'ADMINISTRADOR',
            'estado' => 'ACTIVO',
        ]);
        $this->admin->assignRole('ADMINISTRADOR');
    }

    public function test_guests_cannot_access_users_management(): void
    {
        $response = $this->get('/users');
        $response->assertRedirect('/login');
    }

    public function test_admin_can_view_users_list(): void
    {
        $user = User::factory()->create([
            'name' => 'Mario Vargas',
            'email' => 'mvargas@test.com',
            'rol' => 'LOGISTICO',
        ]);

        $response = $this->actingAs($this->admin)->get('/users');

        $response->assertStatus(200);
        $response->assertSee('Mario Vargas');
        $response->assertSee('mvargas@test.com');
        $response->assertSee('LOGISTICO');
    }

    public function test_can_create_a_new_user_and_link_to_personal(): void
    {
        $personal = Personal::factory()->create(['estado' => 'ACTIVO']);

        $data = [
            'name' => 'Nuevo Logístico',
            'email' => 'logistica@logisticpcs.test',
            'password' => 'secret123',
            'rol' => 'LOGISTICO',
            'estado' => 'ACTIVO',
            'personal_id' => $personal->id,
        ];

        $response = $this->actingAs($this->admin)->post('/users', $data);

        $response->assertRedirect('/users');
        $this->assertDatabaseHas('users', [
            'email' => 'logistica@logisticpcs.test',
            'rol' => 'LOGISTICO',
        ]);

        $createdUser = User::where('email', 'logistica@logisticpcs.test')->first();
        $this->assertTrue(Hash::check('secret123', $createdUser->password));
        $this->assertTrue($createdUser->hasRole('LOGISTICO'));

        // Verificar vinculación en personal
        $this->assertDatabaseHas('personal', [
            'id' => $personal->id,
            'user_id' => $createdUser->id,
        ]);
    }

    public function test_can_update_user_without_changing_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('originalPassword'),
            'rol' => 'LOGISTICO',
        ]);

        $response = $this->actingAs($this->admin)->put("/users/{$user->id}", [
            'name' => 'Nombre Modificado',
            'email' => $user->email,
            'password' => null, // No se cambia contraseña
            'rol' => 'SUPERVISOR',
            'estado' => 'ACTIVO',
        ]);

        $response->assertRedirect('/users');
        $user->refresh();

        $this->assertEquals('Nombre Modificado', $user->name);
        $this->assertEquals('SUPERVISOR', $user->rol);
        $this->assertTrue(Hash::check('originalPassword', $user->password));
    }

    public function test_user_cannot_delete_their_own_account(): void
    {
        $response = $this->actingAs($this->admin)->delete("/users/{$this->admin->id}");

        $response->assertRedirect('/users');
        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
    }

    public function test_admin_can_view_dedicated_role_permissions_page(): void
    {
        $role = Role::findByName('SUPERVISOR');

        $response = $this->actingAs($this->admin)->get(route('users.roles.permissions.edit', $role));

        $response->assertStatus(200);
        $response->assertSee('Permisos del Rol: SUPERVISOR');
        $response->assertSee('SUPERVISOR');
        $response->assertSee('Guardar Permisos');
    }

    public function test_admin_can_update_role_permissions_via_dedicated_view(): void
    {
        $role = Role::findByName('SUPERVISOR');

        Permission::firstOrCreate(['name' => 'reportes.ver', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'stock.ver', 'guard_name' => 'web']);

        $response = $this->actingAs($this->admin)->put(route('users.roles.permissions.update', $role), [
            'permissions' => ['reportes.ver', 'stock.ver'],
        ]);

        $response->assertRedirect(route('users.index', ['tab' => 'roles']));
        $role->refresh();
        $this->assertTrue($role->hasPermissionTo('reportes.ver'));
        $this->assertTrue($role->hasPermissionTo('stock.ver'));
    }

    public function test_non_admin_cannot_access_role_permissions_page(): void
    {
        $supervisor = User::factory()->create(['rol' => 'SUPERVISOR']);
        $role = Role::findByName('SUPERVISOR');

        $response = $this->actingAs($supervisor)->get(route('users.roles.permissions.edit', $role));

        $response->assertRedirect(route('dashboard'));
    }
}
