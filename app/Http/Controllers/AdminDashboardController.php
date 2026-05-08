<?php

namespace App\Http\Controllers;

use App\Models\Grabacion;
use App\Models\Reunion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class AdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $currentUserId = $request->user()?->id;

        return view('dashboard.admin', [
            'metrics' => [
                'usuarios' => User::count(),
                'reuniones' => Reunion::count(),
                'grabaciones' => Grabacion::count(),
                'live' => Reunion::where('estado', 'en_vivo')->count(),
            ],
            'roles' => Role::query()
                ->whereIn('slug', ['docente', 'estudiante'])
                ->withCount('users')
                ->get(),
            'users' => User::with('role')->latest()->get(),
            'managedUsers' => User::query()
                ->with('role')
                ->whereHas('role', fn ($query) => $query->whereIn('slug', ['docente', 'estudiante']))
                ->when($currentUserId, fn ($query) => $query->whereKeyNot($currentUserId))
                ->latest()
                ->get(),
            'meetings' => Reunion::with('docente')->latest('scheduled_at')->get(),
        ]);
    }
}
