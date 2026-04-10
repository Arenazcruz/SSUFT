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
