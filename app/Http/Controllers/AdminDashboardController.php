<?php

namespace App\Http\Controllers;

use App\Models\Grabacion;
use App\Models\Reunion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;

class AdminDashboardController extends Controller
{
    public function index(): View
    {
        return view('dashboard.admin', [
            'metrics' => [
                'usuarios' => User::count(),
                'reuniones' => Reunion::count(),
                'grabaciones' => Grabacion::count(),
                'live' => Reunion::where('estado', 'en_vivo')->count(),
            ],
            'roles' => Role::withCount('users')->get(),
            'users' => User::with('role')->latest()->get(),
            'meetings' => Reunion::with('docente')->latest('scheduled_at')->get(),
        ]);
    }
}
