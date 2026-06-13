<?php

namespace App\Http\Controllers;

use App\Models\Grabacion;
use App\Models\Reunion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StudentDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();

        return view('dashboard.student', [
            'liveMeetings' => Reunion::query()
                ->with('docente')
                ->where('estado', 'en_vivo')
                ->latest('started_at')
                ->take(3)
                ->get(),
            'scheduledMeetings' => Reunion::query()
                ->with('docente')
                ->where('estado', 'programada')
                ->orderBy('scheduled_at')
                ->take(6)
                ->get(),
            'recentRecordings' => Grabacion::query()
                ->with('reunion.docente')
                ->latest('published_at')
                ->take(4)
                ->get(),
            'historyMeetings' => Reunion::query()
                ->with('docente')
                ->whereHas('participantes', fn ($query) => $query->where('user_id', $user->id))
                ->latest('scheduled_at')
                ->take(4)
                ->get(),
        ]);
    }
}
