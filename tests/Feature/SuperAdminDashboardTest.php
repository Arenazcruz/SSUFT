<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperAdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_superadmin_is_redirected_to_superadmin_dashboard(): void
    {
        [$superadmin] = $this->createContext();

        $response = $this->actingAs($superadmin)->get(route('dashboard'));

        $response->assertRedirect(route('superadmin.dashboard'));
    }

    public function test_superadmin_dashboard_only_shows_manageable_users(): void
    {
        [$superadmin] = $this->createContext();
        $otherSuperadmin = $this->createUser('super2@unifranz.edu.bo', 'Super Dos', 'superadministrador');
        $admin = $this->createUser('admin2@unifranz.edu.bo', 'Admin Dos', 'administrador');
        $teacher = $this->createUser('docente2@unifranz.edu.bo', 'Docente Dos', 'docente');
        $student = $this->createUser('estudiante2@unifranz.edu.bo', 'Estudiante Dos', 'estudiante');

        $response = $this->actingAs($superadmin)->get(route('superadmin.dashboard'));

        $response->assertOk();
        $response->assertDontSee(route('superadmin.users.update', $superadmin));
        $response->assertDontSee(route('superadmin.users.update', $otherSuperadmin));
        $response->assertSee(route('superadmin.users.update', $admin));
        $response->assertSee(route('superadmin.users.update', $teacher));
        $response->assertSee(route('superadmin.users.update', $student));
    }

    public function test_superadmin_can_update_administrator_user(): void
    {
        [$superadmin] = $this->createContext();
        $admin = $this->createUser('admin2@unifranz.edu.bo', 'Admin Dos', 'administrador');

        $response = $this->actingAs($superadmin)
            ->from(route('superadmin.dashboard'))
            ->patch(route('superadmin.users.update', $admin), [
                'name' => 'Admin Operativo',
                'email' => 'admin.operativo@unifranz.edu.bo',
                'role_id' => $this->roleBySlug('docente')->id,
                'activo' => '0',
            ]);

        $response->assertRedirect(route('superadmin.dashboard'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuarios', [
            'id' => $admin->id,
            'name' => 'Admin Operativo',
            'email' => 'admin.operativo@unifranz.edu.bo',
            'role_id' => $this->roleBySlug('docente')->id,
            'activo' => false,
        ]);
    }

    public function test_superadmin_cannot_update_another_superadmin(): void
    {
        [$superadmin] = $this->createContext();
        $otherSuperadmin = $this->createUser('super2@unifranz.edu.bo', 'Super Dos', 'superadministrador');

        $response = $this->actingAs($superadmin)
            ->from(route('superadmin.dashboard'))
            ->patch(route('superadmin.users.update', $otherSuperadmin), [
                'name' => 'Super Editado',
                'email' => $otherSuperadmin->email,
                'role_id' => $this->roleBySlug('administrador')->id,
                'activo' => '1',
            ]);

        $response->assertRedirect(route('superadmin.dashboard'));
        $response->assertSessionHasErrors('superadmin_user');
        $this->assertDatabaseHas('usuarios', [
            'id' => $otherSuperadmin->id,
            'name' => 'Super Dos',
            'role_id' => $this->roleBySlug('superadministrador')->id,
        ]);
    }

    public function test_superadmin_cannot_delete_another_superadmin(): void
    {
        [$superadmin] = $this->createContext();
        $otherSuperadmin = $this->createUser('super2@unifranz.edu.bo', 'Super Dos', 'superadministrador');

        $response = $this->actingAs($superadmin)
            ->from(route('superadmin.dashboard'))
            ->delete(route('superadmin.users.destroy', $otherSuperadmin));

        $response->assertRedirect(route('superadmin.dashboard'));
        $response->assertSessionHasErrors('superadmin_user');
        $this->assertDatabaseHas('usuarios', [
            'id' => $otherSuperadmin->id,
        ]);
    }

    public function test_superadmin_cannot_create_superadmin_accounts_from_dashboard(): void
    {
        [$superadmin] = $this->createContext();

        $response = $this->actingAs($superadmin)
            ->from(route('superadmin.dashboard'))
            ->post(route('superadmin.users.store'), [
                'name' => 'Super Nuevo',
                'email' => 'super.nuevo@unifranz.edu.bo',
                'role_id' => $this->roleBySlug('superadministrador')->id,
                'password' => 'Password1',
                'password_confirmation' => 'Password1',
                'activo' => '1',
            ]);

        $response->assertRedirect(route('superadmin.dashboard'));
        $response->assertSessionHasErrors('role_id');
        $this->assertDatabaseMissing('usuarios', [
            'email' => 'super.nuevo@unifranz.edu.bo',
        ]);
    }

    private function createContext(): array
    {
        Role::create(['name' => 'Administrador', 'slug' => 'administrador']);
        Role::create(['name' => 'Docente', 'slug' => 'docente']);
        Role::create(['name' => 'Estudiante', 'slug' => 'estudiante']);
        Role::create(['name' => 'Superadministrador', 'slug' => 'superadministrador']);

        $superadmin = $this->createUser('superadmin@unifranz.edu.bo', 'Super Uno', 'superadministrador');

        return [$superadmin];
    }

    private function createUser(string $email, string $name, string $roleSlug): User
    {
        return User::create([
            'role_id' => $this->roleBySlug($roleSlug)->id,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('Password1'),
            'activo' => true,
        ]);
    }

    private function roleBySlug(string $slug): Role
    {
        return Role::query()->where('slug', $slug)->firstOrFail();
    }
}

