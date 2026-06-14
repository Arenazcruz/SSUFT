<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DemoTeacherSeeder extends Seeder
{
    public function run(): void
    {
        $teacherRole = Role::updateOrCreate(
            ['slug' => 'docente'],
            [
                'name' => 'Docente',
                'description' => 'Programa, transmite y revisa clases y grabaciones.',
            ]
        );

        User::updateOrCreate(
            ['email' => 'docente.demo@unifranz.edu.bo'],
            [
                'name' => 'Docente Demo',
                'role_id' => $teacherRole->id,
                'password' => Hash::make('Demo123456'),
                'activo' => true,
                'avatar_color' => '#F57C00',
                'email_verified_at' => now(),
            ]
        );
    }
}
