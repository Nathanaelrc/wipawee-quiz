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

$appConfigPath = __DIR__ . '/config/app.php';
$appConfig = file_exists($appConfigPath) ? (require $appConfigPath) : [];
if (!is_array($appConfig)) {
    $appConfig = [];
}

$defaultQuestions = [
    [
        'pregunta' => '1) How long have we been together?',
        'opciones' => ['8 months', '1 year and 2 months', '1 year and 10 months', '2 years and 3 months', '6 months'],
        'correcta' => 2,
    ],
    [
        'pregunta' => '2) What is my favorite color?',
        'opciones' => ['Light blue', 'Red', 'Dark green', 'Dark blue', 'Black'],
        'correcta' => 3,
    ],
    [
        'pregunta' => '3) What color are my eyes?',
        'opciones' => ['Green', 'Light brown', 'Blue', 'Black', 'Dark brown'],
        'correcta' => 4,
    ],
    [
        'pregunta' => '4) What food do I dislike?',
        'opciones' => ['Tomatoes', 'Garlic', 'Peppers', 'Onions', 'Mushrooms'],
        'correcta' => 3,
    ],
    [
        'pregunta' => '5) On what day did I ask you to be my girlfriend?',
        'opciones' => ['July 5th', 'July 20th', 'June 10th', 'August 14th', 'July 10th'],
        'correcta' => 4,
    ],
    [
        'pregunta' => '6) What color are my glasses?',
        'opciones' => ['Brown', 'Silver', 'Blue', 'Black', 'Transparent'],
        'correcta' => 3,
    ],
    [
        'pregunta' => '7) What do I love most about you?',
        'opciones' => ['The way you speak', 'Your smile only', 'Your sense of humor', 'Your kindness', 'Everything, because you are perfect'],
        'correcta' => 4,
    ],
    [
        'pregunta' => '8) Why do I love talking to you and calling you every day?',
        'opciones' => ['Because I have free time', 'Because we have a lot in common', 'Because my life without you cannot be happy, and you are my everything', 'Because I miss your voice', 'Because I like video calls'],
        'correcta' => 2,
    ],
];

