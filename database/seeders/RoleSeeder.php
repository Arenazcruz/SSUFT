<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'name' => 'Administrador',
                'slug' => 'administrador',
                'description' => 'Gestiona usuarios, reuniones y el gobierno general de la plataforma.',
            ],
            [
                'name' => 'Docente',
                'slug' => 'docente',
                'description' => 'Programa, transmite y revisa clases y grabaciones.',
            ],
            [
                'name' => 'Estudiante',
                'slug' => 'estudiante',
                'description' => 'Accede a clases en vivo, programadas y grabaciones.',
            ],
        ];

        foreach ($roles as $role) {
            Role::updateOrCreate(['slug' => $role['slug']], $role);
        }
    }
}
