<?php

namespace App\Http\Controllers;

use App\Models\ParticipanteReunion;
use App\Models\Reunion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ReunionController extends Controller
{
    public function show(Request $request, Reunion $reunion): View
    {
        [$user, $isTeacherOwner] = $this->resolveAuthorizedUser($request, $reunion);

        $streamAccessClosed = in_array($reunion->estado, ['finalizada', 'cancelada'], true);
        $canJoinLiveRoom = $reunion->estado === 'en_vivo' && filled($reunion->jitsi_room);

        $reunion->load([
            'docente.role',
            'participantes.user',
            'mensajes.user',
            'grabaciones',
        ]);

        return view('reuniones.show', [
            'reunion' => $reunion,
            'streamAccessClosed' => $streamAccessClosed,
            'isTeacherOwner' => $isTeacherOwner,
            'canJoinLiveRoom' => $canJoinLiveRoom,
            'liveRoomUrl' => $canJoinLiveRoom ? route('reuniones.live', $reunion) : null,
            'relatedMeetings' => Reunion::query()
                ->with('docente')
                ->whereKeyNot($reunion->id)
                ->latest('scheduled_at')
                ->take(3)
                ->get(),
        ]);
    }

    public function live(Request $request, Reunion $reunion): View|RedirectResponse
    {
        [$user, $isTeacherOwner] = $this->resolveAuthorizedUser($request, $reunion);

        $canJoinLiveRoom = $reunion->estado === 'en_vivo' && filled($reunion->jitsi_room);
        if (! $canJoinLiveRoom) {
            return redirect()
                ->route('reuniones.show', $reunion)
                ->withErrors(['reunion' => 'La videollamada no está disponible para esta clase en este momento.']);
        }

        if ($user->isRole('estudiante')) {
            ParticipanteReunion::firstOrCreate(
                [
                    'reunion_id' => $reunion->id,
                    'user_id' => $user->id,
                ],
                [
                    'joined_at' => now(),
                ]
            );
        }

        $reunion->load('docente');

        return view('reuniones.live', [
            'reunion' => $reunion,
            'isTeacherOwner' => $isTeacherOwner,
            'jitsiDomain' => (string) config('services.jitsi.domain', 'meet.jit.si'),
            'jitsiRoom' => $reunion->jitsi_room,
            'userDisplayName' => $user->name,
            'userEmail' => $user->email,
            'leaveRedirectUrl' => route('reuniones.show', $reunion),
        ]);
    }

    private function resolveAuthorizedUser(Request $request, Reunion $reunion): array
    {
        $user = $request->user();
        abort_unless($user, 403);

        abort_unless($user->isRole(['superadministrador', 'administrador', 'docente', 'estudiante']), 403);

        if ($user->isRole('docente') && $reunion->docente_id !== $user->id) {
            abort(403);
        }

        return [$user, $user->isRole('docente') && $reunion->docente_id === $user->id];
    }
}
