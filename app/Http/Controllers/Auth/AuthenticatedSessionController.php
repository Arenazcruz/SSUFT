<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\FaceLoginRequest;
use App\Http\Requests\Auth\LoginRequest;
use App\Models\User;
use App\Rules\InstitutionalEmail;
use Illuminate\Contracts\View\View;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class AuthenticatedSessionController extends Controller
{
    public function create(): View
    {
        return view('auth.login');
    }

    public function store(LoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        return redirect()->intended(route('dashboard'));
    }

    public function faceReference(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'email' => ['required', 'email', new InstitutionalEmail()],
        ]);

        $email = Str::lower(trim((string) $validated['email']));

        $user = User::query()
            ->where('email', $email)
            ->where('activo', true)
            ->whereNotNull('foto_perfil')
            ->first();

        $path = $this->normalizePhotoPath($user?->foto_perfil);

        if (! $user || ! $path || ! Storage::disk('public')->exists($path)) {
            throw ValidationException::withMessages([
                'email' => 'No fue posible obtener una referencia facial para esta cuenta.',
            ]);
        }

        $mimeType = Storage::disk('public')->mimeType($path) ?: 'image/jpeg';
        $binary = Storage::disk('public')->get($path);
        $dataUrl = 'data:'.$mimeType.';base64,'.base64_encode($binary);

        return response()->json([
            'reference_photo' => $dataUrl,
        ]);
    }

    public function faceStore(FaceLoginRequest $request): RedirectResponse
    {
        $request->authenticate();

        return redirect()->intended(route('dashboard'));
    }

    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
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
