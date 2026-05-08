# README2 - Codigo de foto de perfil y deteccion facial

Este documento contiene el codigo actualmente usado en el proyecto para:
- Gestionar foto de perfil (subida, captura por camara, guardado y visualizacion).
- Login con deteccion/comparacion facial.
## routes/web.php

```php
<?php

use App\Http\Controllers\Admin\ReunionController as AdminReunionController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\AdminDashboardController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LandingController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\ReunionController;
use App\Http\Controllers\StudentDashboardController;
use App\Http\Controllers\Teacher\ReunionController as TeacherReunionController;
use App\Http\Controllers\TeacherDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', LandingController::class)->name('landing');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
    Route::post('/login/face-reference', [AuthenticatedSessionController::class, 'faceReference'])->name('login.face.reference');
    Route::post('/login/face', [AuthenticatedSessionController::class, 'faceStore'])->name('login.face.store');

    Route::get('/forgot-password', [PasswordResetLinkController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetLinkController::class, 'store'])->name('password.email');

    Route::get('/reset-password/{token}', [NewPasswordController::class, 'create'])->name('password.reset');
    Route::post('/reset-password', [NewPasswordController::class, 'store'])->name('password.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/reuniones/{reunion}', [ReunionController::class, 'show'])->name('reuniones.show');
    Route::get('/perfil', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::get('/perfil/foto', [ProfileController::class, 'photo'])->name('profile.photo');
    Route::get('/usuarios/{user}/foto', [ProfileController::class, 'userPhoto'])->name('users.photo');
    Route::patch('/perfil', [ProfileController::class, 'update'])->name('profile.update');

    Route::middleware('role:estudiante')->group(function (): void {
        Route::get('/estudiante/dashboard', [StudentDashboardController::class, 'index'])->name('student.dashboard');
    });

    Route::middleware('role:docente')->group(function (): void {
        Route::get('/docente/dashboard', [TeacherDashboardController::class, 'index'])->name('teacher.dashboard');
        Route::post('/docente/reuniones', [TeacherReunionController::class, 'store'])->name('teacher.reuniones.store');
        Route::patch('/docente/reuniones/{reunion}', [TeacherReunionController::class, 'update'])->name('teacher.reuniones.update');
        Route::delete('/docente/reuniones/{reunion}', [TeacherReunionController::class, 'destroy'])->name('teacher.reuniones.destroy');
    });

    Route::middleware('role:administrador')->group(function (): void {
        Route::get('/administrador/dashboard', [AdminDashboardController::class, 'index'])->name('admin.dashboard');
        Route::post('/administrador/usuarios', [AdminUserController::class, 'store'])->name('admin.users.store');
        Route::patch('/administrador/usuarios/{user}', [AdminUserController::class, 'update'])->name('admin.users.update');
        Route::delete('/administrador/usuarios/{user}', [AdminUserController::class, 'destroy'])->name('admin.users.destroy');
        Route::patch('/administrador/reuniones/{reunion}', [AdminReunionController::class, 'update'])->name('admin.reuniones.update');
        Route::delete('/administrador/reuniones/{reunion}', [AdminReunionController::class, 'destroy'])->name('admin.reuniones.destroy');
    });
});
```

## app/Models/User.php

```php
<?php

namespace App\Models;

use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuarios';

    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'activo',
        'avatar_color',
        'foto_perfil',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'activo' => 'boolean',
        ];
    }

    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function reunionesComoDocente(): HasMany
    {
        return $this->hasMany(Reunion::class, 'docente_id');
    }

    public function participaciones(): HasMany
    {
        return $this->hasMany(ParticipanteReunion::class, 'user_id');
    }

    public function mensajes(): HasMany
    {
        return $this->hasMany(ChatReunion::class, 'user_id');
    }

    public function isRole(string|array $roles): bool
    {
        $roles = is_array($roles) ? $roles : [$roles];

        return in_array($this->role?->slug, $roles, true);
    }
}
```

## app/Http/Controllers/ProfileController.php

```php
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
```

## app/Http/Controllers/Auth/AuthenticatedSessionController.php

```php
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
```

## app/Http/Requests/Profile/UpdateProfileRequest.php

```php
<?php

namespace App\Http\Requests\Profile;

use App\Rules\InstitutionalEmail;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UpdateProfileRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user() !== null;
    }

    public function rules(): array
    {
        $userId = $this->user()?->id;

        return [
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:255', 'unique:usuarios,email,'.$userId, new InstitutionalEmail()],
            'avatar_color' => ['nullable', 'regex:/^#[A-Fa-f0-9]{6}$/'],
            'foto_perfil' => ['nullable', 'file', 'mimes:jpg,jpeg,png,webp', 'max:4096'],
            'captured_photo' => ['nullable', 'string', 'starts_with:data:image/'],
            'remove_photo' => ['nullable', 'boolean'],
            'password' => ['nullable', 'confirmed', Password::min(8)],
        ];
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }
}
```

## app/Http/Requests/Auth/FaceLoginRequest.php

```php
<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Rules\InstitutionalEmail;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class FaceLoginRequest extends FormRequest
{
    private const FACE_DISTANCE_THRESHOLD = 0.48;

    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'email', new InstitutionalEmail()],
            'remember' => ['nullable', 'boolean'],
            'captured_photo' => ['required', 'string', 'starts_with:data:image/'],
            'face_verified' => ['required', 'accepted'],
            'face_distance' => ['required', 'numeric', 'min:0', 'max:1'],
        ];
    }

    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        $email = $this->string('email')->lower()->toString();

        $user = User::query()
            ->where('email', $email)
            ->where('activo', true)
            ->whereNotNull('foto_perfil')
            ->first();

        if (! $user || (float) $this->input('face_distance') > self::FACE_DISTANCE_THRESHOLD) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'No fue posible validar la identidad facial. Intenta de nuevo.',
            ]);
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
        $this->session()->regenerate();
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'email' => Str::lower(trim((string) $this->input('email'))),
        ]);
    }

    protected function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => "Demasiados intentos de validación facial. Intenta nuevamente en {$seconds} segundos.",
        ]);
    }

    protected function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')->toString()).'|'.$this->ip().'|face');
    }
}
```