$questionsFile = (string) ($appConfig['questions_file'] ?? '');
$preguntasQuiz = $defaultQuestions;
if ($questionsFile !== '' && is_file($questionsFile) && is_readable($questionsFile)) {
    $questionsRaw = file_get_contents($questionsFile);
    $questionsDecoded = is_string($questionsRaw) ? json_decode($questionsRaw, true) : null;
    if (is_array($questionsDecoded) && !empty($questionsDecoded)) {
        $preguntasQuiz = $questionsDecoded;
    }
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
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@400;500;700;800&family=Nunito+Sans:wght@400;500;700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

    <link rel="stylesheet" href="assets/css/app.css">
</head>

<body class="d-flex align-items-center justify-content-center py-4">
    <button id="btn-audio" class="audio-toggle" type="button" aria-pressed="false">🔇 Music: Off</button>

    <!-- Princess Aurora Enchanted Background -->
    <div class="aurora">
        <div class="blob one"></div>   <!-- Flora — pink fairy -->
        <div class="blob two"></div>   <!-- Merryweather — blue fairy -->
        <div class="blob three"></div> <!-- Fauna — lavender magic -->
        <div class="blob four"></div>  <!-- Royal golden sparkle -->

        <!-- Fairy dust sparkles floating around the enchanted kingdom -->
        <span class="spark" style="top:5%;left:6%;font-size:1.5rem;animation-delay:.2s;animation-name:floatBounce;animation-duration:3.8s;">&#x2728;</span>
        <span class="spark" style="top:9%;right:7%;font-size:1.1rem;animation-delay:1.4s;animation-name:glimmer;animation-duration:4.2s;">&#x2B50;</span>
        <span class="spark" style="top:19%;left:18%;font-size:1.0rem;animation-delay:2.8s;animation-name:glimmer;animation-duration:3.6s;">&#x2728;</span>
        <span class="spark" style="top:14%;right:22%;font-size:1.6rem;animation-delay:.6s;animation-name:floatBounce;animation-duration:4.5s;">&#x1F451;</span>
        <span class="spark" style="top:68%;left:4%;font-size:1.2rem;animation-delay:1.9s;animation-name:floatBounce;animation-duration:3.9s;">&#x1F338;</span>
        <span class="spark" style="top:78%;right:6%;font-size:1.3rem;animation-delay:3.2s;animation-name:glimmer;animation-duration:4.8s;">&#x2728;</span>
        <span class="spark" style="top:86%;left:14%;font-size:1.0rem;animation-delay:.9s;animation-name:glimmer;animation-duration:3.5s;">&#x2B50;</span>
        <span class="spark" style="top:91%;right:18%;font-size:1.4rem;animation-delay:2.1s;animation-name:floatBounce;animation-duration:5.0s;">&#x1F339;</span>
    </div>

    <div class="container" style="max-width: 980px; position: relative; z-index: 2;">
        <div class="tab-nav-wrap mb-3">
            <button id="btn-tab-prev" class="tab-arrow" type="button" aria-label="Previous tab" disabled>&#x2190;</button>
            <div class="tab-shell-inner">
            <div class="tab-shell">
            <ul class="nav nav-pills tab-love gap-2 justify-content-center" id="loveTabs" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="tab-quiz-btn" data-bs-toggle="tab" data-bs-target="#tab-quiz" type="button" role="tab" aria-controls="tab-quiz" aria-selected="true">Love Quiz + Puzzle</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-game-btn" data-bs-toggle="tab" data-bs-target="#tab-princess" type="button" role="tab" aria-controls="tab-princess" aria-selected="false">Princess Adventure</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-memory-btn" data-bs-toggle="tab" data-bs-target="#tab-memory" type="button" role="tab" aria-controls="tab-memory" aria-selected="false">Memory Game 💞</button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="tab-catcher-btn" data-bs-toggle="tab" data-bs-target="#tab-catcher" type="button" role="tab" aria-controls="tab-catcher" aria-selected="false">Catch My Love &#x1F48C;</button>
                </li>
            </ul>
        </div>
            </div>
            <button id="btn-tab-next" class="tab-arrow" type="button" aria-label="Next tab">&#x2192;</button>
        </div>

        <div class="tab-content">
            <div class="tab-pane fade show active" id="tab-quiz" role="tabpanel" aria-labelledby="tab-quiz-btn" tabindex="0">
                <div class="quiz-pane-wrap">
        <div id="screen-inicio" class="card-romantic p-4 p-md-5 text-center fade-in">
            <div class="icon-heartbeat" style="font-size: 3.4rem; line-height:1;">&#x1F496;</div>
            <h1 class="font-title fw-bold mb-2" style="font-size: clamp(1.9rem, 6vw, 2.6rem); color: #a5164d;">
                Happy Anniversary, My Love 🌹
            </h1>
            <p class="font-title fst-italic mb-1" style="color:#be185d; font-size:clamp(1rem,3.5vw,1.2rem);">
                1 year &amp; 10 months loving you with everything I have 💕
            </p>
            <p class="mx-auto text-secondary mb-4" style="max-width: 480px; line-height: 1.75;">
                Baby I prepared <strong>8 little questions for you</strong>, a puzzle of our photos,
                and a letter written straight from my heart. You are my everything, Sweetie and I want to spend my life with you. 💖
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

            <?php
            $fotosNuevas = array_values(array_filter($imagenesPuzzle, fn($f) => preg_match('/^img\/im\d/', $f)));
            if (!empty($fotosNuevas)):
            ?>
            <div class="d-flex justify-content-center gap-3 mb-4">
                <?php foreach ($fotosNuevas as $foto): ?>
                <div style="width:88px;height:88px;border-radius:50%;overflow:hidden;border:3px solid rgba(244,63,94,0.35);box-shadow:0 8px 22px rgba(244,63,94,0.22);flex-shrink:0;">
                    <img src="<?php echo htmlspecialchars($foto, ENT_QUOTES, 'UTF-8'); ?>" alt="Our memory" loading="lazy" style="width:100%;height:100%;object-fit:cover;">
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <button id="btn-iniciar" class="btn btn-love">Open my heart for you 💌</button>
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
                <?php echo htmlspecialchars((string) ($appConfig['victory_footer_text'] ?? 'HAPPY ANNIVERSARY MY LOVE 1 YEAR AND 10 MONTHS, UNTIL THE ETERNITY YOU AND ME.'), ENT_QUOTES, 'UTF-8'); ?>
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

            <div class="tab-pane fade" id="tab-memory" role="tabpanel" aria-labelledby="tab-memory-btn" tabindex="0">
                <div class="card-romantic p-4 p-md-5">
                    <div class="text-center mb-4">
                        <div style="font-size:2.5rem;line-height:1;">💞</div>
                        <h2 class="font-title fw-bold mb-1" style="font-size:clamp(1.5rem,4vw,2rem);color:#a5164d;">Memory of Our Love</h2>
                        <p class="text-secondary mb-0">Find matching pairs - personalized just for us 💕</p>
                    </div>

                    <div class="d-flex justify-content-center gap-3 mb-4 flex-wrap">
                        <span class="game-chip">Matches: <span id="memory-matches">0</span> / <span id="memory-total">6</span></span>
                        <span class="game-chip">Moves: <span id="memory-moves">0</span></span>
                        <span class="game-chip">Score: <span id="memory-score">0</span>%</span>
                    </div>

                    <div id="memory-board" class="d-flex justify-content-center flex-wrap gap-3 mb-4" style="max-width:500px;margin:0 auto;"></div>

                    <div class="text-center">
                        <button id="btn-memory-start" class="btn btn-love me-2">Start Game</button>
                        <button id="btn-memory-restart" class="btn btn-gold">Restart</button>
                    </div>

                    <p id="memory-status" class="text-center fw-semibold mt-4 mb-0" style="color:#9f1239;min-height:24px;"></p>
                </div>
            </div>

            <div class="tab-pane fade" id="tab-catcher" role="tabpanel" aria-labelledby="tab-catcher-btn" tabindex="0">
                <div class="card-romantic princess-game-card">
                    <div class="text-center mb-3">
                        <div style="font-size:2.2rem;line-height:1;">&#x1F30D;</div>
                        <h2 class="font-title fw-bold mb-1" style="font-size:clamp(1.5rem,4vw,2rem);color:#a5164d;">Catch My Love</h2>
                        <p class="text-secondary mb-1">Catch everything I send you from across the world &#x1F495;</p>
                        <small class="text-secondary" style="font-size:.83rem;">Words from my letter fly to you &mdash; catch them all! &#x1F48C;</small>
                    </div>

                    <div class="d-flex flex-wrap gap-2 justify-content-center mb-2">
                        <span class="game-chip">Lives: <span id="catcher-lives">&#x2764;&#xFE0F;&#x2764;&#xFE0F;&#x2764;&#xFE0F;</span></span>
                        <span class="game-chip">Love meter: <span id="catcher-love">0</span>%</span>
                    </div>

                    <div class="catcher-love-shell mb-3">
                        <div id="catcher-love-bar" class="catcher-love-fill"></div>
                    </div>

                    <canvas id="catcher-canvas" class="catcher-canvas" width="960" height="380" aria-label="Catch My Love game"></canvas>

                    <p id="catcher-status" class="text-center fw-semibold mt-3 mb-3" style="color:#9f1239;min-height:24px;">Press Start to receive my love from Chile! &#x1F48C;</p>

                    <div class="d-flex justify-content-center flex-wrap gap-2 mb-2">
                        <button id="btn-catcher-start" class="btn btn-love" type="button">Start Catching &#x1F48C;</button>
                        <button id="btn-catcher-restart" class="btn btn-gold" type="button">Restart</button>
                    </div>

                    <div class="game-touch-controls" aria-label="Touch controls for mobile">
                        <div class="touch-pad-left">
                            <button id="btn-catcher-left" class="game-touch-btn" type="button">&#x2190;</button>
                            <button id="btn-catcher-right" class="game-touch-btn" type="button">&#x2192;</button>
                        </div>
                        <div class="touch-pad-right"></div>
                    </div>

                    <p class="text-center text-secondary mb-0" style="font-size:.9rem;">Controls: <strong>&#x2190; / &#x2192;</strong> or <strong>A / D</strong> &mdash; catch &#x1F48C; &#x1F339; &#x1F495; &#x2B50; and avoid &#x1F494; broken hearts!</p>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script nonce="<?php echo htmlspecialchars($cspNonce, ENT_QUOTES, 'UTF-8'); ?>">
    window.APP_CONFIG = {
        imagenesPuzzle: <?php echo json_encode($imagenesPuzzle, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
        csrfToken: <?php echo json_encode($csrfToken, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
        passingScore: <?php echo json_encode($appConfig['passing_score'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>,
        preguntas: <?php echo json_encode($preguntasQuiz, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE); ?>
    };
    </script>
    <script src="assets/js/app.js"></script>
</body>
</html>
