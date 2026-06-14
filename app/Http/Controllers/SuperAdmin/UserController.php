<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Http\Requests\SuperAdmin\StoreUserRequest;
use App\Http\Requests\SuperAdmin\UpdateUserRequest;
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

        return back()->with('success', 'Usuario creado correctamente desde superadministración.');
    }

    public function update(UpdateUserRequest $request, User $user): RedirectResponse
    {
        if ($this->isSuperAdministrator($user)) {
            return back()->withErrors([
                'superadmin_user' => 'No puedes modificar cuentas de superadministradores.',
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

        return back()->with('success', 'Usuario actualizado correctamente desde superadministración.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ($this->isSuperAdministrator($user)) {
            return back()->withErrors([
                'superadmin_user' => 'No puedes eliminar cuentas de superadministradores.',
            ]);
        }

        DB::table('sessions')->where('user_id', $user->id)->delete();
        $user->delete();

        return back()->with('success', 'Usuario eliminado correctamente desde superadministración.');
    }

    private function avatarPalette(): array
    {
        return ['#F57C00', '#111827', '#EA580C'];
    }

    private function isSuperAdministrator(User $user): bool
    {
        return $user->relationLoaded('role')
            ? $user->role?->slug === 'superadministrador'
            : $user->role()->where('slug', 'superadministrador')->exists();
    }
}
