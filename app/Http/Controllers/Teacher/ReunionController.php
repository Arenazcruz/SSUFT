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

        $reunion = Reunion::create([
            'docente_id' => $request->user()->id,
            'title' => $request->validated('title'),
            'description' => $request->validated('description'),
            'scheduled_at' => $request->validated('scheduled_at'),
            'duration_minutes' => $request->validated('duration_minutes'),
            'estado' => 'programada',
            'access_code' => Str::upper(Str::random(6)),
            'cover_gradient' => $gradients[array_rand($gradients)],
        ]);
        $reunion->jitsi_room = $this->generateJitsiRoom($reunion);
        $reunion->save();

        return back()->with('success', 'La transmisión fue programada correctamente.');
    }

    public function update(UpdateReunionStatusRequest $request, Reunion $reunion): RedirectResponse
    {
        abort_unless($reunion->docente_id === $request->user()->id, 403);

        $estado = $request->validated('estado');
        $payload = ['estado' => $estado];
        $message = 'El estado de la clase fue actualizado.';

        if ($estado === 'en_vivo' && ! $reunion->started_at) {
            $payload['started_at'] = now();
            $payload['ended_at'] = null;
            $message = 'La transmisión en vivo se inició correctamente.';
        }

        if ($estado === 'en_vivo' && ! $reunion->jitsi_room) {
            $payload['jitsi_room'] = $this->generateJitsiRoom($reunion);
        }

        if ($estado === 'finalizada') {
            $payload['ended_at'] = now();
            $payload['started_at'] = $reunion->started_at ?? now();

            $reunion->grabaciones()->firstOrCreate(
                ['title' => 'Grabación - '.$reunion->title],
                [
                    'description' => 'Grabación generada automáticamente al finalizar la sesión.',
                    'url' => '#',
                    'published_at' => now(),
                    'duration_minutes' => $reunion->duration_minutes,
                ]
            );

            $message = 'La transmisión fue finalizada y la sesión quedó cerrada.';
        }

        if ($estado === 'cancelada') {
            $payload['started_at'] = null;
            $payload['ended_at'] = now();
            $message = 'La transmisión programada fue cancelada.';
        }

        $reunion->update($payload);

        return back()->with('success', $message);
    }

    public function destroy(Request $request, Reunion $reunion): RedirectResponse
    {
        abort_unless($reunion->docente_id === $request->user()->id, 403);

        if ($reunion->estado !== 'programada') {
            return back()->withErrors([
                'estado' => 'Solo se pueden cancelar transmisiones en estado programada.',
            ]);
        }

        $reunion->update([
            'estado' => 'cancelada',
            'started_at' => null,
            'ended_at' => now(),
        ]);

        return back()->with('success', 'La transmisión programada fue cancelada.');
    }

    private function generateJitsiRoom(Reunion $reunion): string
    {
        $accessCode = strtoupper(preg_replace('/[^A-Za-z0-9]/', '', (string) ($reunion->access_code ?? ''))) ?: 'NOACCESS';
        $base = 'SSUFT-'.$reunion->id.'-'.$accessCode;

        do {
            $suffix = strtoupper(Str::random(8));
            $room = $base.'-'.$suffix;
            $exists = Reunion::query()
                ->where('jitsi_room', $room)
                ->whereKeyNot($reunion->id)
                ->exists();
        } while ($exists);

        return $room;
    }
}
