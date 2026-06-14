<?php

namespace Tests\Feature;

use App\Models\Reunion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_store_user_normalizes_email_and_requires_strong_password(): void
    {
        [$admin] = $this->createAdminContext();

        $response = $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->post(route('admin.users.store'), [
                'name' => 'Nuevo Usuario',
                'email' => '  NUEVO.USUARIO@UNIFRANZ.EDU.BO  ',
                'role_id' => $this->roleBySlug('docente')->id,
                'password' => 'Password1',
                'password_confirmation' => 'Password1',
                'activo' => '1',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('usuarios', [
            'email' => 'nuevo.usuario@unifranz.edu.bo',
            'name' => 'Nuevo Usuario',
        ]);
    }

    public function test_admin_cannot_change_own_role_from_administrator(): void
    {
        [$admin] = $this->createAdminContext();

        $response = $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->patch(route('admin.users.update', $admin), [
                'name' => $admin->name,
                'email' => $admin->email,
                'role_id' => $this->roleBySlug('docente')->id,
                'activo' => '1',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('admin_user');
        $this->assertDatabaseHas('usuarios', [
            'id' => $admin->id,
            'role_id' => $this->roleBySlug('administrador')->id,
        ]);
    }

    public function test_admin_cannot_set_invalid_reunion_state_transition(): void
    {
        [$admin] = $this->createAdminContext();
        $teacher = $this->createTeacher();

        $meeting = Reunion::create([
            'docente_id' => $teacher->id,
            'title' => 'Arquitectura de software',
            'description' => 'Clase finalizada',
            'scheduled_at' => now()->subDay(),
            'estado' => 'finalizada',
            'duration_minutes' => 90,
            'access_code' => 'FIN-001',
        ]);

        $response = $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->patch(route('admin.reuniones.update', $meeting), [
                'estado' => 'en_vivo',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('estado');
        $this->assertDatabaseHas('reuniones', [
            'id' => $meeting->id,
            'estado' => 'finalizada',
        ]);
    }

    public function test_admin_dashboard_only_shows_teachers_and_students_in_user_management(): void
    {
        [$admin] = $this->createAdminContext();
        $otherAdmin = $this->createAdministrator('admin2@unifranz.edu.bo', 'Admin Secundario');
        $teacher = $this->createTeacher();
        $student = $this->createStudent();

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk();
        $response->assertDontSee(route('admin.users.update', $admin));
        $response->assertDontSee(route('admin.users.update', $otherAdmin));
        $response->assertSee(route('admin.users.update', $teacher));
        $response->assertSee(route('admin.users.update', $student));
    }

    public function test_admin_cannot_deactivate_another_administrator(): void
    {
        [$admin] = $this->createAdminContext();
        $otherAdmin = $this->createAdministrator('admin2@unifranz.edu.bo', 'Admin Secundario');

        $response = $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->patch(route('admin.users.update', $otherAdmin), [
                'name' => $otherAdmin->name,
                'email' => $otherAdmin->email,
                'role_id' => $this->roleBySlug('administrador')->id,
                'activo' => '0',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('admin_user');
        $this->assertDatabaseHas('usuarios', [
            'id' => $otherAdmin->id,
            'activo' => true,
        ]);
    }

    public function test_admin_cannot_change_role_of_another_administrator(): void
    {
        [$admin] = $this->createAdminContext();
        $otherAdmin = $this->createAdministrator('admin2@unifranz.edu.bo', 'Admin Secundario');

        $response = $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->patch(route('admin.users.update', $otherAdmin), [
                'name' => $otherAdmin->name,
                'email' => $otherAdmin->email,
                'role_id' => $this->roleBySlug('docente')->id,
                'activo' => '1',
            ]);

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('admin_user');
        $this->assertDatabaseHas('usuarios', [
            'id' => $otherAdmin->id,
            'role_id' => $this->roleBySlug('administrador')->id,
        ]);
    }

    public function test_admin_cannot_delete_another_administrator(): void
    {
        [$admin] = $this->createAdminContext();
        $otherAdmin = $this->createAdministrator('admin2@unifranz.edu.bo', 'Admin Secundario');

        $response = $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->delete(route('admin.users.destroy', $otherAdmin));

        $response->assertRedirect(route('admin.dashboard'));
        $response->assertSessionHasErrors('admin_user');
        $this->assertDatabaseHas('usuarios', [
            'id' => $otherAdmin->id,
        ]);
    }

    public function test_admin_cannot_modify_superadministrator_accounts(): void
    {
        [$admin] = $this->createAdminContext();
        $superadmin = $this->createSuperAdministrator('super2@unifranz.edu.bo', 'Super Dos');

        $updateResponse = $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->patch(route('admin.users.update', $superadmin), [
                'name' => 'Super Editado',
                'email' => $superadmin->email,
                'role_id' => $this->roleBySlug('docente')->id,
                'activo' => '1',
            ]);

        $updateResponse->assertRedirect(route('admin.dashboard'));
        $updateResponse->assertSessionHasErrors('admin_user');

        $deleteResponse = $this->actingAs($admin)
            ->from(route('admin.dashboard'))
            ->delete(route('admin.users.destroy', $superadmin));

        $deleteResponse->assertRedirect(route('admin.dashboard'));
        $deleteResponse->assertSessionHasErrors('admin_user');
        $this->assertDatabaseHas('usuarios', [
            'id' => $superadmin->id,
            'role_id' => $this->roleBySlug('superadministrador')->id,
        ]);
    }

    private function createAdminContext(): array
    {
        $adminRole = Role::create([
            'name' => 'Administrador',
            'slug' => 'administrador',
        ]);

        Role::create([
            'name' => 'Docente',
            'slug' => 'docente',
        ]);

        Role::create([
            'name' => 'Estudiante',
            'slug' => 'estudiante',
        ]);

        Role::create([
            'name' => 'Superadministrador',
            'slug' => 'superadministrador',
        ]);

        $admin = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Admin Principal',
            'email' => 'admin@unifranz.edu.bo',
            'password' => Hash::make('Password1'),
            'activo' => true,
        ]);

        return [$admin];
    }

    private function createTeacher(): User
    {
        return User::create([
            'role_id' => $this->roleBySlug('docente')->id,
            'name' => 'Docente Uno',
            'email' => 'docente@unifranz.edu.bo',
            'password' => Hash::make('Password1'),
            'activo' => true,
        ]);
    }

    private function createStudent(): User
    {
        return User::create([
            'role_id' => $this->roleBySlug('estudiante')->id,
            'name' => 'Estudiante Uno',
            'email' => 'estudiante@unifranz.edu.bo',
            'password' => Hash::make('Password1'),
            'activo' => true,
        ]);
    }

    private function createAdministrator(string $email, string $name): User
    {
        return User::create([
            'role_id' => $this->roleBySlug('administrador')->id,
            'name' => $name,
            'email' => $email,
            'password' => Hash::make('Password1'),
            'activo' => true,
        ]);
    }

    private function createSuperAdministrator(string $email, string $name): User
    {
        return User::create([
            'role_id' => $this->roleBySlug('superadministrador')->id,
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
