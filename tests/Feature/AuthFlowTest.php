<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
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
}
