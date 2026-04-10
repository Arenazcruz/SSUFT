<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateReunionRequest;
use App\Models\Reunion;
use Illuminate\Http\RedirectResponse;

class ReunionController extends Controller
{
    public function update(UpdateReunionRequest $request, Reunion $reunion): RedirectResponse
    {
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
                    'description' => 'Grabación disponible para revisión académica.',
                    'url' => '#',
                    'published_at' => now(),
                    'duration_minutes' => $reunion->duration_minutes,
                ]
            );
        }

        $reunion->update($payload);

        return back()->with('success', 'Reunión actualizada correctamente.');
    }
}
