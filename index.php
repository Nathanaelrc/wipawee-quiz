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

        .tab-shell {
            border-radius: 1rem;
            background: rgba(255, 255, 255, 0.56);
            padding: 0.35rem;
            border: 1px solid rgba(255, 255, 255, 0.75);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
        }

        .tab-love .nav-link {
            border: none;
            color: #9f1239;
            font-weight: 700;
            border-radius: 999px;
            padding: 0.62rem 1rem;
        }

        .tab-love .nav-link.active {
            color: #fff;
            background: linear-gradient(135deg, #f43f5e, #fb7185);
            box-shadow: 0 10px 24px rgba(244, 63, 94, 0.3);
        }

        .quiz-pane-wrap {
            max-width: 650px;
            margin: 0 auto;
        }

        .princess-game-card {
            border-radius: 1.25rem;
            padding: 1rem;
        }

        .princess-canvas {
            width: 100%;
            height: auto;
            border-radius: 1rem;
            border: 1px solid rgba(165, 22, 77, 0.28);
            background: linear-gradient(180deg, #ffe5ef 0%, #ffd7e6 100%);
            box-shadow: inset 0 0 0 1px rgba(255, 255, 255, 0.45), 0 12px 26px rgba(66, 25, 52, 0.15);
        }

        .game-chip {
            border-radius: 999px;
            padding: 0.35rem 0.7rem;
            font-size: 0.82rem;
            font-weight: 700;
            color: #831843;
            background: rgba(244, 63, 94, 0.13);
            border: 1px solid rgba(244, 63, 94, 0.2);
        }

        .game-touch-controls {
            display: none;
            justify-content: space-between;
            align-items: stretch;
            margin-bottom: 0.4rem;
            gap: 0.8rem;
        }

        .touch-pad-left {
            display: grid;
            grid-template-columns: repeat(2, minmax(58px, 1fr));
            gap: 0.55rem;
            flex: 1;
        }

        .touch-pad-right {
            width: min(38vw, 156px);
            display: flex;
            justify-content: flex-end;
        }

        .game-touch-btn {
            min-width: 58px;
            min-height: 52px;
            border: none;
            border-radius: 0.9rem;
            color: #fff;
            font-weight: 800;
            background: linear-gradient(135deg, #f43f5e, #fb7185);
            box-shadow: 0 8px 18px rgba(244, 63, 94, 0.24);
            touch-action: manipulation;
        }

        .game-touch-btn.jump {
            width: 100%;
            min-height: 56px;
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            box-shadow: 0 8px 18px rgba(245, 158, 11, 0.32);
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

            .tab-love .nav-link {
                font-size: 0.84rem;
                padding: 0.52rem 0.8rem;
            }

            .princess-game-card {
                padding: 0.75rem;
            }

            .audio-toggle {
                top: 10px;
                right: 10px;
                font-size: 0.78rem;
                padding: 0.48rem 0.78rem;
            }
        }

        @media (max-width: 768px) {
            .game-touch-controls {
                display: flex;
            }

            .princess-canvas {
                border-radius: 0.85rem;
            }

            .tab-shell {
                margin-bottom: 0.65rem !important;
            }

            .tab-love {
                flex-wrap: nowrap;
            }

            .tab-love .nav-link {
                white-space: nowrap;
                font-size: 0.83rem;
            }

            .princess-game-card {
                padding: 0.62rem;
            }
        }

        @media (max-width: 480px) {
            body {
                padding-top: 0.45rem !important;
                padding-bottom: 0.8rem !important;
            }

            .container {
                padding-left: 0.35rem;
                padding-right: 0.35rem;
            }

            .game-chip {
                font-size: 0.76rem;
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

    <div class="container" style="max-width: 980px; position: relative; z-index: 2;">
        <div class="tab-shell mb-3">
            <ul class="nav nav-pills tab-love gap-2 justify-content-center" id="loveTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-quiz-btn" data-bs-toggle="tab" data-bs-target="#tab-quiz" type="button" role="tab" aria-controls="tab-quiz" aria-selected="true">Love Quiz + Puzzle</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-game-btn" data-bs-toggle="tab" data-bs-target="#tab-princess" type="button" role="tab" aria-controls="tab-princess" aria-selected="false">Princess Adventure</button>
                </li>
            </ul>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-quiz" role="tabpanel" aria-labelledby="tab-quiz-btn" tabindex="0">
                <div class="quiz-pane-wrap">
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
            </div>

            <div class="tab-pane fade" id="tab-princess" role="tabpanel" aria-labelledby="tab-game-btn" tabindex="0">
                <div class="card-romantic princess-game-card">
                    <div class="text-center mb-3">
                        <div style="font-size:2.2rem;line-height:1;">👑</div>
                        <h2 class="font-title fw-bold mb-1" style="font-size:clamp(1.5rem,4vw,2rem);color:#a5164d;">Princess Adventure</h2>
                        <p class="text-secondary mb-0">A game for my beautiful Princess 💖</p>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-center mb-3">
                        <span class="game-chip">Level: <span id="game-level">1</span> / <span id="game-level-total">5</span></span>
                        <span class="game-chip">Lives: <span id="game-lives">3</span></span>
                        <span class="game-chip">Stars: <span id="game-stars">0</span></span>
                    </div>

                    <canvas id="princess-game-canvas" class="princess-canvas" width="960" height="420" aria-label="Princess platform game"></canvas>

                    <p id="game-status" class="text-center fw-semibold mt-3 mb-3" style="color:#9f1239;">Press Start to begin your adventure.</p>

                    <div class="d-flex justify-content-center flex-wrap gap-2 mb-2">
                        <button id="btn-game-start" class="btn btn-love" type="button">Start Game</button>
                        <button id="btn-game-restart" class="btn btn-gold" type="button">Restart Level</button>
                        <button id="btn-game-next" class="btn btn-gold" type="button" disabled>Next Level</button>
                    </div>

                    <div class="game-touch-controls" aria-label="Touch controls for mobile">
                        <div class="touch-pad-left">
                            <button id="btn-touch-left" class="game-touch-btn" type="button">←</button>
                            <button id="btn-touch-right" class="game-touch-btn" type="button">→</button>
                        </div>
                        <div class="touch-pad-right">
                            <button id="btn-touch-jump" class="game-touch-btn jump" type="button">JUMP</button>
                        </div>
                    </div>

                    <p class="text-center text-secondary mb-0" style="font-size:.9rem;">Controls: <strong>A / D</strong> or <strong>← / →</strong> to move, <strong>W / Space / ↑</strong> to jump. On mobile use touch buttons and double tap JUMP for a higher jump.</p>
                </div>
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

    const gameCanvas = $('princess-game-canvas');
    const gameCtx = gameCanvas ? gameCanvas.getContext('2d') : null;

    const game = {
        running: false,
        loopStarted: false,
        currentLevel: 0,
        lives: 3,
        stars: 0,
        cameraX: 0,
        viewScale: 1,
        isMobileView: false,
        jumpQueued: false,
        jumpBoost: false,
        lastJumpTap: 0,
        levelCompleted: false,
        showFinalMsg: false,
        keys: { left: false, right: false },
        player: { x: 40, y: 80, w: 30, h: 44, vx: 0, vy: 0, onGround: false },
        levels: [
            {
                worldWidth: 2050,
                spawn: { x: 46, y: 180 },
                goal: { x: 1940, y: 198, w: 48, h: 82 },
                platforms: [
                    { x: 0, y: 280, w: 360, h: 60 },
                    { x: 420, y: 280, w: 360, h: 60 },
                    { x: 840, y: 280, w: 390, h: 60 },
                    { x: 1280, y: 280, w: 420, h: 60 },
                    { x: 1740, y: 280, w: 310, h: 60 },
                    { x: 210, y: 220, w: 120, h: 16 },
                    { x: 620, y: 210, w: 130, h: 16 },
                    { x: 1010, y: 205, w: 120, h: 16 },
                    { x: 1820, y: 220, w: 120, h: 16 }
                ],
                stars: [
                    { x: 260, y: 188, taken: false },
                    { x: 670, y: 178, taken: false },
                    { x: 1060, y: 174, taken: false },
                    { x: 1450, y: 242, taken: false },
                    { x: 1860, y: 188, taken: false },
                    { x: 1980, y: 246, taken: false }
                ],
                enemies: [
                    { x: 520, y: 248, w: 30, h: 32, minX: 460, maxX: 730, speed: 1.3, dir: 1 },
                    { x: 1160, y: 248, w: 30, h: 32, minX: 950, maxX: 1220, speed: 1.5, dir: -1 },
                    { x: 1890, y: 248, w: 30, h: 32, minX: 1770, maxX: 2010, speed: 1.6, dir: -1 }
                ]
            },
            {
                worldWidth: 2350,
                spawn: { x: 46, y: 170 },
                goal: { x: 2240, y: 178, w: 48, h: 102 },
                platforms: [
                    { x: 0, y: 280, w: 310, h: 60 },
                    { x: 360, y: 260, w: 220, h: 80 },
                    { x: 640, y: 225, w: 170, h: 18 },
                    { x: 860, y: 280, w: 250, h: 60 },
                    { x: 1170, y: 248, w: 220, h: 92 },
                    { x: 1460, y: 210, w: 180, h: 18 },
                    { x: 1700, y: 280, w: 300, h: 60 },
                    { x: 2040, y: 280, w: 310, h: 60 },
                    { x: 2120, y: 220, w: 150, h: 18 }
                ],
                stars: [
                    { x: 430, y: 224, taken: false },
                    { x: 700, y: 188, taken: false },
                    { x: 1260, y: 210, taken: false },
                    { x: 1520, y: 174, taken: false },
                    { x: 1800, y: 246, taken: false },
                    { x: 2165, y: 188, taken: false },
                    { x: 2280, y: 246, taken: false }
                ],
                enemies: [
                    { x: 930, y: 248, w: 30, h: 32, minX: 900, maxX: 1060, speed: 1.8, dir: 1 },
                    { x: 1750, y: 248, w: 30, h: 32, minX: 1720, maxX: 1920, speed: 1.65, dir: -1 },
                    { x: 2190, y: 248, w: 30, h: 32, minX: 2070, maxX: 2330, speed: 1.9, dir: 1 }
                ]
            },
            {
                worldWidth: 2650,
                spawn: { x: 50, y: 130 },
                goal: { x: 2540, y: 120, w: 56, h: 160 },
                platforms: [
                    { x: 0, y: 280, w: 260, h: 60 },
                    { x: 320, y: 248, w: 190, h: 92 },
                    { x: 570, y: 210, w: 170, h: 20 },
                    { x: 790, y: 175, w: 170, h: 20 },
                    { x: 1010, y: 235, w: 180, h: 105 },
                    { x: 1260, y: 195, w: 170, h: 20 },
                    { x: 1490, y: 165, w: 170, h: 20 },
                    { x: 1730, y: 220, w: 200, h: 120 },
                    { x: 1980, y: 280, w: 320, h: 60 },
                    { x: 2340, y: 280, w: 310, h: 60 },
                    { x: 2410, y: 210, w: 160, h: 18 }
                ],
                stars: [
                    { x: 390, y: 210, taken: false },
                    { x: 640, y: 170, taken: false },
                    { x: 860, y: 138, taken: false },
                    { x: 1320, y: 160, taken: false },
                    { x: 1560, y: 130, taken: false },
                    { x: 1810, y: 186, taken: false },
                    { x: 2100, y: 242, taken: false },
                    { x: 2480, y: 176, taken: false }
                ],
                enemies: [
                    { x: 350, y: 216, w: 30, h: 32, minX: 330, maxX: 470, speed: 1.9, dir: 1 },
                    { x: 1040, y: 203, w: 30, h: 32, minX: 1020, maxX: 1160, speed: 2, dir: -1 },
                    { x: 2000, y: 248, w: 30, h: 32, minX: 1990, maxX: 2240, speed: 2.15, dir: 1 },
                    { x: 2470, y: 248, w: 30, h: 32, minX: 2360, maxX: 2600, speed: 2.2, dir: -1 }
                ]
            },
            {
                worldWidth: 2900,
                spawn: { x: 50, y: 145 },
                goal: { x: 2780, y: 150, w: 56, h: 130 },
                platforms: [
                    { x: 0, y: 280, w: 220, h: 60 },
                    { x: 280, y: 245, w: 160, h: 95 },
                    { x: 500, y: 210, w: 150, h: 20 },
                    { x: 710, y: 180, w: 140, h: 20 },
                    { x: 910, y: 220, w: 200, h: 120 },
                    { x: 1170, y: 180, w: 150, h: 20 },
                    { x: 1390, y: 150, w: 160, h: 20 },
                    { x: 1620, y: 200, w: 200, h: 140 },
                    { x: 1880, y: 170, w: 160, h: 20 },
                    { x: 2100, y: 220, w: 190, h: 120 },
                    { x: 2330, y: 280, w: 170, h: 60 },
                    { x: 2540, y: 245, w: 160, h: 95 },
                    { x: 2750, y: 280, w: 150, h: 60 }
                ],
                stars: [
                    { x: 340, y: 206, taken: false },
                    { x: 560, y: 172, taken: false },
                    { x: 770, y: 142, taken: false },
                    { x: 1230, y: 146, taken: false },
                    { x: 1460, y: 118, taken: false },
                    { x: 1690, y: 168, taken: false },
                    { x: 1940, y: 138, taken: false },
                    { x: 2160, y: 188, taken: false },
                    { x: 2600, y: 210, taken: false },
                    { x: 2820, y: 246, taken: false }
                ],
                enemies: [
                    { x: 300, y: 213, w: 30, h: 32, minX: 290, maxX: 430, speed: 2, dir: 1 },
                    { x: 940, y: 188, w: 30, h: 32, minX: 925, maxX: 1070, speed: 2.1, dir: -1 },
                    { x: 1640, y: 168, w: 30, h: 32, minX: 1635, maxX: 1790, speed: 2.2, dir: 1 },
                    { x: 2130, y: 188, w: 30, h: 32, minX: 2115, maxX: 2270, speed: 2.25, dir: -1 },
                    { x: 2580, y: 213, w: 30, h: 32, minX: 2550, maxX: 2690, speed: 2.3, dir: 1 }
                ]
            },
            {
                worldWidth: 3300,
                spawn: { x: 50, y: 160 },
                goal: { x: 3180, y: 135, w: 60, h: 145 },
                platforms: [
                    { x: 0, y: 280, w: 210, h: 60 },
                    { x: 260, y: 240, w: 170, h: 100 },
                    { x: 490, y: 200, w: 150, h: 24 },
                    { x: 700, y: 165, w: 150, h: 24 },
                    { x: 900, y: 130, w: 150, h: 24 },
                    { x: 1100, y: 190, w: 170, h: 150 },
                    { x: 1325, y: 150, w: 150, h: 24 },
                    { x: 1530, y: 115, w: 160, h: 24 },
                    { x: 1745, y: 175, w: 180, h: 165 },
                    { x: 1990, y: 140, w: 150, h: 24 },
                    { x: 2200, y: 105, w: 150, h: 24 },
                    { x: 2410, y: 170, w: 190, h: 170 },
                    { x: 2650, y: 280, w: 200, h: 60 },
                    { x: 2900, y: 245, w: 170, h: 95 },
                    { x: 3120, y: 280, w: 180, h: 60 }
                ],
                stars: [
                    { x: 320, y: 200, taken: false },
                    { x: 540, y: 160, taken: false },
                    { x: 750, y: 124, taken: false },
                    { x: 950, y: 92, taken: false },
                    { x: 1360, y: 122, taken: false },
                    { x: 1580, y: 88, taken: false },
                    { x: 2030, y: 110, taken: false },
                    { x: 2240, y: 76, taken: false },
                    { x: 2470, y: 136, taken: false },
                    { x: 2700, y: 242, taken: false },
                    { x: 2960, y: 210, taken: false },
                    { x: 3210, y: 246, taken: false }
                ],
                enemies: [
                    { x: 290, y: 208, w: 30, h: 32, minX: 275, maxX: 415, speed: 2.2, dir: 1 },
                    { x: 1130, y: 158, w: 30, h: 32, minX: 1110, maxX: 1240, speed: 2.35, dir: -1 },
                    { x: 1770, y: 143, w: 30, h: 32, minX: 1760, maxX: 1900, speed: 2.45, dir: 1 },
                    { x: 2430, y: 138, w: 30, h: 32, minX: 2420, maxX: 2580, speed: 2.55, dir: -1 },
                    { x: 2940, y: 213, w: 30, h: 32, minX: 2920, maxX: 3050, speed: 2.6, dir: 1 }
                ]
            }
        ]
    };

    function updateGameViewport() {
        if (!gameCanvas) return;
        game.isMobileView = window.matchMedia('(max-width: 768px)').matches;
        game.viewScale = game.isMobileView ? 1.3 : 1;
    }

    function updateGameHud() {
        if (!gameCanvas) return;
        $('game-level').textContent = String(game.currentLevel + 1);
        $('game-level-total').textContent = String(game.levels.length);
        $('game-lives').textContent = String(game.lives);
        $('game-stars').textContent = String(game.stars);
    }

    function setGameStatus(msg, color = '#9f1239') {
        if (!gameCanvas) return;
        const el = $('game-status');
        el.textContent = msg;
        el.style.color = color;
    }

    function levelData() {
        return game.levels[game.currentLevel];
    }

    function resetPlayerPosition() {
        const lvl = levelData();
        game.player.x = lvl.spawn.x;
        game.player.y = lvl.spawn.y;
        game.player.vx = 0;
        game.player.vy = 0;
        game.player.onGround = false;
        game.cameraX = 0;
    }

    function loadCurrentLevel() {
        const lvl = levelData();
        lvl.stars.forEach(star => { star.taken = false; });
        lvl.enemies.forEach(enemy => {
            enemy.dir = enemy.dir >= 0 ? 1 : -1;
            enemy.alive = true;
        });
        game.levelCompleted = false;
        game.showFinalMsg = false;
        $('btn-game-next').disabled = true;
        resetPlayerPosition();
        updateGameHud();
    }

    function intersects(a, b) {
        return a.x < b.x + b.w && a.x + a.w > b.x && a.y < b.y + b.h && a.y + a.h > b.y;
    }

    function loseLife(reason) {
        game.lives -= 1;
        if (game.lives <= 0) {
            game.lives = 3;
            game.stars = 0;
            loadCurrentLevel();
            setGameStatus(`You lost all lives. Level ${game.currentLevel + 1} restarted.`, '#be123c');
            return;
        }
        resetPlayerPosition();
        setGameStatus(`${reason} Lives left: ${game.lives}.`, '#be123c');
        updateGameHud();
    }

    function completeLevel() {
        game.levelCompleted = true;
        game.running = false;
        const isLast = game.currentLevel === game.levels.length - 1;
        if (isLast) {
            game.showFinalMsg = true;
            setGameStatus('', '#047857');
            $('btn-game-next').disabled = true;
            return;
        }
        $('btn-game-next').disabled = false;
        setGameStatus(`Level ${game.currentLevel + 1} complete. Tap Next Level.`, '#047857');
    }

    function updateGame() {
        if (!gameCanvas || !game.running) return;

        const lvl = levelData();
        const player = game.player;
        const prevBottom = player.y + player.h;

        player.vx = 0;
        if (game.keys.left) player.vx = -3.5;
        if (game.keys.right) player.vx = 3.5;

        if (game.jumpQueued && player.onGround) {
            player.vy = game.jumpBoost ? -14.2 : -10.8;
            player.onGround = false;
            game.jumpBoost = false;
        }
        game.jumpQueued = false;

        player.vy += 0.54;
        if (player.vy > 11) player.vy = 11;

        player.x += player.vx;
        lvl.platforms.forEach(pl => {
            if (!intersects(player, pl)) return;
            if (player.vx > 0) player.x = pl.x - player.w;
            else if (player.vx < 0) player.x = pl.x + pl.w;
        });

        player.y += player.vy;
        player.onGround = false;
        lvl.platforms.forEach(pl => {
            if (!intersects(player, pl)) return;
            if (player.vy > 0) {
                player.y = pl.y - player.h;
                player.vy = 0;
                player.onGround = true;
            } else if (player.vy < 0) {
                player.y = pl.y + pl.h;
                player.vy = 0;
            }
        });

        if (player.x < 0) player.x = 0;
        if (player.x + player.w > lvl.worldWidth) player.x = lvl.worldWidth - player.w;

        const viewHeight = gameCanvas.height / game.viewScale;
        if (player.y > viewHeight + 120) {
            loseLife('You fell from the castle path.');
            return;
        }

        let wasDamaged = false;
        lvl.enemies.forEach(enemy => {
            if (!enemy.alive) return;

            enemy.x += enemy.speed * enemy.dir;
            if (enemy.x <= enemy.minX || enemy.x + enemy.w >= enemy.maxX) {
                enemy.dir *= -1;
            }

            if (intersects(player, enemy)) {
                const stompHit = player.vy >= 0 && prevBottom <= enemy.y + 12;
                if (stompHit) {
                    enemy.alive = false;
                    player.vy = -8.4;
                    player.y = enemy.y - player.h - 1;
                    setGameStatus('Great jump! You defeated a guardian.', '#047857');
                    return;
                }

                wasDamaged = true;
            }
        });

        if (wasDamaged) {
            loseLife('A guardian blocked your way.');
            return;
        }

        lvl.stars.forEach(star => {
            if (star.taken) return;
            const hit = intersects(player, { x: star.x - 8, y: star.y - 8, w: 16, h: 16 });
            if (!hit) return;
            star.taken = true;
            game.stars += 1;
            updateGameHud();
            setGameStatus(`Star collected. Total stars: ${game.stars}.`, '#9f1239');
        });

        if (intersects(player, lvl.goal)) {
            completeLevel();
        }

        const viewWidth = gameCanvas.width / game.viewScale;
        const maxCamera = Math.max(0, lvl.worldWidth - viewWidth);
        game.cameraX = Math.max(0, Math.min(maxCamera, player.x - viewWidth * 0.35));
    }

    function drawCloud(x, y, size) {
        gameCtx.beginPath();
        gameCtx.arc(x, y, size, Math.PI * 0.5, Math.PI * 1.5);
        gameCtx.arc(x + size, y - size * 0.35, size * 1.08, Math.PI, Math.PI * 2);
        gameCtx.arc(x + size * 2, y, size * 0.9, Math.PI * 1.5, Math.PI * 0.5);
        gameCtx.closePath();
        gameCtx.fill();
    }

    function drawPrincessSprite(x, y, w, h) {
        const centerX = x + w / 2;

        gameCtx.fillStyle = '#f59e0b';
        gameCtx.beginPath();
        gameCtx.moveTo(centerX - 11, y - 4);
        gameCtx.lineTo(centerX - 5, y - 11);
        gameCtx.lineTo(centerX, y - 3);
        gameCtx.lineTo(centerX + 5, y - 11);
        gameCtx.lineTo(centerX + 11, y - 4);
        gameCtx.closePath();
        gameCtx.fill();

        gameCtx.fillStyle = '#7f1d1d';
        gameCtx.beginPath();
        gameCtx.arc(centerX, y + 10, 11, Math.PI, 0);
        gameCtx.fill();

        gameCtx.fillStyle = '#fde2d2';
        gameCtx.beginPath();
        gameCtx.arc(centerX, y + 11, 9, 0, Math.PI * 2);
        gameCtx.fill();

        gameCtx.fillStyle = '#1f2937';
        gameCtx.fillRect(centerX - 4, y + 10, 2, 2);
        gameCtx.fillRect(centerX + 2, y + 10, 2, 2);

        const dress = gameCtx.createLinearGradient(x, y + 17, x, y + h);
        dress.addColorStop(0, '#ec4899');
        dress.addColorStop(1, '#be185d');
        gameCtx.fillStyle = dress;
        gameCtx.beginPath();
        gameCtx.moveTo(centerX, y + 17);
        gameCtx.lineTo(x + 3, y + h);
        gameCtx.lineTo(x + w - 3, y + h);
        gameCtx.closePath();
        gameCtx.fill();

        gameCtx.fillStyle = '#fbcfe8';
        gameCtx.fillRect(centerX - 14, y + 24, 9, 5);
        gameCtx.fillRect(centerX + 5, y + 24, 9, 5);

        gameCtx.fillStyle = '#9d174d';
        gameCtx.fillRect(centerX - 10, y + h - 4, 8, 4);
        gameCtx.fillRect(centerX + 2, y + h - 4, 8, 4);
    }

    function drawEnemySprite(enemy) {
        const x = enemy.x;
        const y = enemy.y;
        const w = enemy.w;
        const h = enemy.h;

        const body = gameCtx.createLinearGradient(x, y, x, y + h);
        body.addColorStop(0, '#be185d');
        body.addColorStop(1, '#831843');
        gameCtx.fillStyle = body;
        gameCtx.beginPath();
        gameCtx.roundRect(x, y + 4, w, h - 4, 7);
        gameCtx.fill();

        gameCtx.fillStyle = '#fecdd3';
        gameCtx.beginPath();
        gameCtx.arc(x + w * 0.35, y + h * 0.4, 3.2, 0, Math.PI * 2);
        gameCtx.arc(x + w * 0.65, y + h * 0.4, 3.2, 0, Math.PI * 2);
        gameCtx.fill();

        gameCtx.fillStyle = '#3f0d23';
        gameCtx.fillRect(x + w * 0.22, y + h - 4, 8, 4);
        gameCtx.fillRect(x + w * 0.55, y + h - 4, 8, 4);
    }

    function drawGame() {
        if (!gameCanvas || !gameCtx) return;

        const w = gameCanvas.width;
        const h = gameCanvas.height;
        gameCtx.clearRect(0, 0, w, h);

        const bg = gameCtx.createLinearGradient(0, 0, 0, h);
        bg.addColorStop(0, '#ffe9f2');
        bg.addColorStop(1, '#ffd7e7');
        gameCtx.fillStyle = bg;
        gameCtx.fillRect(0, 0, w, h);

        const parallax = game.cameraX * 0.35;
        gameCtx.fillStyle = 'rgba(255,255,255,0.8)';
        drawCloud(120 - parallax, 70, 16);
        drawCloud(380 - parallax * 0.9, 52, 18);
        drawCloud(690 - parallax * 0.7, 84, 14);

        const lvl = levelData();
        const scale = game.viewScale;

        gameCtx.save();
        gameCtx.scale(scale, scale);
        gameCtx.translate(-game.cameraX, 0);

        gameCtx.fillStyle = '#f9a8d4';
        lvl.platforms.forEach(pl => {
            gameCtx.fillRect(pl.x, pl.y, pl.w, pl.h);
            gameCtx.fillStyle = '#f472b6';
            gameCtx.fillRect(pl.x, pl.y, pl.w, 9);
            gameCtx.fillStyle = '#f9a8d4';
        });

        lvl.stars.forEach(star => {
            if (star.taken) return;
            gameCtx.fillStyle = '#f59e0b';
            gameCtx.beginPath();
            gameCtx.arc(star.x, star.y, 7, 0, Math.PI * 2);
            gameCtx.fill();
            gameCtx.fillStyle = '#fff7ed';
            gameCtx.fillRect(star.x - 1, star.y - 5, 2, 10);
            gameCtx.fillRect(star.x - 5, star.y - 1, 10, 2);
        });

        lvl.enemies.forEach(enemy => {
            if (!enemy.alive) return;
            drawEnemySprite(enemy);
        });

        gameCtx.fillStyle = '#e11d48';
        gameCtx.fillRect(lvl.goal.x, lvl.goal.y, lvl.goal.w, lvl.goal.h);
        gameCtx.fillStyle = '#fff';
        gameCtx.fillRect(lvl.goal.x + 5, lvl.goal.y + 8, 13, 10);
        gameCtx.fillStyle = '#f59e0b';
        gameCtx.fillRect(lvl.goal.x + 20, lvl.goal.y - 14, 18, 14);

        const p = game.player;
        drawPrincessSprite(p.x, p.y, p.w, p.h);

        gameCtx.restore();

        if (game.showFinalMsg) {
            const mx = w * 0.06;
            const my = h * 0.12;
            const mw2 = w * 0.88;
            const mh2 = h * 0.76;

            gameCtx.fillStyle = 'rgba(255, 228, 240, 0.94)';
            gameCtx.beginPath();
            gameCtx.roundRect(mx, my, mw2, mh2, 22);
            gameCtx.fill();
            gameCtx.strokeStyle = 'rgba(244, 63, 94, 0.45)';
            gameCtx.lineWidth = 2.5;
            gameCtx.stroke();

            const titleSize = Math.max(18, Math.round(h * 0.09));
            gameCtx.fillStyle = '#f59e0b';
            gameCtx.font = 'bold ' + titleSize + 'px Georgia, serif';
            gameCtx.textAlign = 'center';
            gameCtx.textBaseline = 'middle';
            gameCtx.fillText('\uD83D\uDC51 Congratulations! \uD83D\uDC51', w / 2, my + mh2 * 0.22);

            const bodySize = Math.max(13, Math.round(h * 0.053));
            gameCtx.font = bodySize + 'px Georgia, serif';
            gameCtx.fillStyle = '#9f1239';
            const FINAL_MSG = 'My love, I worked on this game to cheer you up when you are tired. I love you so much, and you are my everything. \u2764\uFE0F';
            const maxLineW = mw2 * 0.84;
            const lineHeight = bodySize * 1.65;
            let lineY = my + mh2 * 0.44;
            const words = FINAL_MSG.split(' ');
            let lineBuf = '';
            words.forEach(word => {
                const test = lineBuf ? lineBuf + ' ' + word : word;
                if (gameCtx.measureText(test).width > maxLineW && lineBuf) {
                    gameCtx.fillText(lineBuf, w / 2, lineY);
                    lineBuf = word;
                    lineY += lineHeight;
                } else {
                    lineBuf = test;
                }
            });
            if (lineBuf) gameCtx.fillText(lineBuf, w / 2, lineY);

            gameCtx.textBaseline = 'alphabetic';
            gameCtx.textAlign = 'left';
        }
    }

    function gameLoop() {
        if (!gameCanvas) return;
        updateGame();
        drawGame();
        requestAnimationFrame(gameLoop);
    }

    function ensureGameLoop() {
        if (game.loopStarted || !gameCanvas) return;
        game.loopStarted = true;
        requestAnimationFrame(gameLoop);
    }

    function startPrincessGame() {
        if (!gameCanvas) return;
        game.currentLevel = 0;
        game.lives = 3;
        game.stars = 0;
        loadCurrentLevel();
        game.running = true;
        setGameStatus('Adventure started. Reach the pink castle gate.', '#9f1239');
        ensureGameLoop();
    }

    function restartPrincessLevel() {
        if (!gameCanvas) return;
        loadCurrentLevel();
        game.running = true;
        setGameStatus(`Level ${game.currentLevel + 1} restarted.`, '#9f1239');
        ensureGameLoop();
    }

    function nextPrincessLevel() {
        if (!gameCanvas) return;
        if (game.currentLevel >= game.levels.length - 1) return;
        game.currentLevel += 1;
        loadCurrentLevel();
        game.running = true;
        setGameStatus(`Welcome to level ${game.currentLevel + 1}.`, '#9f1239');
        ensureGameLoop();
    }

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

    window.addEventListener('keydown', event => {
        const key = event.key.toLowerCase();
        if (key === 'arrowleft' || key === 'a') game.keys.left = true;
        if (key === 'arrowright' || key === 'd') game.keys.right = true;
        if (key === 'arrowup' || key === 'w' || key === ' ') {
            game.jumpBoost = false;
            game.jumpQueued = true;
        }
        if (['arrowleft', 'arrowright', 'arrowup', ' ', 'a', 'd', 'w'].includes(key)) {
            event.preventDefault();
        }
    });

    window.addEventListener('keyup', event => {
        const key = event.key.toLowerCase();
        if (key === 'arrowleft' || key === 'a') game.keys.left = false;
        if (key === 'arrowright' || key === 'd') game.keys.right = false;
    });

    window.addEventListener('resize', () => {
        updateGameViewport();
    });

    function bindTouchControl(buttonId, onPress, onRelease) {
        const btn = $(buttonId);
        if (!btn) return;

        const press = event => {
            event.preventDefault();
            onPress();
        };

        const release = event => {
            event.preventDefault();
            onRelease();
        };

        btn.addEventListener('touchstart', press, { passive: false });
        btn.addEventListener('touchend', release, { passive: false });
        btn.addEventListener('touchcancel', release, { passive: false });
        btn.addEventListener('mousedown', press);
        btn.addEventListener('mouseup', release);
        btn.addEventListener('mouseleave', release);
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

    if (gameCanvas) {
        updateGameViewport();
        $('btn-game-start').addEventListener('click', startPrincessGame);
        $('btn-game-restart').addEventListener('click', restartPrincessLevel);
        $('btn-game-next').addEventListener('click', nextPrincessLevel);

        bindTouchControl('btn-touch-left', () => { game.keys.left = true; }, () => { game.keys.left = false; });
        bindTouchControl('btn-touch-right', () => { game.keys.right = true; }, () => { game.keys.right = false; });
        bindTouchControl('btn-touch-jump', () => {
            const now = Date.now();
            game.jumpBoost = (now - game.lastJumpTap) < 260;
            game.lastJumpTap = now;
            game.jumpQueued = true;
        }, () => {});

        updateGameHud();
        drawGame();
    }

    $('btn-mezclar').addEventListener('click', reiniciarPuzzleActual);
    updateAudioButton();
    </script>
</body>
</html>
