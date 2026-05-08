<?php

namespace Tests\Feature;

use App\Models\Reunion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class TeacherValidationTest extends TestCase
{
    use RefreshDatabase;

    public function test_teacher_can_store_reunion_with_sanitized_fields(): void
    {
        [$teacher] = $this->createTeacherContext();

        $response = $this->actingAs($teacher)
            ->from(route('teacher.dashboard'))
            ->post(route('teacher.reuniones.store'), [
                'title' => '  Arquitectura de Sistemas Distribuidos  ',
                'description' => '  Clase de introducción a arquitecturas resilientes.  ',
                'scheduled_at' => now()->addDay()->format('Y-m-d H:i:s'),
                'duration_minutes' => 90,
            ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHasNoErrors();
        $this->assertDatabaseHas('reuniones', [
            'docente_id' => $teacher->id,
            'title' => 'Arquitectura de Sistemas Distribuidos',
            'description' => 'Clase de introducción a arquitecturas resilientes.',
            'estado' => 'programada',
        ]);
    }

    public function test_teacher_cannot_schedule_reunion_in_the_past(): void
    {
        [$teacher] = $this->createTeacherContext();

        $response = $this->actingAs($teacher)
            ->from(route('teacher.dashboard'))
            ->post(route('teacher.reuniones.store'), [
                'title' => 'Laboratorio de datos',
                'description' => 'Sesión práctica.',
                'scheduled_at' => now()->subHour()->format('Y-m-d H:i:s'),
                'duration_minutes' => 90,
            ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHasErrors('scheduled_at');
        $this->assertDatabaseMissing('reuniones', [
            'title' => 'Laboratorio de datos',
        ]);
    }

    public function test_teacher_cannot_set_invalid_status_transition(): void
    {
        [$teacher] = $this->createTeacherContext();

        $meeting = Reunion::create([
            'docente_id' => $teacher->id,
            'title' => 'Clase finalizada',
            'description' => 'Estado final',
            'scheduled_at' => now()->subDay(),
            'estado' => 'finalizada',
            'duration_minutes' => 90,
            'access_code' => 'CLS-001',
        ]);

        $response = $this->actingAs($teacher)
            ->from(route('teacher.dashboard'))
            ->patch(route('teacher.reuniones.update', $meeting), [
                'estado' => 'en_vivo',
            ]);

        $response->assertRedirect(route('teacher.dashboard'));
        $response->assertSessionHasErrors('estado');
        $this->assertDatabaseHas('reuniones', [
            'id' => $meeting->id,
            'estado' => 'finalizada',
        ]);
    }

    public function test_teacher_cannot_update_status_of_other_teacher_reunion(): void
    {
        [$teacher] = $this->createTeacherContext();
        $otherTeacher = $this->createTeacher('docente2@unifranz.edu.bo', 'Docente Dos');

        $meeting = Reunion::create([
            'docente_id' => $otherTeacher->id,
            'title' => 'Clase externa',
            'description' => 'No editable por otro docente',
            'scheduled_at' => now()->addDay(),
            'estado' => 'programada',
            'duration_minutes' => 90,
            'access_code' => 'EXT-001',
        ]);

        $response = $this->actingAs($teacher)
            ->patch(route('teacher.reuniones.update', $meeting), [
                'estado' => 'en_vivo',
            ]);

        $response->assertForbidden();
        $this->assertDatabaseHas('reuniones', [
            'id' => $meeting->id,
            'estado' => 'programada',
        ]);
    }

    private function createTeacherContext(): array
    {
        Role::create([
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

        $teacher = $this->createTeacher('docente1@unifranz.edu.bo', 'Docente Uno');

        return [$teacher];
    }

    private function createTeacher(string $email, string $name): User
    {
        return User::create([
            'role_id' => $this->roleBySlug('docente')->id,
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

