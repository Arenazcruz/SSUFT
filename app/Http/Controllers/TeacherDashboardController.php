<?php

namespace App\Http\Controllers;

use App\Models\Grabacion;
use App\Models\Reunion;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class TeacherDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $teacher = $request->user();

        return view('dashboard.teacher', [
            'metrics' => [
                'total' => Reunion::where('docente_id', $teacher->id)->count(),
                'live' => Reunion::where('docente_id', $teacher->id)->where('estado', 'en_vivo')->count(),
                'recordings' => Grabacion::whereHas('reunion', fn ($query) => $query->where('docente_id', $teacher->id))->count(),
            ],
            'meetings' => Reunion::query()
                ->withCount('participantes')
                ->where('docente_id', $teacher->id)
                ->latest('scheduled_at')
                ->get(),
            'recordings' => Grabacion::query()
                ->with('reunion')
                ->whereHas('reunion', fn ($query) => $query->where('docente_id', $teacher->id))
                ->latest('published_at')
                ->take(6)
                ->get(),
        ]);
    }
}
