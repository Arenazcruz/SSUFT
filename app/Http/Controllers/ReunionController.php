<?php

namespace App\Http\Controllers;

use App\Models\ParticipanteReunion;
use App\Models\Reunion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class ReunionController extends Controller
{
    public function show(Request $request, Reunion $reunion): View
    {
        if ($request->user()?->isRole('estudiante')) {
            ParticipanteReunion::firstOrCreate(
                [
                    'reunion_id' => $reunion->id,
                    'user_id' => $request->user()->id,
                ],
                [
                    'joined_at' => now(),
                ]
            );
        }

        $reunion->load([
            'docente.role',
            'participantes.user',
            'mensajes.user',
            'grabaciones',
        ]);

        return view('reuniones.show', [
            'reunion' => $reunion,
            'relatedMeetings' => Reunion::query()
                ->with('docente')
                ->whereKeyNot($reunion->id)
                ->latest('scheduled_at')
                ->take(3)
                ->get(),
        ]);
    }
}
