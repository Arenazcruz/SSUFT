<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\StoreUserRequest;
use App\Http\Requests\Admin\UpdateUserRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

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
        if ($request->user()->is($user) && ! $request->boolean('activo', false)) {
            return back()->withErrors([
                'admin_user' => 'No puedes desactivar tu propia cuenta.',
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

    private function avatarPalette(): array
    {
        return ['#F57C00', '#111827', '#EA580C'];
    }
}
