<?php
/**
 * index.php — Quiz romántico + puzzle de imagen.
 */

$secureCookie = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off');
session_set_cookie_params([
    'lifetime' => 0,
    'path' => '/',
    'httponly' => true,
    'samesite' => 'Strict',
    'secure' => $secureCookie,
]);

if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
}

if (empty($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrfToken = $_SESSION['csrf_token'];
$cspNonce = base64_encode(random_bytes(18));

header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Resource-Policy: same-origin');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($secureCookie) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

$csp = "default-src 'self'; "
    . "script-src 'self' https://cdn.jsdelivr.net 'nonce-{$cspNonce}'; "
    . "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net; "
    . "img-src 'self' data:; "
    . "font-src 'self' https://fonts.gstatic.com; "
    . "connect-src 'self'; "
    . "object-src 'none'; "
    . "frame-ancestors 'none'; "
    . "base-uri 'self'; "
    . "form-action 'self'";
header("Content-Security-Policy: {$csp}");

$imagenesPuzzle = [];
$imgDir = __DIR__ . '/img';

if (is_dir($imgDir)) {
    $patrones = ['*.png', '*.jpg', '*.jpeg', '*.webp', '*.gif'];
    foreach ($patrones as $patron) {
        foreach (glob($imgDir . '/' . $patron) ?: [] as $archivo) {
            $imagenesPuzzle[] = 'img/' . basename($archivo);
        }
    }
    sort($imagenesPuzzle);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Our Love Story</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:wght@400;500;700&family=Manrope:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

    <style>
        :root {
            --rose-1: #f43f5e;
            --rose-2: #e11d48;
            --peach: #fb7185;
            --gold: #f59e0b;
            --ink: #23132b;
            --card: rgba(255, 255, 255, 0.8);
        }

        body {
            font-family: 'Manrope', sans-serif;
            min-height: 100vh;
            color: var(--ink);
            background:
                radial-gradient(circle at 10% 8%, rgba(251, 113, 133, 0.42), transparent 34%),
                radial-gradient(circle at 90% 20%, rgba(245, 158, 11, 0.22), transparent 40%),
                radial-gradient(circle at 30% 85%, rgba(244, 63, 94, 0.22), transparent 34%),
                linear-gradient(130deg, #fff6fb 0%, #fff0f6 48%, #fff7e9 100%);
            overflow-x: hidden;
        }

        .font-title {
            font-family: 'Cormorant Garamond', serif;
            letter-spacing: 0.02em;
        }

        .aurora {
            position: fixed;
            inset: 0;
            pointer-events: none;
            z-index: 0;
        }

        .blob {
            position: absolute;
            filter: blur(36px);
            opacity: 0.48;
            animation: drift 14s ease-in-out infinite;
        }

        .blob.one {
            width: 240px;
            height: 240px;
            border-radius: 52% 48% 57% 43%;
            background: rgba(244, 63, 94, 0.28);
            top: -30px;
            left: -30px;
        }

        .blob.two {
            width: 280px;
            height: 280px;
            border-radius: 61% 39% 38% 62%;
            background: rgba(251, 146, 60, 0.24);
            right: -80px;
            top: 25%;
            animation-delay: 1.1s;
        }

        .blob.three {
            width: 250px;
            height: 250px;
            border-radius: 36% 64% 65% 35%;
            background: rgba(244, 63, 94, 0.25);
            left: 18%;
            bottom: -90px;
            animation-delay: 2.2s;
        }

        @keyframes drift {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-14px) scale(1.05); }
        }

        .spark {
            position: absolute;
            animation: floatUp 4.8s ease-in-out infinite;
            opacity: 0.18;
        }

        @keyframes floatUp {
            0%, 100% { transform: translateY(0) rotate(0deg); opacity: 0.1; }
            50% { transform: translateY(-14px) rotate(8deg); opacity: 0.28; }
        }

        .card-romantic {
            background: var(--card);
            border: 1px solid rgba(255, 255, 255, 0.72);
            border-radius: 1.25rem;
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            box-shadow: 0 20px 46px rgba(66, 25, 52, 0.12);
        }

        .fade-in {
            animation: fadeIn 0.65s cubic-bezier(.21,.81,.35,1) both;
        }

        .slide-in {
            animation: slideIn 0.5s cubic-bezier(.21,.81,.35,1) both;
        }

        .shake {
            animation: shake 0.46s ease both;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(22px); }
            to { opacity: 1; transform: translateY(0); }
        }

        @keyframes slideIn {
            from { opacity: 0; transform: translateX(24px); }
            to { opacity: 1; transform: translateX(0); }
        }

        @keyframes shake {
            0%, 100% { transform: translateX(0); }
            20% { transform: translateX(-8px); }
            45% { transform: translateX(8px); }
            70% { transform: translateX(-5px); }
        }

        .btn-love {
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 0.85rem 1.9rem;
            font-weight: 700;
            background: linear-gradient(135deg, var(--rose-1), var(--peach));
            box-shadow: 0 12px 26px rgba(244, 63, 94, 0.33);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
        }

        .btn-love:hover {
            color: #fff;
            transform: translateY(-2px);
            box-shadow: 0 16px 34px rgba(244, 63, 94, 0.4);
        }

        .btn-gold {
            color: #fff;
            border: none;
            border-radius: 999px;
            padding: 0.8rem 1.6rem;
            font-weight: 700;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            box-shadow: 0 10px 24px rgba(245, 158, 11, 0.35);
        }

        .audio-toggle {
            position: fixed;
            top: 16px;
            right: 16px;
            z-index: 30;
            border: 1px solid rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.8);
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            color: #a5164d;
            border-radius: 999px;
            font-weight: 700;
            font-size: 0.85rem;
            padding: 0.55rem 0.95rem;
            box-shadow: 0 10px 20px rgba(66, 25, 52, 0.14);
        }

        .audio-toggle:hover {
            color: #fff;
            background: linear-gradient(135deg, #f43f5e, #fb7185);
        }

        .progress-shell {
            background: rgba(244, 63, 94, 0.1);
            border-radius: 999px;
            overflow: hidden;
            height: 8px;
        }

        .progress-love {
            height: 100%;
            width: 0;
            background: linear-gradient(90deg, #f43f5e, #fb7185, #f59e0b);
            transition: width 0.5s cubic-bezier(.21,.81,.35,1);
        }

        .btn-respuesta {
            width: 100%;
            border-radius: 0.85rem;
            border: 1px solid rgba(244, 63, 94, 0.22);
            padding: 0.82rem 1rem;
            text-align: left;
            background: rgba(255, 255, 255, 0.74);
            transition: all 0.2s ease;
            font-size: 0.95rem;
        }

        .btn-respuesta:hover:not(:disabled) {
            transform: translateY(-1px);
            border-color: var(--rose-1);
            box-shadow: 0 8px 20px rgba(244, 63, 94, 0.14);
        }

        .btn-respuesta:disabled {
            cursor: default;
        }

        .btn-respuesta.correcta {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0) !important;
            border-color: #10b981 !important;
            color: #064e3b;
        }

        .btn-respuesta.incorrecta {
            background: linear-gradient(135deg, #fee2e2, #fecaca) !important;
            border-color: #ef4444 !important;
            color: #7f1d1d;
        }

        .puzzle-board {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 8px;
            max-width: 430px;
            margin: 0 auto;
        }

        .puzzle-tile {
            aspect-ratio: 1 / 1;
            border-radius: 0.9rem;
            border: 1px solid rgba(255, 255, 255, 0.85);
            box-shadow: 0 8px 18px rgba(17, 24, 39, 0.17);
            transition: transform 0.2s ease, box-shadow 0.2s ease;
            background-color: #f6f6f6;
            position: relative;
            overflow: hidden;
            cursor: pointer;
        }

        .puzzle-tile:hover {
            transform: translateY(-2px) scale(1.01);
            box-shadow: 0 12px 22px rgba(17, 24, 39, 0.24);
        }

        .puzzle-tile.selected {
            outline: 3px solid rgba(244, 63, 94, 0.72);
            transform: scale(1.02);
        }

        .puzzle-fallback {
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            color: #fff;
            font-size: clamp(0.9rem, 3vw, 1.3rem);
            text-shadow: 0 1px 4px rgba(17, 24, 39, 0.35);
            background: linear-gradient(135deg, #f43f5e, #fb7185);
        }

        .letter {
            background: linear-gradient(145deg, rgba(255,255,255,0.92), rgba(255,248,232,0.92));
            border: 2px solid rgba(245, 158, 11, 0.36);
            position: relative;
            overflow: hidden;
        }

        .letter::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(circle at 15% 12%, rgba(244,63,94,0.1), transparent 42%),
                radial-gradient(circle at 88% 88%, rgba(245,158,11,0.12), transparent 44%);
            pointer-events: none;
        }

        .letter-reveal {
            opacity: 0;
            transform: scale(0.96) translateY(12px);
            transition: all 0.72s ease;
        }

        .letter-reveal.visible {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        @media (max-width: 576px) {
            .puzzle-board {
                gap: 6px;
            }
        }
    </style>
</head>

<body class="d-flex align-items-center justify-content-center py-4">
    <button id="btn-audio" class="audio-toggle" type="button" aria-pressed="false">🔇 Music: Off</button>

    <div class="aurora">
        <div class="blob one"></div>
        <div class="blob two"></div>
        <div class="blob three"></div>

        <span class="spark" style="top:10%;left:7%;font-size:1.3rem;animation-delay:.2s;">✨</span>
        <span class="spark" style="top:17%;right:8%;font-size:1.8rem;animation-delay:1.1s;">💗</span>
        <span class="spark" style="top:72%;left:6%;font-size:1.1rem;animation-delay:2s;">🌸</span>
        <span class="spark" style="top:82%;right:13%;font-size:1.4rem;animation-delay:.7s;">💕</span>
    </div>

    <div class="container" style="max-width: 650px; position: relative; z-index: 2;">
        <div id="screen-inicio" class="card-romantic p-4 p-md-5 text-center fade-in">
            <div style="font-size: 3.4rem; line-height:1;">💌</div>
            <h1 class="font-title fw-bold mb-2" style="font-size: clamp(1.9rem, 6vw, 2.6rem); color: #a5164d;">
                Our Story, Question by Question
            </h1>
            <p class="mx-auto text-secondary mb-4" style="max-width: 480px; line-height: 1.75;">
                Answer the <strong>8 new questions</strong>, beat the puzzle challenge with our photos,
                and unlock your final anniversary letter. 💖
            </p>

            <div class="d-flex justify-content-center gap-4 gap-md-5 mb-4 flex-wrap">
                <div>
                    <div class="fw-bold" style="color:#f43f5e; font-size:1.5rem;">8</div>
                    <small class="text-uppercase text-secondary" style="letter-spacing:.08em;">Questions</small>
                </div>
                <div>
                    <div class="fw-bold" style="color:#f59e0b; font-size:1.5rem;">1</div>
                    <small class="text-uppercase text-secondary" style="letter-spacing:.08em;">Puzzle</small>
                </div>
                <div>
                    <div class="fw-bold" style="color:#f43f5e; font-size:1.5rem;">∞</div>
                    <small class="text-uppercase text-secondary" style="letter-spacing:.08em;">Love</small>
                </div>
            </div>

            <button id="btn-iniciar" class="btn btn-love">Start Experience 💕</button>
        </div>

        <div id="screen-quiz" class="d-none">
            <div class="d-flex justify-content-between align-items-center mb-3 px-1 flex-wrap gap-2">
                <span class="badge rounded-pill px-3 py-2" style="background:rgba(244,63,94,.12); color:#a5164d;">💞 para mi hermosa Wipawee</span>
                <span id="label-score" class="badge rounded-pill px-3 py-2" style="background:rgba(245,158,11,.12); color:#a16207;">⭐ 0 / 8</span>
            </div>

            <div class="progress-shell mb-4">
                <div id="barra-progreso" class="progress-love"></div>
            </div>

            <div id="tarjeta-pregunta" class="card-romantic p-4 p-md-5">
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div id="num-pregunta" class="rounded-circle d-flex align-items-center justify-content-center text-white fw-bold" style="width:38px;height:38px;background:linear-gradient(135deg,#f43f5e,#fb7185);">1</div>
                    <span id="meta-preguntas" class="text-secondary" style="font-size:.85rem;">Question 1 of 8</span>
                </div>

                <h2 id="texto-pregunta" class="font-title fw-bold mb-4" style="font-size: clamp(1.2rem, 4vw, 1.45rem); line-height:1.5;"></h2>
                <div id="contenedor-opciones" class="d-grid gap-3"></div>

                <div id="feedback" class="mt-3 text-center fw-semibold" style="min-height:26px; opacity:0; transition: opacity .25s ease;"></div>
            </div>
        </div>

        <div id="screen-puzzle" class="d-none text-center">
            <div class="card-romantic p-4 p-md-5 fade-in">
                <div style="font-size: 2.7rem;">🧩</div>
                <h2 class="font-title fw-bold mb-2" style="font-size: clamp(1.6rem, 5vw, 2.1rem); color:#a5164d;">Final Challenge: Complete the Puzzle</h2>
                <p class="text-secondary mb-3">
                    Swap two pieces per turn until each image is reconstructed.
                    You must complete both photos to unlock your special letter. 💘
                </p>

                <p id="progreso-fotos" class="fw-semibold mb-2" style="color:#a5164d;">Photo 1 of 2</p>
                <p id="estado-puzzle" class="fw-semibold mb-3" style="color:#be185d;">Moves: 0</p>
                <div class="d-flex justify-content-center align-items-center gap-2 mb-3 flex-wrap">
                    <label for="puzzle-dificultad" class="fw-semibold" style="color:#a5164d;">Difficulty:</label>
                    <select id="puzzle-dificultad" class="form-select" style="max-width: 170px;">
                        <option value="4" selected>4x4 (classic)</option>
                        <option value="5">5x5 (challenge)</option>
                    </select>
                </div>
                <div id="puzzle-board" class="puzzle-board mb-4"></div>
                <button id="btn-mezclar" class="btn btn-gold">Shuffle Again</button>
            </div>
        </div>

        <div id="screen-victoria" class="d-none text-center">
            <div class="mb-2" style="font-size:3rem;">🎉</div>
            <h2 class="font-title fw-bold mb-1" style="font-size: clamp(1.7rem, 5vw, 2.2rem); color:#a5164d;">You Did It, My Love</h2>
            <p class="text-secondary mb-4">Final score: <strong id="score-porcentaje" style="color:#f43f5e;">—</strong> · Gift unlocked 💝</p>

            <div id="carta-amor" class="letter card-romantic p-4 p-md-5 text-start mb-4 letter-reveal">
                <div class="text-center mb-4">
                    <div style="font-size:2rem;">💌</div>
                    <h3 class="font-title fst-italic fw-bold mb-0" style="font-size: 1.3rem; color:#92400e;">Love Letter</h3>
                </div>
                <div id="mensaje-amor" class="font-title" style="line-height: 2; font-size: clamp(.96rem, 3vw, 1.12rem);"></div>
            </div>

            <div id="descargas-imagenes" class="card-romantic p-3 p-md-4 mb-4 d-none">
                <h4 class="font-title fw-bold mb-2" style="color:#a5164d;">Your Photos to Download</h4>
                <p class="text-secondary mb-3" style="font-size:.92rem;">You can save these memory photos to your device.</p>
                <div id="lista-descargas" class="d-flex flex-wrap justify-content-center gap-2"></div>
            </div>

            <p class="font-title fw-bold mb-4" style="color:#a5164d; font-size:clamp(1rem,3.8vw,1.35rem); letter-spacing:.02em;">
                HAPPY ANNIVERSARY MY LOVE 1 YEAR AND 9 MONTHS, UNTIL THE ETERNITY YOU AND ME.
            </p>

            <button id="btn-rejugar" class="btn btn-gold">Play Again ♻️</button>
        </div>

        <div id="screen-derrota" class="d-none text-center">
            <div class="card-romantic p-4 p-md-5 fade-in">
                <div class="mb-3" style="font-size:3rem;">🥺</div>
                <h2 class="font-title fw-bold mb-2" style="color:#a5164d;">One More Try, My Love</h2>
                <p class="text-secondary mb-4">
                    You need at least <strong>80%</strong> to reach the final puzzle.
                </p>
                <div class="rounded-3 p-3 mb-4" style="background:rgba(244,63,94,.08); border:1px solid rgba(244,63,94,.18);">
                    <span class="fw-semibold" style="color:#a5164d;">Your score: <span id="puntaje-final">0</span> / 8 · <span id="porcentaje-final">0%</span></span>
                </div>
                <button id="btn-reintentar" class="btn btn-love">Try Again 🌸</button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?php echo htmlspecialchars($cspNonce, ENT_QUOTES, 'UTF-8'); ?>">
    const IMAGENES_PUZZLE = <?php echo json_encode($imagenesPuzzle, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;
    const CSRF_TOKEN = <?php echo json_encode($csrfToken, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>;

    const preguntas = [
        {
            pregunta: 'a) How many years together will we celebrate in July 2026?',
            opciones: ['1 year', '2 years', '3 years', '4 years'],
            correcta: 1
        },
        {
            pregunta: 'b) What do I love most about our calls?',
            opciones: [
                'Only seeing you on camera',
                'Hearing your voice, seeing your smile, and listening to what you say',
                'Talking about work',
                'Keeping them short'
            ],
            correcta: 1
        },
        {
            pregunta: 'c) Which shoulder hurts me in winter?',
            opciones: ['Right', 'Left', 'Both', 'None'],
            correcta: 1
        },
        {
            pregunta: 'd) Which food can I not eat?',
            opciones: ['Tomatoes', 'Garlic', 'Onions', 'Peppers'],
            correcta: 2
        },
        {
            pregunta: 'e) What is my field of study?',
            opciones: ['Civil Engineering', 'Cybersecurity Engineering', 'Digital Design', 'Medicine'],
            correcta: 1
        },
        {
            pregunta: 'f) In which month was I born?',
            opciones: ['January', 'February', 'March', 'April'],
            correcta: 1
        },
        {
            pregunta: 'g) What do I love most about your personality?',
            opciones: [
                'The way you speak',
                'Your intelligence',
                'Everything, because you are perfect for me',
                'Your patience'
            ],
            correcta: 2
        },
        {
            pregunta: 'h) Who is my everything and my priority now and in the future?',
            opciones: ['My work', 'My family', 'My goals', 'My beautiful princess Ana'],
            correcta: 3
        }
    ];

    const TOTAL = preguntas.length;
    const PASSING_SCORE = 80;
    let puzzleSize = 4;

    let indice = 0;
    let puntaje = 0;
    let respondido = false;

    let puzzleOrden = [];
    let puzzleSeleccionado = null;
    let puzzleMovimientos = 0;
    let puzzleBloqueado = false;
    let imagenPuzzleActual = '';
    let puzzleImagenesReto = [];
    let puzzleFotoActual = 0;
    let puzzleMovimientosTotales = 0;

    const $ = id => document.getElementById(id);

    const pantallas = [
        $('screen-inicio'),
        $('screen-quiz'),
        $('screen-puzzle'),
        $('screen-victoria'),
        $('screen-derrota')
    ];

    let audioCtx = null;
    let bgmMaster = null;
    let bgmMuted = true;

    function getPuzzleSize() {
        const size = Number.parseInt($('puzzle-dificultad').value, 10);
        return size === 5 ? 5 : 4;
    }

    function prepararRetoPuzzle() {
        const candidatas = Array.isArray(IMAGENES_PUZZLE) ? IMAGENES_PUZZLE.slice(0, 2) : [];
        puzzleImagenesReto = candidatas.length > 0 ? candidatas : [''];
        puzzleFotoActual = 0;
        puzzleMovimientosTotales = 0;
    }

    function actualizarInfoPuzzle() {
        const totalFotos = puzzleImagenesReto.length;
        $('progreso-fotos').textContent = `Photo ${Math.min(puzzleFotoActual + 1, totalFotos)} of ${totalFotos}`;
        $('estado-puzzle').textContent = `Moves: ${puzzleMovimientos}`;
    }

    function renderizarDescargasImagenes() {
        const panel = $('descargas-imagenes');
        const lista = $('lista-descargas');
        lista.innerHTML = '';

        if (!Array.isArray(IMAGENES_PUZZLE) || IMAGENES_PUZZLE.length === 0) {
            panel.classList.add('d-none');
            return;
        }

        IMAGENES_PUZZLE.forEach((ruta, idx) => {
            const enlace = document.createElement('a');
            const nombre = ruta.split('/').pop() || `foto-${idx + 1}.png`;
            enlace.href = ruta;
            enlace.download = nombre;
            enlace.className = 'btn btn-love';
            enlace.style.padding = '0.55rem 1.1rem';
            enlace.style.fontSize = '0.82rem';
            enlace.textContent = `Download photo ${idx + 1}`;
            lista.appendChild(enlace);
        });

        panel.classList.remove('d-none');
    }

    function updateAudioButton() {
        const btn = $('btn-audio');
        btn.textContent = bgmMuted ? '🔇 Music: Off' : '🔊 Music: On';
        btn.setAttribute('aria-pressed', String(!bgmMuted));
    }

    function iniciarMusicaFondo() {
        if (audioCtx) return;

        const Ctx = window.AudioContext || window.webkitAudioContext;
        if (!Ctx) return;

        audioCtx = new Ctx();
        bgmMaster = audioCtx.createGain();
        bgmMaster.gain.value = 0;

        const masterFilter = audioCtx.createBiquadFilter();
        masterFilter.type = 'lowpass';
        masterFilter.frequency.value = 1800;

        const masterCompressor = audioCtx.createDynamicsCompressor();
        masterCompressor.threshold.value = -24;
        masterCompressor.knee.value = 15;
        masterCompressor.ratio.value = 3;

        masterFilter.connect(masterCompressor);
        masterCompressor.connect(bgmMaster);
        bgmMaster.connect(audioCtx.destination);

        const nota = semitonos => 220 * Math.pow(2, semitonos / 12);

        function tocarNota(freq, duracion, tiempo, tipo, volumen) {
            const osc = audioCtx.createOscillator();
            const gain = audioCtx.createGain();
            const filtro = audioCtx.createBiquadFilter();

            osc.type = tipo;
            osc.frequency.setValueAtTime(freq, tiempo);

            filtro.type = 'lowpass';
            filtro.frequency.setValueAtTime(2200, tiempo);

            gain.gain.setValueAtTime(0.0001, tiempo);
            gain.gain.exponentialRampToValueAtTime(volumen, tiempo + 0.08);
            gain.gain.exponentialRampToValueAtTime(0.0001, tiempo + duracion);

            osc.connect(filtro);
            filtro.connect(gain);
            gain.connect(masterFilter);

            osc.start(tiempo);
            osc.stop(tiempo + duracion + 0.03);
        }

        const melodia = [
            7, 9, 10, 9, 7, 5, 4, 2,
            4, 5, 7, 9, 10, 9, 7, 5
        ];
        const bajos = [0, 5, 7, 4];

        const tempo = 78;
        const negra = 60 / tempo;
        const compas = negra * 4;

        let compasActual = 0;
        setInterval(() => {
            if (!audioCtx) return;
            const inicio = audioCtx.currentTime + 0.05;

            const bajo = nota(-12 + bajos[compasActual % bajos.length]);
            tocarNota(bajo, compas * 0.96, inicio, 'sine', 0.05);

            const triadaBase = bajos[compasActual % bajos.length];
            const tercera = triadaBase + 4;
            const quinta = triadaBase + 7;
            tocarNota(nota(triadaBase), compas * 0.9, inicio, 'triangle', 0.018);
            tocarNota(nota(tercera), compas * 0.9, inicio + negra * 0.06, 'triangle', 0.015);
            tocarNota(nota(quinta), compas * 0.9, inicio + negra * 0.12, 'triangle', 0.015);

            for (let i = 0; i < 4; i++) {
                const idx = (compasActual * 4 + i) % melodia.length;
                const freq = nota(melodia[idx]);
                const t = inicio + i * negra;
                tocarNota(freq, negra * 0.86, t, 'sine', 0.045);
            }

            compasActual++;
        }, compas * 1000);
    }

    async function activarMusica() {
        iniciarMusicaFondo();
        if (!audioCtx || !bgmMaster) return;
        if (audioCtx.state === 'suspended') {
            await audioCtx.resume();
        }
        bgmMuted = false;
        bgmMaster.gain.setTargetAtTime(0.08, audioCtx.currentTime, 0.5);
        updateAudioButton();
    }

    async function alternarMusica() {
        iniciarMusicaFondo();
        if (!audioCtx || !bgmMaster) return;

        if (audioCtx.state === 'suspended') {
            await audioCtx.resume();
        }

        bgmMuted = !bgmMuted;
        const target = bgmMuted ? 0 : 0.08;
        bgmMaster.gain.setTargetAtTime(target, audioCtx.currentTime, 0.25);
        updateAudioButton();
    }

    function reproducirAcierto() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const notas = [523.25, 659.25, 783.99];
            notas.forEach((freq, i) => {
                const osc = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                const t = ctx.currentTime + i * 0.12;
                osc.frequency.setValueAtTime(freq, t);
                gain.gain.setValueAtTime(0.16, t);
                gain.gain.exponentialRampToValueAtTime(0.001, t + 0.35);
                osc.start(t);
                osc.stop(t + 0.35);
            });
        } catch (_) {}
    }

    function reproducirError() {
        try {
            const ctx = new (window.AudioContext || window.webkitAudioContext)();
            const osc = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'triangle';
            osc.frequency.setValueAtTime(220, ctx.currentTime);
            osc.frequency.exponentialRampToValueAtTime(95, ctx.currentTime + 0.28);
            gain.gain.setValueAtTime(0.09, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.28);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.28);
        } catch (_) {}
    }

    function lanzarConfetti() {
        const colores = ['#f43f5e', '#fb7185', '#f59e0b', '#fbbf24', '#ffffff'];
        const fin = Date.now() + 2300;
        (function rafLoop() {
            confetti({ particleCount: 4, angle: 60, spread: 58, origin: { x: 0 }, colors: colores, scalar: 1.08 });
            confetti({ particleCount: 4, angle: 120, spread: 58, origin: { x: 1 }, colors: colores, scalar: 1.08 });
            if (Date.now() < fin) requestAnimationFrame(rafLoop);
        })();
    }

    function mostrarPantalla(id) {
        pantallas.forEach(el => {
            el.classList.add('d-none');
            el.classList.remove('fade-in');
        });
        const target = $(id);
        target.classList.remove('d-none');
        void target.offsetWidth;
        target.classList.add('fade-in');
    }

    function renderizarPregunta() {
        respondido = false;
        const q = preguntas[indice];

        $('num-pregunta').textContent = indice + 1;
        $('meta-preguntas').textContent = `Question ${indice + 1} of ${TOTAL}`;
        $('texto-pregunta').textContent = q.pregunta;
        $('barra-progreso').style.width = `${(indice / TOTAL) * 100}%`;
        $('label-score').textContent = `⭐ ${puntaje} / ${TOTAL}`;

        const feedback = $('feedback');
        feedback.style.opacity = '0';
        feedback.textContent = '';

        const contenedor = $('contenedor-opciones');
        contenedor.innerHTML = '';

        q.opciones.forEach((opcion, i) => {
            const btn = document.createElement('button');
            btn.className = 'btn-respuesta';
            btn.innerHTML = `<span style="color:#f43f5e;font-weight:700;margin-right:.45rem;">${String.fromCharCode(65 + i)}.</span>${opcion}`;
            btn.addEventListener('click', () => manejarRespuesta(i, btn));
            contenedor.appendChild(btn);
        });

        const tarjeta = $('tarjeta-pregunta');
        tarjeta.classList.remove('slide-in');
        void tarjeta.offsetWidth;
        tarjeta.classList.add('slide-in');
    }

    function manejarRespuesta(seleccionado, btnSeleccionado) {
        if (respondido) return;
        respondido = true;

        const q = preguntas[indice];
        const botones = $('contenedor-opciones').querySelectorAll('.btn-respuesta');
        const feedback = $('feedback');

        botones.forEach(b => { b.disabled = true; });

        if (seleccionado === q.correcta) {
            puntaje++;
            btnSeleccionado.classList.add('correcta');
            feedback.innerHTML = '<span style="color:#059669;">✅ Correct, my love.</span>';
            reproducirAcierto();
        } else {
            btnSeleccionado.classList.add('incorrecta');
            botones[q.correcta].classList.add('correcta');
            feedback.innerHTML = '<span style="color:#dc2626;">💔 Almost, check the highlighted answer.</span>';
            reproducirError();
            const tarjeta = $('tarjeta-pregunta');
            tarjeta.classList.remove('shake');
            void tarjeta.offsetWidth;
            tarjeta.classList.add('shake');
            setTimeout(() => tarjeta.classList.remove('shake'), 460);
        }

        feedback.style.opacity = '1';
        $('label-score').textContent = `⭐ ${puntaje} / ${TOTAL}`;

        setTimeout(() => {
            indice++;
            if (indice < TOTAL) {
                renderizarPregunta();
                return;
            }
            finalizarQuiz();
        }, 1500);
    }

    function finalizarQuiz() {
        const porcentaje = Math.round((puntaje / TOTAL) * 100);
        if (porcentaje >= PASSING_SCORE) {
            $('score-porcentaje').textContent = `${porcentaje}%`;
            mostrarPantalla('screen-puzzle');
            iniciarRetoPuzzle();
            return;
        }

        $('puntaje-final').textContent = puntaje;
        $('porcentaje-final').textContent = `${porcentaje}%`;
        mostrarPantalla('screen-derrota');
    }

    function mezclar(array) {
        const copia = array.slice();
        for (let i = copia.length - 1; i > 0; i--) {
            const j = Math.floor(Math.random() * (i + 1));
            [copia[i], copia[j]] = [copia[j], copia[i]];
        }
        return copia;
    }

    function puzzleResuelto() {
        return puzzleOrden.every((pieza, idx) => pieza === idx);
    }

    function reiniciarPuzzleActual() {
        puzzleSize = getPuzzleSize();
        imagenPuzzleActual = puzzleImagenesReto[puzzleFotoActual] || '';

        puzzleBloqueado = false;
        puzzleSeleccionado = null;
        puzzleMovimientos = 0;
        actualizarInfoPuzzle();

        const base = Array.from({ length: puzzleSize * puzzleSize }, (_, i) => i);
        do {
            puzzleOrden = mezclar(base);
        } while (puzzleResuelto());

        renderizarPuzzle();
    }

    function iniciarRetoPuzzle() {
        puzzleSize = getPuzzleSize();
        prepararRetoPuzzle();
        reiniciarPuzzleActual();
    }

    function crearTile(pieza, posicion) {
        const tile = document.createElement('button');
        tile.className = 'puzzle-tile';
        tile.type = 'button';

        const x = pieza % puzzleSize;
        const y = Math.floor(pieza / puzzleSize);
        const bgX = (x / (puzzleSize - 1)) * 100;
        const bgY = (y / (puzzleSize - 1)) * 100;

        if (imagenPuzzleActual) {
            tile.style.backgroundImage = `url('${imagenPuzzleActual}')`;
            tile.style.backgroundSize = `${puzzleSize * 100}% ${puzzleSize * 100}%`;
            tile.style.backgroundPosition = `${bgX}% ${bgY}%`;
        } else {
            tile.classList.add('puzzle-fallback');
            tile.textContent = String(pieza + 1);
        }

        if (puzzleSeleccionado === posicion) {
            tile.classList.add('selected');
        }

        tile.addEventListener('click', () => manejarClickPuzzle(posicion));
        return tile;
    }

    function renderizarPuzzle() {
        const board = $('puzzle-board');
        board.style.gridTemplateColumns = `repeat(${puzzleSize}, 1fr)`;
        board.style.maxWidth = puzzleSize === 5 ? '560px' : '500px';
        board.innerHTML = '';
        puzzleOrden.forEach((pieza, posicion) => {
            board.appendChild(crearTile(pieza, posicion));
        });
    }

    function manejarClickPuzzle(posicion) {
        if (puzzleBloqueado) return;

        if (puzzleSeleccionado === null) {
            puzzleSeleccionado = posicion;
            renderizarPuzzle();
            return;
        }

        if (puzzleSeleccionado === posicion) {
            puzzleSeleccionado = null;
            renderizarPuzzle();
            return;
        }

        [puzzleOrden[puzzleSeleccionado], puzzleOrden[posicion]] = [puzzleOrden[posicion], puzzleOrden[puzzleSeleccionado]];
        puzzleSeleccionado = null;
        puzzleMovimientos++;
        actualizarInfoPuzzle();
        renderizarPuzzle();

        if (!puzzleResuelto()) return;

        puzzleBloqueado = true;
        puzzleMovimientosTotales += puzzleMovimientos;

        const faltantes = puzzleImagenesReto.length - (puzzleFotoActual + 1);
        if (faltantes > 0) {
            $('estado-puzzle').textContent = `Photo completed in ${puzzleMovimientos} moves. ${faltantes} photo left 💖`;
            lanzarConfetti();
            setTimeout(() => {
                puzzleFotoActual++;
                reiniciarPuzzleActual();
            }, 900);
            return;
        }

        $('estado-puzzle').textContent = `Challenge completed in ${puzzleMovimientosTotales} moves 💖`;
        lanzarConfetti();
        setTimeout(obtenerMensajeDeAmor, 900);
    }

    async function obtenerMensajeDeAmor() {
        mostrarPantalla('screen-victoria');
        renderizarDescargasImagenes();

        try {
            const resp = await fetch('api.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': CSRF_TOKEN,
                },
                body: JSON.stringify({ score: puntaje, total: TOTAL })
            });

            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

            const datos = await resp.json();
            if (datos.success && datos.message) {
                $('mensaje-amor').innerHTML = datos.message;
            } else {
                $('mensaje-amor').textContent = 'Thank you for coming this far, my love. 💕';
            }
        } catch (err) {
            console.warn('[Quiz] Could not load letter:', err.message);
            $('mensaje-amor').textContent = 'Thank you for coming this far, my love. 💕';
        }

        setTimeout(() => $('carta-amor').classList.add('visible'), 220);
        setTimeout(lanzarConfetti, 360);
    }

    function reiniciarQuiz() {
        indice = 0;
        puntaje = 0;
        respondido = false;

        puzzleOrden = [];
        puzzleSeleccionado = null;
        puzzleMovimientos = 0;
        puzzleBloqueado = false;
        puzzleImagenesReto = [];
        puzzleFotoActual = 0;
        puzzleMovimientosTotales = 0;

        $('barra-progreso').style.width = '0%';
        $('label-score').textContent = `⭐ 0 / ${TOTAL}`;
        $('meta-preguntas').textContent = `Question 1 of ${TOTAL}`;
        $('score-porcentaje').textContent = '—';
        $('porcentaje-final').textContent = '0%';
        $('puntaje-final').textContent = '0';
        $('progreso-fotos').textContent = 'Photo 1 of 2';
        $('estado-puzzle').textContent = 'Moves: 0';
        $('mensaje-amor').innerHTML = '';
        $('lista-descargas').innerHTML = '';
        $('descargas-imagenes').classList.add('d-none');
        $('carta-amor').classList.remove('visible');
    }

    $('btn-iniciar').addEventListener('click', () => {
        activarMusica();
        reiniciarQuiz();
        mostrarPantalla('screen-quiz');
        renderizarPregunta();
    });

    $('btn-rejugar').addEventListener('click', () => {
        activarMusica();
        reiniciarQuiz();
        mostrarPantalla('screen-quiz');
        renderizarPregunta();
    });

    $('btn-reintentar').addEventListener('click', () => {
        activarMusica();
        reiniciarQuiz();
        mostrarPantalla('screen-quiz');
        renderizarPregunta();
    });

    $('btn-audio').addEventListener('click', alternarMusica);
    $('puzzle-dificultad').addEventListener('change', () => {
        if ($('screen-puzzle').classList.contains('d-none')) return;
        iniciarRetoPuzzle();
    });

    $('btn-mezclar').addEventListener('click', reiniciarPuzzleActual);
    updateAudioButton();
    </script>
</body>
</html>
