<?php

namespace App\Http\Controllers;

use App\Models\Grabacion;
use App\Models\Reunion;
use App\Models\Role;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class SuperAdminDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $manageableRoleSlugs = ['administrador', 'docente', 'estudiante'];
        $currentUserId = $request->user()?->id;

        $roles = Role::query()
            ->whereIn('slug', $manageableRoleSlugs)
            ->withCount('users')
            ->get();

        $roleCounts = Role::query()
            ->whereIn('slug', ['superadministrador', ...$manageableRoleSlugs])
            ->withCount('users')
            ->get()
            ->keyBy('slug');

        $managedUsers = User::query()
            ->with('role')
            ->whereHas('role', fn ($query) => $query->whereIn('slug', $manageableRoleSlugs))
            ->when($currentUserId, fn ($query) => $query->whereKeyNot($currentUserId))
            ->latest()
            ->get();

        $meetings = Reunion::query()
            ->with('docente')
            ->latest('scheduled_at')
            ->get();

        $activeUsers = User::query()->where('activo', true)->count();
        $inactiveUsers = User::query()->where('activo', false)->count();
        $totalUsers = $activeUsers + $inactiveUsers;

        return view('dashboard.superadmin', [
            'metrics' => [
                'usuarios_total' => $totalUsers,
                'usuarios_activos' => $activeUsers,
                'usuarios_inactivos' => $inactiveUsers,
                'superadmins' => (int) ($roleCounts->get('superadministrador')?->users_count ?? 0),
                'admins' => (int) ($roleCounts->get('administrador')?->users_count ?? 0),
                'docentes' => (int) ($roleCounts->get('docente')?->users_count ?? 0),
                'estudiantes' => (int) ($roleCounts->get('estudiante')?->users_count ?? 0),
                'reuniones_total' => $meetings->count(),
                'reuniones_programadas' => $meetings->where('estado', 'programada')->count(),
                'reuniones_live' => $meetings->where('estado', 'en_vivo')->count(),
                'reuniones_finalizadas' => $meetings->where('estado', 'finalizada')->count(),
                'grabaciones' => Grabacion::query()->count(),
            ],
            'roles' => $roles,
            'managedUsers' => $managedUsers,
            'latestManagedUsers' => $managedUsers->take(5),
            'meetings' => $meetings,
        ]);
    }
}
