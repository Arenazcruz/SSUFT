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
            <div class="relative">
                <input id="password_login" name="password" type="password" required autocomplete="current-password"
                       class="field-input pr-24" placeholder="Tu contraseña">
                <button type="button"
                        data-password-toggle-button
                        data-target="password_login"
                        class="absolute right-3 top-1/2 -translate-y-1/2 rounded-full px-3 py-1 text-xs font-semibold text-slate-600 transition hover:bg-slate-100 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-orange-500">
                    Mostrar
                </button>
            </div>
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
