<?php

namespace App\Http\Controllers;

use App\Models\Grabacion;
use App\Models\Reunion;
use App\Models\User;
use Illuminate\Contracts\View\View;

class LandingController extends Controller
{
    public function __invoke(): View
    {
        return view('landing.index', [
            'featuredMeetings' => Reunion::query()
                ->with('docente')
                ->whereIn('estado', ['en_vivo', 'programada'])
                ->orderByRaw("CASE WHEN estado = 'en_vivo' THEN 0 ELSE 1 END")
                ->orderBy('scheduled_at')
                ->take(3)
                ->get(),
            'stats' => [
                'usuarios' => User::count(),
                'reuniones' => Reunion::count(),
                'grabaciones' => Grabacion::count(),
            ],
        ]);
    }
}
