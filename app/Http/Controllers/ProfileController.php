<?php

namespace App\Http\Controllers;

use App\Http\Requests\Profile\UpdateProfileRequest;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user();

        return view('profile.edit', [
            'user' => $user,
            'photoUrl' => $user->foto_perfil
                ? route('profile.photo', ['v' => optional($user->updated_at)->timestamp])
                : null,
            'avatarColors' => ['#F57C00', '#111827', '#EA580C', '#0EA5E9', '#16A34A', '#7C3AED'],
        ]);
    }

    public function photo(Request $request): StreamedResponse
    {
        $user = $request->user();
        $path = $this->normalizePhotoPath($user?->foto_perfil);

        abort_if(! $path || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    public function userPhoto(Request $request, User $user): StreamedResponse
    {
        $viewer = $request->user();

        abort_unless($viewer && ($viewer->is($user) || $viewer->isRole('administrador')), 403);

        $path = $this->normalizePhotoPath($user->foto_perfil);

        abort_if(! $path || ! Storage::disk('public')->exists($path), 404);

        return Storage::disk('public')->response($path);
    }

    public function update(UpdateProfileRequest $request): RedirectResponse
    {
        $user = $request->user();

        $user->fill([
            'name' => $request->validated('name'),
            'email' => $request->validated('email'),
            'avatar_color' => $request->validated('avatar_color'),
        ]);

        if ($request->filled('password')) {
            $user->password = $request->validated('password');
        }

        if ($request->boolean('remove_photo')) {
            $this->deleteCurrentPhoto($user->foto_perfil);
            $user->foto_perfil = null;
        }

        if ($request->filled('captured_photo')) {
            $this->deleteCurrentPhoto($user->foto_perfil);
            $user->foto_perfil = $this->storeCapturedPhoto($request->validated('captured_photo'), $user->id);
        } elseif ($request->hasFile('foto_perfil')) {
            $this->deleteCurrentPhoto($user->foto_perfil);
            $user->foto_perfil = $request->file('foto_perfil')->store('profile-photos/'.$user->id, 'public');
        }

        $user->save();

        return back()->with('success', 'Perfil actualizado correctamente.');
    }

    private function storeCapturedPhoto(string $dataUrl, int $userId): string
    {
        if (! preg_match('/^data:image\/(png|jpeg|jpg|webp);base64,(.+)$/', $dataUrl, $matches)) {
            throw ValidationException::withMessages([
                'captured_photo' => 'La captura de cámara no es válida.',
            ]);
        }

        $base64 = str_replace(' ', '+', $matches[2]);
        $binary = base64_decode($base64, true);

        if ($binary === false) {
            throw ValidationException::withMessages([
                'captured_photo' => 'No se pudo procesar la imagen capturada.',
            ]);
        }

        if (strlen($binary) > (4 * 1024 * 1024)) {
            throw ValidationException::withMessages([
                'captured_photo' => 'La captura supera el tamaño máximo permitido de 4MB.',
            ]);
        }

        $extension = $matches[1] === 'jpeg' ? 'jpg' : $matches[1];
        $path = 'profile-photos/'.$userId.'/captura-'.now()->format('YmdHis').'-'.Str::lower(Str::random(10)).'.'.$extension;

        Storage::disk('public')->put($path, $binary);

        return $path;
    }

    private function deleteCurrentPhoto(?string $path): void
    {
        $path = $this->normalizePhotoPath($path);

        if (! $path) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function normalizePhotoPath(?string $path): ?string
    {
        if (! $path) {
            return null;
        }

        $normalized = str_replace('\\', '/', trim($path));
        $normalized = ltrim($normalized, '/');

        if (str_starts_with($normalized, 'storage/')) {
            $normalized = substr($normalized, strlen('storage/'));
        }

        return $normalized !== '' ? $normalized : null;
    }
}
