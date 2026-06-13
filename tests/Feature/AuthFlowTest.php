<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AuthFlowTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_requires_an_institutional_email(): void
    {
        $response = $this->from('/login')->post('/login', [
            'email' => 'usuario@gmail.com',
            'password' => 'password',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors('email');
    }

    public function test_admin_user_is_redirected_to_admin_dashboard_after_login(): void
    {
        $adminRole = Role::create([
            'name' => 'Administrador',
            'slug' => 'administrador',
        ]);

        $user = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Paola Mendoza',
            'email' => 'admin@unifranz.edu.bo',
            'password' => Hash::make('password'),
            'activo' => true,
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));

        $redirected = $this->actingAs($user)->get(route('dashboard'));

        $redirected->assertRedirect(route('admin.dashboard'));
    }

    public function test_plain_text_password_is_accepted_and_migrated_to_hash_on_login(): void
    {
        $adminRole = Role::create([
            'name' => 'Administrador',
            'slug' => 'administrador',
        ]);

        $user = User::create([
            'role_id' => $adminRole->id,
            'name' => 'Legacy Password User',
            'email' => 'legacy@unifranz.edu.bo',
            'password' => Hash::make('temporal-pass-123'),
            'activo' => true,
        ]);

        // Simula un dato legacy guardado en texto plano en la base.
        User::query()->whereKey($user->id)->update([
            'password' => 'plano123',
        ]);

        $response = $this->post('/login', [
            'email' => 'legacy@unifranz.edu.bo',
            'password' => 'plano123',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);

        $user->refresh();
        $this->assertNotSame('plano123', $user->password);
        $this->assertTrue(Hash::isHashed($user->password));
        $this->assertTrue(Hash::check('plano123', $user->password));
    }

    public function test_face_login_authenticates_user_when_face_is_verified(): void
    {
        Storage::fake('public');

        $studentRole = Role::create([
            'name' => 'Estudiante',
            'slug' => 'estudiante',
        ]);

        Storage::disk('public')->put('profile-photos/1/referencia.jpg', 'fake-image-content');

        $user = User::create([
            'role_id' => $studentRole->id,
            'name' => 'Ana Flores',
            'email' => 'ana@unifranz.edu.bo',
            'password' => Hash::make('password'),
            'activo' => true,
            'foto_perfil' => 'profile-photos/1/referencia.jpg',
        ]);

        $response = $this->post(route('login.face.store'), [
            'email' => $user->email,
            'remember' => '1',
            'captured_photo' => 'data:image/jpeg;base64,'.base64_encode('captured-image'),
            'face_verified' => '1',
            'face_distance' => '0.20',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_face_login_fails_when_face_is_not_verified(): void
    {
        Storage::fake('public');

        $studentRole = Role::create([
            'name' => 'Estudiante',
            'slug' => 'estudiante',
        ]);

        Storage::disk('public')->put('profile-photos/2/referencia.jpg', 'fake-image-content');

        User::create([
            'role_id' => $studentRole->id,
            'name' => 'Carlos Vega',
            'email' => 'carlos@unifranz.edu.bo',
            'password' => Hash::make('password'),
            'activo' => true,
            'foto_perfil' => 'profile-photos/2/referencia.jpg',
        ]);

        $response = $this->from('/login')->post(route('login.face.store'), [
            'email' => 'carlos@unifranz.edu.bo',
            'captured_photo' => 'data:image/jpeg;base64,'.base64_encode('captured-image'),
            'face_verified' => '0',
            'face_distance' => '0.75',
        ]);

        $response->assertRedirect('/login');
        $response->assertSessionHasErrors();
        $this->assertGuest();
    }
}
