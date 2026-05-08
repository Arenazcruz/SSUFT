<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::create([
            ...$request->safe()->except('password_confirmation'),
            'activo' => $request->boolean('activo', true),
            'avatar_color' => $this->avatarPalette()[$request->validated('role_id') % 3],
        ]);

        return back()->with('success', 'Usuario institucional creado correctamente.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($this->isSuperAdministrator($user)) {
            return back()->withErrors([
                'admin_user' => 'No puedes modificar cuentas de superadministradores.',
            ]);
        }

        $isAdministratorTarget = $this->isAdministrator($user);

        if ($request->user()->is($user) && ! $request->boolean('activo', false)) {
            return back()->withErrors([
                'admin_user' => 'No puedes desactivar tu propia cuenta.',
            ]);
        }

        if ($request->user()->is($user) && ! $this->isAdministratorRole((int) $request->validated('role_id'))) {
            return back()->withErrors([
                'admin_user' => 'No puedes cambiar tu propio rol de administrador.',
            ]);
        }

        if ($isAdministratorTarget && ! $this->isAdministratorRole((int) $request->validated('role_id'))) {
            return back()->withErrors([
                'admin_user' => 'No puedes cambiar el rol de otro administrador.',
            ]);
        }

        if ($isAdministratorTarget && $user->activo && ! $request->boolean('activo')) {
            return back()->withErrors([
                'admin_user' => 'No puedes desactivar cuentas de administradores.',
            ]);
        }

        if (
            $this->isActiveAdministrator($user)
            && (
                ! $this->isAdministratorRole((int) $request->validated('role_id'))
                || ! $request->boolean('activo')
            )
            && $this->activeAdministratorsCount() <= 1
        ) {
            return back()->withErrors([
                'admin_user' => 'Debe existir al menos un administrador activo en la plataforma.',
            ]);
        }

        $user->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'role_id' => $request->validated('role_id'),
            'activo' => $request->boolean('activo'),
        ]);

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        $user->save();

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($request->user()->is($user)) {
            return back()->withErrors([
                'admin_user' => 'No puedes eliminar tu propia cuenta.',
            ]);
        }

        if ($this->isSuperAdministrator($user)) {
            return back()->withErrors([
                'admin_user' => 'No puedes eliminar cuentas de superadministradores.',
            ]);
        }

        if ($this->isAdministrator($user)) {
            return back()->withErrors([
                'admin_user' => 'No puedes eliminar cuentas de administradores.',
            ]);
        }

        if ($this->isActiveAdministrator($user) && $this->activeAdministratorsCount() <= 1) {
            return back()->withErrors([
                'admin_user' => 'No puedes eliminar el último administrador activo.',
            ]);
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();

        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente.');
    }

    private function avatarPalette(): array
    {
        return ['#F57C00', '#111827', '#EA580C'];
    }

    private function isAdministratorRole(int $roleId): bool
    {
        return Role::query()
            ->whereKey($roleId)
            ->where('slug', 'administrador')
            ->exists();
    }

    private function isAdministrator(User $user): bool
    {
        return $user->relationLoaded('role')
            ? $user->role?->slug === 'administrador'
            : $user->role()->where('slug', 'administrador')->exists();
    }

    private function isSuperAdministrator(User $user): bool
    {
        return $user->relationLoaded('role')
            ? $user->role?->slug === 'superadministrador'
            : $user->role()->where('slug', 'superadministrador')->exists();
    }

    private function isActiveAdministrator(User $user): bool
    {
        if (! $user->activo) {
            return false;
        }

        return $user->relationLoaded('role')
            ? $user->role?->slug === 'administrador'
            : $user->role()->where('slug', 'administrador')->exists();
    }

    private function activeAdministratorsCount(): int
    {
        return User::query()
            ->where('activo', true)
            ->whereHas('role', fn ($query) => $query->where('slug', 'administrador'))
            ->count();
    }
}