## resources/views/profile/edit.blade.php

```blade
@extends('layouts.app', [
    'title' => 'Editar perfil | UNIFRANZ Stream',
    'eyebrow' => 'Cuenta',
    'pageTitle' => 'Personaliza tu perfil',
    'pageDescription' => 'Actualiza tus datos y foto de perfil desde tu cuenta.',
])

@section('content')
    @php
        $currentColor = old('avatar_color', $user->avatar_color ?? '#F57C00');
        $initials = strtoupper(substr($user->name ?? 'UF', 0, 2));
    @endphp

    <form action="{{ route('profile.update') }}" method="POST" enctype="multipart/form-data" class="grid gap-6 xl:grid-cols-[1.2fr_0.8fr]">
        @csrf
        @method('PATCH')

        <section class="surface-panel p-7">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Datos de perfil</p>
            <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Información personal</h2>

            <div class="mt-6 grid gap-5">
                <label>
                    <span class="field-label">Nombre completo</span>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" class="field-input" maxlength="120" required>
                </label>

                <label>
                    <span class="field-label">Correo institucional</span>
                    <input type="email" name="email" value="{{ old('email', $user->email) }}" class="field-input" required>
                </label>

                <div>
                    <span class="field-label">Color de avatar</span>
                    <div class="flex flex-wrap items-center gap-3">
                        <input type="color" id="avatarColorInput" name="avatar_color" value="{{ $currentColor }}" class="h-11 w-14 cursor-pointer rounded-xl border border-slate-200 bg-white p-1">
                        @foreach (['#F57C00', '#111827', '#EA580C', '#0EA5E9', '#16A34A', '#7C3AED'] as $presetColor)
                            <button type="button" class="preset-color h-8 w-8 rounded-full border border-white shadow" data-color="{{ $presetColor }}" style="background-color: {{ $presetColor }}"></button>
                        @endforeach
                    </div>
                </div>
            </div>

            <hr class="my-7 border-slate-200">

            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Seguridad</p>
            <h3 class="mt-3 text-xl font-semibold text-[#111111]">Cambiar contraseña (opcional)</h3>
            <div class="mt-6 grid gap-5 md:grid-cols-2">
                <label>
                    <span class="field-label">Nueva contraseña</span>
                    <input type="password" name="password" class="field-input" autocomplete="new-password">
                </label>
                <label>
                    <span class="field-label">Confirmar contraseña</span>
                    <input type="password" name="password_confirmation" class="field-input" autocomplete="new-password">
                </label>
            </div>

            <div class="mt-7">
                <button type="submit" class="btn-primary">Guardar cambios</button>
            </div>
        </section>

        <section class="surface-panel p-7">
            <p class="text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">Foto de perfil</p>
            <h2 class="mt-3 text-2xl font-semibold text-[#111111]">Subir o tomar foto</h2>

            <div class="mt-6 rounded-3xl border border-slate-200 bg-slate-50 p-6">
                <div class="mx-auto grid h-40 w-40 place-items-center overflow-hidden rounded-3xl border border-slate-200 bg-white">
                    <img id="profilePreview" src="{{ $photoUrl ?? '' }}" alt="Vista previa de perfil" class="h-full w-full object-cover {{ $photoUrl ? '' : 'hidden' }}">
                    <span id="initialPreview" class="text-3xl font-bold text-white {{ $photoUrl ? 'hidden' : '' }}" style="background-color: {{ $currentColor }}; width: 100%; height: 100%; display: grid; place-items: center;">
                        {{ $initials }}
                    </span>
                </div>

                <label class="mt-5 block">
                    <span class="field-label">Subir imagen desde tu equipo</span>
                    <input id="photoInput" type="file" name="foto_perfil" accept="image/png,image/jpeg,image/webp" class="field-input file:mr-3 file:rounded-full file:border-0 file:bg-orange-100 file:px-4 file:py-2 file:text-sm file:font-semibold file:text-orange-700">
                </label>

                <input type="hidden" id="capturedPhotoInput" name="captured_photo" value="">

                <div class="mt-5 rounded-2xl border border-slate-200 bg-white p-4">
                    <p class="text-sm font-semibold text-slate-900">Tomar foto con cámara</p>
                    <p class="mt-1 text-xs text-slate-500">Permite usar la cámara de tu laptop/computadora para capturar la imagen.</p>

                    <video id="cameraStream" class="mt-4 hidden w-full rounded-2xl border border-slate-200" autoplay playsinline></video>
                    <canvas id="cameraCanvas" class="hidden"></canvas>

                    <div class="mt-4 flex flex-wrap gap-3">
                        <button id="startCameraBtn" type="button" class="btn-secondary">Activar cámara</button>
                        <button id="capturePhotoBtn" type="button" class="btn-primary hidden">Capturar foto</button>
                        <button id="stopCameraBtn" type="button" class="btn-secondary hidden">Cerrar cámara</button>
                    </div>
                </div>

                <label class="mt-5 inline-flex items-center gap-2 text-sm text-slate-600">
                    <input type="checkbox" id="removePhotoCheckbox" name="remove_photo" value="1" @checked(old('remove_photo'))>
                    Eliminar foto actual
                </label>
            </div>
        </section>
    </form>

    <script>
        (function () {
            const profilePreview = document.getElementById('profilePreview');
            const initialPreview = document.getElementById('initialPreview');
            const photoInput = document.getElementById('photoInput');
            const capturedPhotoInput = document.getElementById('capturedPhotoInput');
            const removePhotoCheckbox = document.getElementById('removePhotoCheckbox');
            const avatarColorInput = document.getElementById('avatarColorInput');
            const presetColorButtons = document.querySelectorAll('.preset-color');

            const cameraStream = document.getElementById('cameraStream');
            const cameraCanvas = document.getElementById('cameraCanvas');
            const startCameraBtn = document.getElementById('startCameraBtn');
            const capturePhotoBtn = document.getElementById('capturePhotoBtn');
            const stopCameraBtn = document.getElementById('stopCameraBtn');

            let mediaStream = null;

            const showPreviewImage = (src) => {
                profilePreview.src = src;
                profilePreview.classList.remove('hidden');
                initialPreview.classList.add('hidden');
            };

            const showInitialAvatar = () => {
                profilePreview.classList.add('hidden');
                initialPreview.classList.remove('hidden');
                initialPreview.style.backgroundColor = avatarColorInput.value || '#F57C00';
            };

            avatarColorInput.addEventListener('input', () => {
                if (profilePreview.classList.contains('hidden')) {
                    showInitialAvatar();
                }
            });

            presetColorButtons.forEach((button) => {
                button.addEventListener('click', () => {
                    avatarColorInput.value = button.dataset.color;
                    if (profilePreview.classList.contains('hidden')) {
                        showInitialAvatar();
                    }
                });
            });

            photoInput.addEventListener('change', (event) => {
                const file = event.target.files && event.target.files[0] ? event.target.files[0] : null;

                if (!file) {
                    return;
                }

                const reader = new FileReader();
                reader.onload = () => {
                    showPreviewImage(reader.result);
                };
                reader.readAsDataURL(file);
                capturedPhotoInput.value = '';
                removePhotoCheckbox.checked = false;
            });

            removePhotoCheckbox.addEventListener('change', () => {
                if (removePhotoCheckbox.checked) {
                    capturedPhotoInput.value = '';
                    photoInput.value = '';
                    showInitialAvatar();
                }
            });

            const startCamera = async () => {
                if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                    return;
                }

                mediaStream = await navigator.mediaDevices.getUserMedia({ video: true });
                cameraStream.srcObject = mediaStream;
                cameraStream.classList.remove('hidden');
                capturePhotoBtn.classList.remove('hidden');
                stopCameraBtn.classList.remove('hidden');
                startCameraBtn.classList.add('hidden');
            };

            const stopCamera = () => {
                if (mediaStream) {
                    mediaStream.getTracks().forEach((track) => track.stop());
                    mediaStream = null;
                }

                cameraStream.srcObject = null;
                cameraStream.classList.add('hidden');
                capturePhotoBtn.classList.add('hidden');
                stopCameraBtn.classList.add('hidden');
                startCameraBtn.classList.remove('hidden');
            };

            startCameraBtn.addEventListener('click', async () => {
                try {
                    await startCamera();
                } catch (error) {
                    console.error(error);
                }
            });

            capturePhotoBtn.addEventListener('click', () => {
                if (!mediaStream) {
                    return;
                }

                const width = cameraStream.videoWidth || 640;
                const height = cameraStream.videoHeight || 480;
                cameraCanvas.width = width;
                cameraCanvas.height = height;

                const context = cameraCanvas.getContext('2d');
                context.drawImage(cameraStream, 0, 0, width, height);

                const dataUrl = cameraCanvas.toDataURL('image/jpeg', 0.92);
                capturedPhotoInput.value = dataUrl;
                photoInput.value = '';
                removePhotoCheckbox.checked = false;
                showPreviewImage(dataUrl);
                stopCamera();
            });

            stopCameraBtn.addEventListener('click', stopCamera);
            window.addEventListener('beforeunload', stopCamera);
        })();
    </script>
@endsection
```

