<?php

namespace Database\Seeders;

use App\Models\ChatReunion;
use App\Models\Grabacion;
use App\Models\ParticipanteReunion;
use App\Models\Reunion;
use App\Models\User;
use Illuminate\Database\Seeder;

class ReunionSeeder extends Seeder
{
    public function run(): void
    {
        $teachers = User::query()->whereHas('role', fn ($query) => $query->where('slug', 'docente'))->get();
        $students = User::query()->whereHas('role', fn ($query) => $query->where('slug', 'estudiante'))->get();

        if ($teachers->isEmpty()) {
            return;
        }

        $meetings = [
            [
                'title' => 'Arquitectura de Software Distribuido',
                'description' => 'Sesión sobre patrones de comunicación síncrona y diseño de servicios universitarios.',
                'scheduled_at' => now()->addHour(),
                'estado' => 'programada',
                'duration_minutes' => 90,
                'access_code' => 'ARQ101',
                'cover_gradient' => 'from-orange-500 via-amber-500 to-zinc-900',
            ],
            [
                'title' => 'Laboratorio de UX para Plataformas Educativas',
                'description' => 'Clase en vivo con revisión de wireframes, heurísticas y experiencia de usuario académica.',
                'scheduled_at' => now()->subMinutes(25),
                'started_at' => now()->subMinutes(18),
                'estado' => 'en_vivo',
                'duration_minutes' => 120,
                'access_code' => 'UX202',
                'cover_gradient' => 'from-zinc-900 via-orange-600 to-orange-400',
            ],
            [
                'title' => 'Analítica de Aprendizaje y Métricas',
                'description' => 'Exploración de indicadores para seguimiento de cohortes y asistencia.',
                'scheduled_at' => now()->subDays(1),
                'started_at' => now()->subDays(1)->addMinutes(5),
                'ended_at' => now()->subDays(1)->addMinutes(95),
                'estado' => 'finalizada',
                'duration_minutes' => 100,
                'access_code' => 'DAT310',
                'cover_gradient' => 'from-slate-950 via-zinc-800 to-orange-500',
            ],
            [
                'title' => 'Cloud Computing para Ingeniería',
                'description' => 'Clase magistral sobre despliegue, observabilidad y seguridad en servicios cloud.',
                'scheduled_at' => now()->addDays(1)->setTime(19, 0),
                'estado' => 'programada',
                'duration_minutes' => 80,
                'access_code' => 'CLD220',
                'cover_gradient' => 'from-orange-500 via-zinc-700 to-slate-950',
            ],
            [
                'title' => 'Producción Audiovisual para Streaming Académico',
                'description' => 'Buenas prácticas para iluminación, audio y guion técnico en clases remotas.',
                'scheduled_at' => now()->subDays(3),
                'started_at' => now()->subDays(3)->addMinutes(2),
                'ended_at' => now()->subDays(3)->addMinutes(92),
                'estado' => 'finalizada',
                'duration_minutes' => 90,
                'access_code' => 'MED115',
                'cover_gradient' => 'from-zinc-950 via-zinc-800 to-orange-500',
            ],
        ];

        foreach ($meetings as $index => $meetingData) {
            $meeting = Reunion::updateOrCreate(
                ['title' => $meetingData['title']],
                [
                    ...$meetingData,
                    'docente_id' => $teachers[$index % $teachers->count()]->id,
                ]
            );

            foreach ($students->take(4) as $offset => $student) {
                ParticipanteReunion::updateOrCreate(
                    [
                        'reunion_id' => $meeting->id,
                        'user_id' => $student->id,
                    ],
                    [
                        'joined_at' => $meeting->scheduled_at?->copy()->addMinutes(5 + ($offset * 2)),
                    ]
                );
            }

            if (in_array($meeting->estado, ['en_vivo', 'finalizada'], true)) {
                foreach ($students->take(3) as $student) {
                    ChatReunion::updateOrCreate(
                        [
                            'reunion_id' => $meeting->id,
                            'user_id' => $student->id,
                            'message' => 'Consulta sobre el tema visto en clase.',
                        ],
                        [
                            'sent_at' => now()->subMinutes(rand(3, 20)),
                        ]
                    );
                }
            }

            if ($meeting->estado === 'finalizada') {
                Grabacion::updateOrCreate(
                    ['reunion_id' => $meeting->id],
                    [
                        'title' => 'Grabación - '.$meeting->title,
                        'description' => 'Disponible para revisión asíncrona y repaso de contenidos.',
                        'url' => '#',
                        'published_at' => now()->subHours(8),
                        'duration_minutes' => $meeting->duration_minutes,
                    ]
                );
            }
        }
    }
}
