<?php

namespace Database\Seeders;

use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $roles = Role::query()->pluck('id', 'slug');

        User::updateOrCreate(
            ['email' => 'superadmin@unifranz.edu.bo'],
            [
                'name' => 'Rocio Andrade',
                'role_id' => $roles['superadministrador'],
                'password' => Hash::make('password'),
                'activo' => true,
                'avatar_color' => '#0f172a',
                'email_verified_at' => now(),
            ]
        );

        User::updateOrCreate(
            ['email' => 'admin@unifranz.edu.bo'],
            [
                'name' => 'Paola Mendoza',
                'role_id' => $roles['administrador'],
                'password' => Hash::make('password'),
                'activo' => true,
                'avatar_color' => '#111827',
                'email_verified_at' => now(),
            ]
        );

        $teacherEmails = [
            'marco.siles@unifranz.edu.bo' => 'Marco Siles',
            'ana.rivera@unifranz.edu.bo' => 'Ana Rivera',
            'carlos.quispe@unifranz.edu.bo' => 'Carlos Quispe',
        ];

        foreach ($teacherEmails as $email => $name) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role_id' => $roles['docente'],
                    'password' => Hash::make('password'),
                    'activo' => true,
                    'avatar_color' => '#F57C00',
                    'email_verified_at' => now(),
                ]
            );
        }

        $studentEmails = [
            'lucia.velasco@unifranz.edu.bo' => 'Lucia Velasco',
            'diego.arias@unifranz.edu.bo' => 'Diego Arias',
            'camila.mendez@unifranz.edu.bo' => 'Camila Mendez',
            'santiago.lopez@unifranz.edu.bo' => 'Santiago Lopez',
            'alejandra.suarez@unifranz.edu.bo' => 'Alejandra Suarez',
            'mateo.gutierrez@unifranz.edu.bo' => 'Mateo Gutierrez',
        ];

        foreach ($studentEmails as $email => $name) {
            User::updateOrCreate(
                ['email' => $email],
                [
                    'name' => $name,
                    'role_id' => $roles['estudiante'],
                    'password' => Hash::make('password'),
                    'activo' => true,
                    'avatar_color' => '#EA580C',
                    'email_verified_at' => now(),
                ]
            );
        }
    }
}