## resources/views/auth/login.blade.php

```blade
@extends('layouts.auth', ['title' => 'Iniciar sesión | UNIFRANZ Stream'])

@section('content')
    <span class="eyebrow">Acceso institucional</span>
    <h2 class="mt-5 text-3xl font-semibold text-[#111111]">Iniciar sesión</h2>
    <p class="mt-3 text-sm leading-7 text-slate-500">
        Puedes entrar con reconocimiento facial usando tu foto de perfil registrada.
    </p>

    <form id="faceLoginForm" action="{{ route('login.face.store') }}" method="POST" class="mt-8 space-y-5">
        @csrf
        <input type="hidden" name="captured_photo" id="capturedPhotoInput" value="">
        <input type="hidden" name="face_verified" id="faceVerifiedInput" value="0">
        <input type="hidden" name="face_distance" id="faceDistanceInput" value="">

        <div>
            <label for="face_email" class="field-label">Correo institucional</label>
            <input id="face_email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   pattern="^[A-Za-z0-9._%+-]+@unifranz\.edu\.bo$" data-institutional-email
                   class="field-input" placeholder="usuario@unifranz.edu.bo">
        </div>

        <div class="rounded-[24px] border border-slate-200 bg-slate-50 p-4">
            <p class="text-sm font-semibold text-slate-900">Verificación con cámara</p>
            <p class="mt-1 text-xs text-slate-500">
                Se comparan rasgos faciales de la cámara contra la foto registrada en tu perfil.
            </p>

            <video id="faceCameraStream" class="mt-4 hidden w-full rounded-2xl border border-slate-200" autoplay playsinline></video>
            <canvas id="faceCameraCanvas" class="hidden"></canvas>

            <img id="faceCapturedPreview" alt="Captura facial" class="mt-4 hidden w-full rounded-2xl border border-slate-200 object-cover">

            <div class="mt-4 flex flex-wrap gap-3">
                <button id="startFaceCameraBtn" type="button" class="btn-secondary">Activar cámara</button>
                <button id="captureFacePhotoBtn" type="button" class="btn-primary hidden">Capturar rostro</button>
                <button id="stopFaceCameraBtn" type="button" class="btn-secondary hidden">Cerrar cámara</button>
                <button id="verifyFaceBtn" type="button" class="btn-secondary">Verificar identidad</button>
            </div>

            <p id="faceStatusMessage" class="mt-3 text-sm text-slate-600">
                Captura tu rostro y luego presiona "Verificar identidad".
            </p>
        </div>

        <label class="flex items-center gap-3 text-sm text-slate-500">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-200" {{ old('remember') ? 'checked' : '' }}>
            Recordar sesión en este dispositivo
        </label>

        <button id="faceLoginSubmitBtn" type="submit" class="btn-primary w-full" disabled>Entrar con rostro</button>
    </form>

    <div class="my-7 border-t border-slate-200"></div>

    <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Acceso alternativo</p>
    <form action="{{ route('login.store') }}" method="POST" class="mt-4 space-y-5">
        @csrf
        <div>
            <label for="password_email" class="field-label">Correo institucional</label>
            <input id="password_email" name="email" type="email" value="{{ old('email') }}" required autocomplete="email"
                   pattern="^[A-Za-z0-9._%+-]+@unifranz\.edu\.bo$" data-institutional-email
                   class="field-input" placeholder="usuario@unifranz.edu.bo">
        </div>

        <div>
            <div class="mb-2 flex items-center justify-between">
                <label for="password_login" class="field-label mb-0">Contraseña</label>
                <a href="{{ route('password.request') }}" class="text-sm font-semibold text-orange-600">¿La olvidaste?</a>
            </div>
            <input id="password_login" name="password" type="password" required autocomplete="current-password"
                   class="field-input" placeholder="Tu contraseña">
        </div>

        <label class="flex items-center gap-3 text-sm text-slate-500">
            <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-200" {{ old('remember') ? 'checked' : '' }}>
            Recordar sesión en este dispositivo
        </label>

        <button type="submit" class="btn-secondary w-full">Entrar con contraseña</button>
    </form>

    <script defer src="https://cdn.jsdelivr.net/npm/face-api.js@0.22.2/dist/face-api.min.js"></script>
    <script>
        (function () {
            const form = document.getElementById('faceLoginForm');
            const emailInput = document.getElementById('face_email');
            const capturedPhotoInput = document.getElementById('capturedPhotoInput');
            const faceVerifiedInput = document.getElementById('faceVerifiedInput');
            const faceDistanceInput = document.getElementById('faceDistanceInput');
            const statusMessage = document.getElementById('faceStatusMessage');
            const submitBtn = document.getElementById('faceLoginSubmitBtn');

            const video = document.getElementById('faceCameraStream');
            const canvas = document.getElementById('faceCameraCanvas');
            const preview = document.getElementById('faceCapturedPreview');
            const startBtn = document.getElementById('startFaceCameraBtn');
            const captureBtn = document.getElementById('captureFacePhotoBtn');
            const stopBtn = document.getElementById('stopFaceCameraBtn');
            const verifyBtn = document.getElementById('verifyFaceBtn');

            const modelUrl = 'https://justadudewhohacks.github.io/face-api.js/models';
            const maxDistance = 0.48;
            let stream = null;
            let modelsReady = null;

            const setStatus = (message, isError = false) => {
                statusMessage.textContent = message;
                statusMessage.classList.toggle('text-red-600', isError);
                statusMessage.classList.toggle('text-emerald-700', !isError);
            };

            const resetVerificationState = () => {
                faceVerifiedInput.value = '0';
                faceDistanceInput.value = '';
                submitBtn.disabled = true;
            };

            const ensureModels = async () => {
                if (!modelsReady) {
                    modelsReady = Promise.all([
                        faceapi.nets.tinyFaceDetector.loadFromUri(modelUrl),
                        faceapi.nets.faceLandmark68Net.loadFromUri(modelUrl),
                        faceapi.nets.faceRecognitionNet.loadFromUri(modelUrl),
                    ]);
                }

                return modelsReady;
            };

            const getDescriptor = async (imageSource) => {
                const detection = await faceapi
                    .detectSingleFace(imageSource, new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 }))
                    .withFaceLandmarks()
                    .withFaceDescriptor();

                return detection ? detection.descriptor : null;
            };

            const dataUrlToImage = (dataUrl) => new Promise((resolve, reject) => {
                const img = new Image();
                img.onload = () => resolve(img);
                img.onerror = () => reject(new Error('No se pudo cargar la imagen para comparar.'));
                img.src = dataUrl;
            });

            const stopCamera = () => {
                if (stream) {
                    stream.getTracks().forEach((track) => track.stop());
                    stream = null;
                }

                video.srcObject = null;
                video.classList.add('hidden');
                captureBtn.classList.add('hidden');
                stopBtn.classList.add('hidden');
                startBtn.classList.remove('hidden');
            };

            startBtn.addEventListener('click', async () => {
                try {
                    stream = await navigator.mediaDevices.getUserMedia({ video: true });
                    video.srcObject = stream;
                    video.classList.remove('hidden');
                    captureBtn.classList.remove('hidden');
                    stopBtn.classList.remove('hidden');
                    startBtn.classList.add('hidden');
                    setStatus('Cámara activa. Presiona "Capturar rostro".');
                } catch (error) {
                    setStatus('No se pudo acceder a la cámara.', true);
                }
            });

            captureBtn.addEventListener('click', () => {
                if (!stream) {
                    setStatus('Activa la cámara antes de capturar.', true);
                    return;
                }

                const width = video.videoWidth || 640;
                const height = video.videoHeight || 480;
                canvas.width = width;
                canvas.height = height;

                const ctx = canvas.getContext('2d');
                ctx.drawImage(video, 0, 0, width, height);

                const dataUrl = canvas.toDataURL('image/jpeg', 0.92);
                capturedPhotoInput.value = dataUrl;
                preview.src = dataUrl;
                preview.classList.remove('hidden');
                resetVerificationState();
                setStatus('Rostro capturado. Ahora presiona "Verificar identidad".');
                stopCamera();
            });

            stopBtn.addEventListener('click', stopCamera);
            window.addEventListener('beforeunload', stopCamera);

            verifyBtn.addEventListener('click', async () => {
                resetVerificationState();

                const email = emailInput.value.trim().toLowerCase();
                const captured = capturedPhotoInput.value;
                const csrf = form.querySelector('input[name="_token"]').value;

                if (!email) {
                    setStatus('Ingresa tu correo institucional.', true);
                    return;
                }

                if (!captured) {
                    setStatus('Captura tu rostro antes de verificar.', true);
                    return;
                }

                setStatus('Validando identidad facial...');

                try {
                    const referenceResponse = await fetch('{{ route('login.face.reference') }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'Accept': 'application/json',
                            'X-CSRF-TOKEN': csrf,
                        },
                        body: JSON.stringify({ email }),
                    });

                    const payload = await referenceResponse.json();

                    if (!referenceResponse.ok || !payload.reference_photo) {
                        throw new Error('No se pudo obtener la foto de referencia del perfil.');
                    }

                    await ensureModels();

                    const referenceImage = await dataUrlToImage(payload.reference_photo);
                    const capturedImage = await dataUrlToImage(captured);

                    const [referenceDescriptor, capturedDescriptor] = await Promise.all([
                        getDescriptor(referenceImage),
                        getDescriptor(capturedImage),
                    ]);

                    if (!referenceDescriptor || !capturedDescriptor) {
                        throw new Error('No se detectó un rostro válido en una de las imágenes.');
                    }

                    const distance = faceapi.euclideanDistance(referenceDescriptor, capturedDescriptor);
                    faceDistanceInput.value = distance.toFixed(4);

                    if (distance <= maxDistance) {
                        faceVerifiedInput.value = '1';
                        submitBtn.disabled = false;
                        setStatus('Identidad verificada correctamente. Ya puedes entrar.');
                    } else {
                        faceVerifiedInput.value = '0';
                        submitBtn.disabled = true;
                        setStatus('El rostro no coincide con la foto registrada.', true);
                    }
                } catch (error) {
                    faceVerifiedInput.value = '0';
                    submitBtn.disabled = true;
                    setStatus(error.message || 'No se pudo completar la validación facial.', true);
                }
            });

            emailInput.addEventListener('input', resetVerificationState);

            form.addEventListener('submit', (event) => {
                if (faceVerifiedInput.value !== '1') {
                    event.preventDefault();
                    setStatus('Primero debes verificar tu identidad facial.', true);
                }
            });
        })();
    </script>
@endsection
```

