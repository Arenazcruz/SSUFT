<?php

namespace App\Http\Controllers\Teacher;

use App\Http\Controllers\Controller;
use App\Http\Requests\Teacher\StoreReunionRequest;
use App\Http\Requests\Teacher\UpdateReunionStatusRequest;
use App\Models\Reunion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ReunionController extends Controller
{
    public function store(StoreReunionRequest $request): RedirectResponse
    {
        $gradients = [
            'from-orange-500 via-amber-500 to-zinc-900',
            'from-zinc-900 via-orange-600 to-orange-400',
            'from-slate-950 via-zinc-800 to-orange-500',
        ];

        Reunion::create([
            'docente_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'scheduled_at' => $request->validated('scheduled_at'),
            'duration_minutes' => $request->validated('duration_minutes'),
            'estado' => 'programada',
            'access_code' => Str::upper(Str::random(6)),
            'cover_gradient' => $gradients[array_rand($gradients)],
        ]);

        return back()->with('success', 'La clase fue programada correctamente.');
    }

    public function update(UpdateReunionStatusRequest $request, Reunion $reunion): RedirectResponse
    {
        abort_unless($reunion->docente_id === $request->user()->id, 403);

        $estado = $request->validated('estado');
        $payload = ['estado' => $estado];

        if ($estado === 'en_vivo' && ! $reunion->started_at) {
            $payload['started_at'] = now();
        }

        if ($estado === 'finalizada') {
            $payload['ended_at'] = now();

            $reunion->grabaciones()->firstOrCreate(
                ['title' => 'Grabación - '.$reunion->title],
                [
                    'description' => 'Grabación generada automáticamente al finalizar la sesión.',
                    'url' => '#',
                    'published_at' => now(),
                    'duration_minutes' => $reunion->duration_minutes,
                ]
            );
        }

        $reunion->update($payload);

        return back()->with('success', 'El estado de la clase fue actualizado.');
    }

    public function destroy(Request $request, Reunion $reunion): RedirectResponse
    {
        abort_unless($reunion->docente_id === $request->user()->id, 403);

        $reunion->delete();

        return back()->with('success', 'La reunion fue eliminada correctamente.');
    }
}
