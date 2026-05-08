<?php

namespace App\Http\Controllers;

use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        return match ($request->user()?->role?->slug) {
            'superadministrador' => redirect()->route('superadmin.dashboard'),
            'administrador' => redirect()->route('admin.dashboard'),
            'docente' => redirect()->route('teacher.dashboard'),
            'estudiante' => redirect()->route('student.dashboard'),
            default => abort(403),
        };
    }
}