## resources/views/layouts/app.blade.php

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $title ?? 'UNIFRANZ Stream' }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&family=Sora:wght@400;600;700;800&display=swap" rel="stylesheet">
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="antialiased">
@php
    $public = $public ?? false;
    $user = auth()->user();
    $role = $user?->role?->slug;
    $profilePhotoUrl = $user?->foto_perfil
        ? route('profile.photo', ['v' => optional($user->updated_at)->timestamp])
        : null;
    $dashboardRoute = match ($role) {
        'administrador' => 'admin.dashboard',
        'docente' => 'teacher.dashboard',
        'estudiante' => 'student.dashboard',
        default => null,
    };
    $navigation = $public || ! $dashboardRoute ? [] : [
        ['label' => 'Mi panel', 'route' => $dashboardRoute],
        ['label' => 'Portal público', 'route' => 'landing'],
    ];
@endphp

<div class="relative min-h-screen">
    <div class="pointer-events-none absolute inset-0 bg-[radial-gradient(circle_at_top_right,rgba(245,124,0,0.12),transparent_25%)]"></div>

    @if ($public)
        <header class="relative z-10 px-6 py-6 lg:px-10">
            <nav class="mx-auto flex max-w-7xl items-center justify-between rounded-full border border-white/60 bg-white/75 px-6 py-4 shadow-[0_14px_40px_rgba(17,17,17,0.06)] backdrop-blur-xl">
                <a href="{{ route('landing') }}" class="flex items-center gap-3">
                    <span class="flex h-11 w-11 items-center justify-center rounded-2xl bg-[#111111] text-sm font-bold text-white">UF</span>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">UNIFRANZ</p>
                        <p class="text-sm text-slate-500">Academic Streaming Platform</p>
                    </div>
                </a>
                <div class="flex items-center gap-3">
                    <a href="#conocer-mas" class="hidden text-sm font-semibold text-slate-600 md:inline-flex">Conocer más</a>
                    <a href="{{ route('login') }}" class="btn-primary">Iniciar sesión</a>
                </div>
            </nav>
        </header>

        <main class="relative z-10">
            @yield('content')
        </main>
    @else
        <div class="relative z-10 lg:grid lg:min-h-screen lg:grid-cols-[290px_minmax(0,1fr)]">
            <aside class="hidden border-r border-white/60 bg-[#f5efe7]/80 px-6 py-8 backdrop-blur-xl lg:flex lg:flex-col">
                <a href="{{ route('dashboard') }}" class="mb-10 flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-2xl bg-[#111111] text-sm font-bold text-white">UF</span>
                    <div>
                        <p class="font-display text-sm font-semibold uppercase tracking-[0.24em] text-orange-600">UNIFRANZ</p>
                        <p class="text-sm text-slate-500">Streaming académico</p>
                    </div>
                </a>

                <div class="surface-panel p-4">
                    <p class="text-xs font-semibold uppercase tracking-[0.25em] text-slate-400">Navegación</p>
                    <div class="mt-4 space-y-2">
                        @foreach ($navigation as $item)
                            <a href="{{ route($item['route']) }}" @class([
                                'sidebar-link',
                                'sidebar-link-active' => request()->routeIs($item['route']),
                            ])>
                                <span>{{ $item['label'] }}</span>
                            </a>
                        @endforeach
                    </div>
                </div>

                <div class="surface-dark mt-6 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-orange-200/80">Acceso</p>
                    <p class="mt-3 text-lg font-semibold">{{ $user?->role?->name }}</p>
                    <p class="mt-2 text-sm text-white/70">Control segmentado por rol con acceso seguro y cuentas institucionales.</p>
                </div>

                <div class="mt-auto rounded-[28px] border border-dashed border-slate-300/80 p-5">
                    <p class="text-xs uppercase tracking-[0.25em] text-slate-400">Cuenta</p>
                    <a href="{{ route('profile.edit') }}" class="mt-4 flex items-center gap-3 rounded-2xl px-2 py-2 transition hover:bg-white hover:shadow-sm">
                        @if ($profilePhotoUrl)
                            <img src="{{ $profilePhotoUrl }}" alt="Foto de perfil" class="h-11 w-11 rounded-2xl border border-white object-cover">
                        @else
                            <span class="flex h-11 w-11 items-center justify-center rounded-2xl text-sm font-bold text-white" style="background-color: {{ $user?->avatar_color ?? '#F57C00' }}">
                                {{ strtoupper(substr($user?->name ?? 'UF', 0, 2)) }}
                            </span>
                        @endif
                        <div>
                            <p class="font-semibold text-slate-900">{{ $user?->name }}</p>
                            <p class="text-sm text-slate-500">{{ $user?->email }}</p>
                        </div>
                    </a>
                </div>
            </aside>

            <div class="flex min-h-screen flex-col">
                <header class="px-6 py-6 lg:px-8">
                    <div class="surface-panel flex flex-col gap-4 px-6 py-5 md:flex-row md:items-center md:justify-between">
                        <div>
                            <p class="text-xs font-semibold uppercase tracking-[0.25em] text-orange-600">{{ $eyebrow ?? 'Panel principal' }}</p>
                            <h1 class="mt-2 text-2xl font-semibold text-[#111111]">{{ $pageTitle ?? 'UNIFRANZ Stream' }}</h1>
                            @isset($pageDescription)
                                <p class="mt-1 text-sm text-slate-500">{{ $pageDescription }}</p>
                            @endisset
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('landing') }}" class="btn-secondary">Ver landing</a>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="btn-primary">Cerrar sesión</button>
                            </form>
                        </div>
                    </div>
                </header>

                <main class="flex-1 px-6 pb-8 lg:px-8">
                    @if (session('success'))
                        <div class="mb-6 rounded-3xl border border-emerald-200 bg-emerald-50 px-5 py-4 text-sm text-emerald-700">
                            {{ session('success') }}
                        </div>
                    @endif

                    @if (session('status'))
                        <div class="mb-6 rounded-3xl border border-orange-200 bg-orange-50 px-5 py-4 text-sm text-orange-700">
                            {{ session('status') }}
                        </div>
                    @endif

                    @if ($errors->any())
                        <div class="mb-6 rounded-3xl border border-red-200 bg-red-50 px-5 py-4 text-sm text-red-700">
                            <ul class="space-y-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    @yield('content')
                </main>
            </div>
        </div>
    @endif
</div>
</body>
</html>
```

## resources/views/dashboard/admin.blade.php

```blade
@extends('layouts.app', [
    'title' => 'Dashboard administrador | UNIFRANZ Stream',
    'eyebrow' => 'Panel administrador',
    'pageTitle' => 'Gobierno institucional de la plataforma',
    'pageDescription' => 'Administra usuarios, roles, reuniones y capacidad operativa del ecosistema.',
])

@php
    $meetingTotal = $meetings->count();
    $meetingScheduled = $meetings->where('estado', 'programada')->count();
    $meetingLive = $meetings->where('estado', 'en_vivo')->count();
    $meetingFinished = $meetings->where('estado', 'finalizada')->count();
    $activeUsers = $users->where('activo', true)->count();
    $inactiveUsers = $users->where('activo', false)->count();
@endphp

@section('content')
    <div class="admin-dashboard-shell" data-admin-dashboard>
        <section class="admin-dashboard-hero surface-dark" data-dashboard-panel>
            <div class="admin-dashboard-hero-copy">
                <p class="admin-dashboard-kicker">Centro de control</p>
                <h2 class="admin-dashboard-hero-title">Operacion academica, usuarios y clases en una sola vista.</h2>
                <p class="admin-dashboard-hero-text">
                    Supervisa el flujo institucional, filtra reuniones por estado y ajusta cuentas sin salir del panel.
                </p>

                <div class="admin-dashboard-highlight-grid">
                    <article class="admin-dashboard-highlight-card">
                        <span class="admin-dashboard-highlight-label">Usuarios activos</span>
                        <strong>{{ $activeUsers }}</strong>
                    </article>

                    <article class="admin-dashboard-highlight-card">
                        <span class="admin-dashboard-highlight-label">Clases en vivo</span>
                        <strong>{{ $meetingLive }}</strong>
                    </article>

                    <article class="admin-dashboard-highlight-card">
                        <span class="admin-dashboard-highlight-label">Reuniones programadas</span>
                        <strong>{{ $meetingScheduled }}</strong>
                    </article>
                </div>
            </div>

            <div class="admin-dashboard-hero-side">
                <div class="admin-dashboard-orbit">
                    <div class="admin-dashboard-orbit-core">
                        <span>Capacidad</span>
                        <strong>{{ $metrics['usuarios'] + $metrics['reuniones'] }}</strong>
                    </div>
                </div>

                <div class="admin-dashboard-mini-stats">
                    <article>
                        <span>Grabaciones</span>
                        <strong>{{ $metrics['grabaciones'] }}</strong>
                    </article>
                    <article>
                        <span>Inactivas</span>
                        <strong>{{ $inactiveUsers }}</strong>
                    </article>
                </div>
            </div>
        </section>

        <section class="surface-panel admin-dashboard-panel admin-dashboard-summary-band" data-dashboard-panel>
            <div class="admin-dashboard-summary-band-head">
                <div>
                    <p class="admin-dashboard-kicker admin-dashboard-kicker-light">Resumen operativo</p>
                    <h2 class="admin-dashboard-section-title">Lectura rapida del sistema</h2>
                </div>
                <p class="admin-dashboard-summary-band-text">
                    Estado sintetico del sistema para revisar acceso, actividad y capacidad operativa sin bajar al resto
                    del panel.
                </p>
            </div>

            <div class="admin-dashboard-summary-band-body">
                <div class="admin-dashboard-summary-stack admin-dashboard-summary-stack-horizontal">
                    <article class="admin-dashboard-summary-card">
                        <span>Acceso</span>
                        <strong>{{ $activeUsers }}/{{ $users->count() }}</strong>
                        <p>Cuentas activas respecto al total institucional.</p>
                    </article>

                    <article class="admin-dashboard-summary-card">
                        <span>Actividad</span>
                        <strong>{{ $meetingLive }}</strong>
                        <p>Clases que requieren observacion inmediata.</p>
                    </article>

                    <article class="admin-dashboard-summary-card">
                        <span>Pipeline</span>
                        <strong>{{ $meetingScheduled }}</strong>
                        <p>Sesiones pendientes de ejecucion en agenda.</p>
                    </article>
                </div>

                <div class="admin-dashboard-timeline admin-dashboard-timeline-horizontal">
                    <article>
                        <span class="admin-dashboard-timeline-dot"></span>
                        <div>
                            <strong>Prioridad alta</strong>
                            <p>Validar reuniones en vivo y docentes asignados.</p>
                        </div>
                    </article>

                    <article>
                        <span class="admin-dashboard-timeline-dot"></span>
                        <div>
                            <strong>Prioridad media</strong>
                            <p>Revisar cuentas inactivas y roles con baja cobertura.</p>
                        </div>
                    </article>

                    <article>
                        <span class="admin-dashboard-timeline-dot"></span>
                        <div>
                            <strong>Prioridad estable</strong>
                            <p>Monitorear el crecimiento de grabaciones publicadas.</p>
                        </div>
                    </article>
                </div>
            </div>
        </section>

        <section class="admin-dashboard-metrics" data-dashboard-panel>
            <article class="admin-dashboard-metric-card">
                <span class="admin-dashboard-metric-label">Usuarios</span>
                <strong>{{ $metrics['usuarios'] }}</strong>
                <p>Base total de cuentas institucionales.</p>
            </article>

            <article class="admin-dashboard-metric-card">
                <span class="admin-dashboard-metric-label">Reuniones</span>
                <strong>{{ $metrics['reuniones'] }}</strong>
                <p>Clases registradas dentro del sistema.</p>
            </article>

            <article class="admin-dashboard-metric-card">
                <span class="admin-dashboard-metric-label">Grabaciones</span>
                <strong>{{ $metrics['grabaciones'] }}</strong>
                <p>Contenido disponible para consulta posterior.</p>
            </article>

            <article class="admin-dashboard-metric-card">
                <span class="admin-dashboard-metric-label">En vivo</span>
                <strong>{{ $metrics['live'] }}</strong>
                <p>Actividad concurrente monitoreada ahora.</p>
            </article>
        </section>

        <section class="admin-dashboard-grid">
            <div class="admin-dashboard-column">
                <section class="surface-panel admin-dashboard-panel" data-dashboard-panel>
                    <div class="admin-dashboard-panel-head">
                        <div>
                            <p class="admin-dashboard-kicker admin-dashboard-kicker-light">Crear usuario</p>
                            <h2 class="admin-dashboard-section-title">Alta institucional</h2>
                        </div>
                    </div>

                    <form action="{{ route('admin.users.store') }}" method="POST" class="admin-dashboard-form">
                        @csrf

                        <div>
                            <label for="name" class="field-label">Nombre</label>
                            <input id="name" name="name" type="text" class="field-input" value="{{ old('name') }}"
                                required>
                        </div>

                        <div>
                            <label for="email" class="field-label">Correo institucional</label>
                            <input id="email" name="email" type="email" class="field-input"
                                value="{{ old('email') }}" required pattern="^[A-Za-z0-9._%+-]+@unifranz\.edu\.bo$">
                        </div>

                        <div>
                            <label for="role_id" class="field-label">Rol</label>
                            <select id="role_id" name="role_id" class="field-select" required>
                                @foreach ($roles as $role)
                                    <option value="{{ $role->id }}" @selected(old('role_id') == $role->id)>
                                        {{ $role->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="admin-dashboard-form-grid">
                            <div>
                                <label for="password" class="field-label">Contrasena</label>
                                <input id="password" name="password" type="password" class="field-input" required>
                            </div>

                            <div>
                                <label for="password_confirmation" class="field-label">Confirmar</label>
                                <input id="password_confirmation" name="password_confirmation" type="password"
                                    class="field-input" required>
                            </div>
                        </div>

                        <label class="admin-dashboard-toggle-row">
                            <input type="checkbox" name="activo" value="1"
                                class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-200" checked>
                            Activar cuenta al crearla
                        </label>

                        <button type="submit" class="btn-primary w-full">Crear usuario</button>
                    </form>
                </section>

            </div>

            <section class="surface-panel admin-dashboard-panel admin-dashboard-panel-tall" data-dashboard-panel>
                <div class="admin-dashboard-panel-head">
                    <div>
                        <p class="admin-dashboard-kicker admin-dashboard-kicker-light">Reuniones</p>
                        <h2 class="admin-dashboard-section-title">Gestion de clases y estados</h2>
                    </div>
                </div>

                <div class="admin-dashboard-meeting-list-legacy" data-meeting-list>
                    @forelse ($meetings as $meeting)
                        @php
                            $meetingUpdateFormId = 'meeting-update-' . $meeting->id;
                            $meetingDeleteFormId = 'meeting-delete-' . $meeting->id;
                        @endphp
                        <form id="{{ $meetingUpdateFormId }}" action="{{ route('admin.reuniones.update', $meeting) }}" method="POST"
                            class="rounded-[28px] border border-slate-100 bg-white p-5"
                            data-meeting-card
                            data-state="{{ $meeting->estado }}"
                            data-search="{{ \Illuminate\Support\Str::lower(trim($meeting->title . ' ' . $meeting->description . ' ' . ($meeting->docente?->name ?? ''))) }}">
                            @csrf
                            @method('PATCH')

                            <div class="grid gap-4 xl:grid-cols-[1.2fr_0.8fr_200px_auto] xl:items-center">
                                <div>
                                    <div class="flex flex-wrap items-center gap-3">
                                        <x-status-badge :status="$meeting->estado" />
                                        <span class="text-sm text-slate-500">
                                            {{ optional($meeting->scheduled_at)->format('d M - H:i') }}
                                        </span>
                                    </div>

                                    <h3 class="mt-3 text-lg font-semibold text-slate-900">{{ $meeting->title }}</h3>
                                    <p class="mt-2 text-sm text-slate-500">{{ $meeting->docente?->name }}</p>
                                </div>

                                <p class="text-sm leading-7 text-slate-500">
                                    {{ \Illuminate\Support\Str::limit($meeting->description, 110) }}
                                </p>

                                <select name="estado" class="field-select">
                                    <option value="programada" @selected($meeting->estado === 'programada')>Programada</option>
                                    <option value="en_vivo" @selected($meeting->estado === 'en_vivo')>En vivo</option>
                                    <option value="finalizada" @selected($meeting->estado === 'finalizada')>Finalizada</option>
                                </select>

                                <div class="flex gap-3">
                                    <a href="{{ route('reuniones.show', $meeting) }}" class="btn-secondary">Ver</a>
                                    <button type="submit" class="btn-primary">Actualizar</button>
                                    <button type="submit" form="{{ $meetingDeleteFormId }}" class="btn-danger">Eliminar</button>
                                </div>
                            </div>
                        </form>
                        <form id="{{ $meetingDeleteFormId }}" action="{{ route('admin.reuniones.destroy', $meeting) }}"
                            method="POST" class="hidden"
                            onsubmit="return confirm('Se eliminara esta reunion y sus registros asociados. Continuar?')">
                            @csrf
                            @method('DELETE')
                        </form>
                    @empty
                        <article class="admin-dashboard-empty-card">
                            <strong>Sin reuniones registradas</strong>
                            <p>No hay clases disponibles para administrar todavia.</p>
                        </article>
                    @endforelse
                </div>

                <article class="admin-dashboard-empty-card" data-meeting-empty hidden>
                    <strong>Sin reuniones visibles</strong>
                    <p>No hay reuniones disponibles para mostrar en este momento.</p>
                </article>
            </section>
        </section>

        <section class="admin-dashboard-bottom-grid">
            <section class="surface-panel admin-dashboard-panel" data-dashboard-panel>
                <div class="admin-dashboard-panel-head admin-dashboard-panel-head-tight">
                    <div>
                        <p class="admin-dashboard-kicker admin-dashboard-kicker-light">Usuarios</p>
                        <h2 class="admin-dashboard-section-title">Gestion institucional</h2>
                    </div>

                    <div class="admin-dashboard-panel-meta">
                        <strong data-user-visible-count>{{ $users->count() }}</strong>
                        <span>cuentas visibles</span>
                    </div>
                </div>

                <div class="admin-dashboard-toolbar admin-dashboard-toolbar-users">
                    <label class="admin-dashboard-search">
                        <span>Buscar</span>
                        <input type="search" placeholder="Nombre, correo o rol" data-user-search>
                    </label>
                </div>

                <div class="admin-dashboard-user-role-strip">
                    @foreach ($roles as $role)
                        @php
                            $roleShare = $metrics['usuarios'] > 0
                                ? round(($role->users_count / $metrics['usuarios']) * 100)
                                : 0;
                        @endphp
                        <article class="admin-dashboard-user-role-pill">
                            <div>
                                <span>{{ $role->name }}</span>
                                <strong>{{ $role->users_count }}</strong>
                            </div>
                            <small>{{ $roleShare }}%</small>
                        </article>
                    @endforeach
                </div>

                <div class="admin-dashboard-user-list" data-user-list>
                    @foreach ($users as $managedUser)
                        @php
                            $userDeleteFormId = 'user-delete-' . $managedUser->id;
                            $isCurrentUser = auth()->id() === $managedUser->id;
                            $managedUserPhotoUrl = $managedUser->foto_perfil
                                ? route('users.photo', ['user' => $managedUser->id, 'v' => optional($managedUser->updated_at)->timestamp])
                                : null;
                        @endphp
                        <form action="{{ route('admin.users.update', $managedUser) }}" method="POST"
                            class="admin-dashboard-user-card"
                            data-user-card
                            data-search="{{ \Illuminate\Support\Str::lower(trim($managedUser->name . ' ' . $managedUser->email . ' ' . ($managedUser->role?->name ?? ''))) }}">
                            @csrf
                            @method('PATCH')

                            <div class="admin-dashboard-user-identity">
                                @if ($managedUserPhotoUrl)
                                    <img src="{{ $managedUserPhotoUrl }}" alt="Foto de {{ $managedUser->name }}"
                                        class="admin-dashboard-user-avatar border border-slate-200 object-cover">
                                @else
                                    <span class="admin-dashboard-user-avatar"
                                        style="background-color: {{ $managedUser->avatar_color ?? '#F57C00' }}">
                                        {{ strtoupper(substr($managedUser->name, 0, 2)) }}
                                    </span>
                                @endif
                                <div>
                                    <h3>{{ $managedUser->name }}</h3>
                                    <p>{{ $managedUser->email }}</p>
                                </div>
                            </div>

                            <div class="admin-dashboard-user-fields">
                                <input name="name" type="text" class="field-input" value="{{ $managedUser->name }}"
                                    required>
                                <input name="email" type="email" class="field-input" value="{{ $managedUser->email }}"
                                    required>

                                <select name="role_id" class="field-select" required>
                                    @foreach ($roles as $role)
                                        <option value="{{ $role->id }}" @selected($managedUser->role_id === $role->id)>
                                            {{ $role->name }}
                                        </option>
                                    @endforeach
                                </select>

                                <label class="admin-dashboard-toggle-row admin-dashboard-toggle-box">
                                    <input type="checkbox" name="activo" value="1"
                                        class="h-4 w-4 rounded border-slate-300 text-orange-600 focus:ring-orange-200"
                                        {{ $managedUser->activo ? 'checked' : '' }}>
                                    Activo
                                </label>

                                <button type="submit" class="btn-secondary">Guardar</button>
                                @if (! $isCurrentUser)
                                    <button type="submit" form="{{ $userDeleteFormId }}" class="btn-danger">Eliminar</button>
                                @else
                                    <span class="text-xs font-semibold uppercase tracking-[0.18em] text-slate-400">Cuenta actual</span>
                                @endif
                            </div>
                        </form>
                        @unless ($isCurrentUser)
                            <form id="{{ $userDeleteFormId }}" action="{{ route('admin.users.destroy', $managedUser) }}"
                                method="POST" class="hidden"
                                onsubmit="return confirm('Se eliminara este usuario y sus datos asociados. Continuar?')">
                                @csrf
                                @method('DELETE')
                            </form>
                        @endunless
                    @endforeach
                </div>

                <article class="admin-dashboard-empty-card" data-user-empty hidden>
                    <strong>Sin usuarios visibles</strong>
                    <p>No hay cuentas que coincidan con la busqueda actual.</p>
                </article>
            </section>
        </section>
    </div>
@endsection
```

## database/migrations/2026_04_10_000004_add_foto_perfil_to_usuarios_table.php

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasColumn('usuarios', 'foto_perfil')) {
            Schema::table('usuarios', function (Blueprint $table): void {
                $table->string('foto_perfil')->nullable()->after('avatar_color');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('usuarios', 'foto_perfil')) {
            Schema::table('usuarios', function (Blueprint $table): void {
                $table->dropColumn('foto_perfil');
            });
        }
    }
};
```


