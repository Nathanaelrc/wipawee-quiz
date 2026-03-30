<?php
/**
 * index.php — Quiz de Trivia Romántica
 *
 * ✏️  PERSONALIZACIÓN RÁPIDA:
 *   1. Edita el array `preguntas` en el JS (busca "PERSONALIZA TUS PREGUNTAS").
 *   2. Edita el mensaje de amor en api.php.
 *   3. Cambia el nombre en la pantalla de inicio si lo deseas.
 */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>How well do you know me? 💕</title>

    <!-- ── Bootstrap 5 ───────────────────────────────────────── -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- ── Tailwind CSS ──────────────────────────────────────── -->
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        rosa:       '#f43f5e',
                        'rosa-mid': '#fb7185',
                        'rosa-pal': '#fda4af',
                        dorado:     '#f59e0b',
                        crema:      '#fffbeb',
                    },
                    fontFamily: {
                        serif: ['"Playfair Display"', 'Georgia', 'serif'],
                        sans:  ['"Lato"', 'sans-serif'],
                    },
                    backdropBlur: { md: '12px' },
                }
            }
        }
    </script>

    <!-- ── Google Fonts ──────────────────────────────────────── -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:ital,wght@0,400;0,700;1,400&family=Lato:wght@300;400;700&display=swap" rel="stylesheet">

    <!-- ── canvas-confetti ───────────────────────────────────── -->
    <script src="https://cdn.jsdelivr.net/npm/canvas-confetti@1.9.2/dist/confetti.browser.min.js"></script>

    <style>
        /* ─── Variables ─────────────────────────────────────── */
        :root {
            --rosa:    #f43f5e;
            --dorado:  #f59e0b;
        }

        /* ─── Base ───────────────────────────────────────────── */
        body {
            font-family: 'Lato', sans-serif;
            background: linear-gradient(145deg, #fff1f2 0%, #fce7f3 55%, #fef3c7 100%);
            min-height: 100vh;
        }
        .playfair { font-family: 'Playfair Display', serif; }

        /* ─── Glassmorphism card ─────────────────────────────── */
        .card-quiz {
            background: rgba(255, 255, 255, 0.82);
            backdrop-filter: blur(14px);
            -webkit-backdrop-filter: blur(14px);
            border: 1px solid rgba(255, 255, 255, 0.65);
        }

        /* ─── Fade-in ────────────────────────────────────────── */
        .fade-in {
            animation: fadeIn 0.55s cubic-bezier(.4,0,.2,1) both;
        }
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(18px); }
            to   { opacity: 1; transform: translateY(0); }
        }

        /* ─── Slide question ─────────────────────────────────── */
        .slide-in {
            animation: slideIn 0.45s cubic-bezier(.4,0,.2,1) both;
        }
        @keyframes slideIn {
            from { opacity: 0; transform: translateX(30px); }
            to   { opacity: 1; transform: translateX(0); }
        }

        /* ─── Shake (respuesta incorrecta) ───────────────────── */
        .shake {
            animation: shake 0.48s ease both;
        }
        @keyframes shake {
            0%,100% { transform: translateX(0); }
            18%     { transform: translateX(-9px); }
            36%     { transform: translateX(9px); }
            54%     { transform: translateX(-6px); }
            72%     { transform: translateX(6px); }
        }

        /* ─── Pulse (score al acertar) ───────────────────────── */
        .pulse-once {
            animation: pulseOnce 0.5s ease both;
        }
        @keyframes pulseOnce {
            0%   { transform: scale(1); }
            40%  { transform: scale(1.28); }
            70%  { transform: scale(1.1); }
            100% { transform: scale(1); }
        }

        /* ─── Floating hearts (decoración) ───────────────────── */
        .floating { animation: floatHeart 3.2s ease-in-out infinite; }
        @keyframes floatHeart {
            0%,100% { transform: translateY(0) rotate(-4deg); }
            50%     { transform: translateY(-12px) rotate(4deg); }
        }

        /* ─── Progress bar ───────────────────────────────────── */
        .progress-bar-rosa {
            background: linear-gradient(90deg, var(--rosa), #fb923c);
            transition: width 0.55s cubic-bezier(.4,0,.2,1);
        }

        /* ─── Botón de respuesta ─────────────────────────────── */
        .btn-respuesta {
            background: rgba(255, 255, 255, 0.72);
            border: 1.5px solid rgba(244, 63, 94, 0.22);
            color: #374151;
            font-size: 0.93rem;
            text-align: left;
            border-radius: 0.65rem;
            padding: 0.75rem 1.1rem;
            transition: all 0.22s ease;
            cursor: pointer;
            width: 100%;
        }
        .btn-respuesta:hover:not(:disabled) {
            background: linear-gradient(135deg, rgba(244,63,94,0.07), rgba(251,113,133,0.07));
            border-color: var(--rosa);
            color: #be185d;
            transform: translateY(-2px);
            box-shadow: 0 4px 14px rgba(244,63,94,0.18);
        }
        .btn-respuesta:disabled { cursor: default; }
        .btn-respuesta.correcta {
            background: linear-gradient(135deg, #d1fae5, #a7f3d0) !important;
            border-color: #10b981 !important;
            color: #064e3b !important;
            box-shadow: 0 4px 14px rgba(16,185,129,0.25) !important;
        }
        .btn-respuesta.incorrecta {
            background: linear-gradient(135deg, #fee2e2, #fecaca) !important;
            border-color: #ef4444 !important;
            color: #7f1d1d !important;
        }

        /* ─── Pergamino / carta de amor ──────────────────────── */
        .pergamino {
            background: linear-gradient(145deg,
                rgba(255,255,255,0.93) 0%,
                rgba(255,249,219,0.93) 100%);
            border: 2px solid rgba(245,158,11,0.38);
            position: relative;
            overflow: hidden;
        }
        .pergamino::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse at 10% 10%,  rgba(245,158,11,0.08) 0%, transparent 55%),
                radial-gradient(ellipse at 90% 90%,  rgba(244,63,94,0.06)  0%, transparent 55%);
            pointer-events: none;
        }
        .pergamino-orla {
            border-top: 1px dashed rgba(245,158,11,0.4);
            border-bottom: 1px dashed rgba(245,158,11,0.4);
        }

        /* ─── Reveal animation ───────────────────────────────── */
        .reveal {
            opacity: 0;
            transform: scale(0.94) translateY(12px);
            transition: opacity 0.75s ease, transform 0.75s ease;
        }
        .reveal.visible {
            opacity: 1;
            transform: scale(1) translateY(0);
        }

        /* ─── Botón primario ─────────────────────────────────── */
        .btn-rosa {
            background: linear-gradient(135deg, #f43f5e, #fb7185);
            border: none;
            color: #fff;
            box-shadow: 0 5px 22px rgba(244,63,94,0.38);
            transition: all 0.22s ease;
        }
        .btn-rosa:hover {
            background: linear-gradient(135deg, #e11d48, #f43f5e);
            box-shadow: 0 8px 28px rgba(244,63,94,0.5);
            transform: translateY(-1px);
            color: #fff;
        }
        .btn-dorado {
            background: linear-gradient(135deg, #f59e0b, #fbbf24);
            border: none;
            color: #fff;
            box-shadow: 0 5px 22px rgba(245,158,11,0.35);
            transition: all 0.22s ease;
        }
        .btn-dorado:hover {
            background: linear-gradient(135deg, #d97706, #f59e0b);
            box-shadow: 0 8px 28px rgba(245,158,11,0.5);
            transform: translateY(-1px);
            color: #fff;
        }
    </style>
</head>

<body class="d-flex flex-column align-items-center justify-content-center py-4" style="min-height:100vh;">

    <!-- ════════ Corazones flotantes de fondo ════════ -->
    <div class="position-fixed top-0 start-0 w-100 h-100 overflow-hidden" style="pointer-events:none; z-index:0;">
        <span class="position-absolute floating" style="top:7%;  left:4%;  font-size:1.6rem; opacity:.13; animation-delay:0s    ">💗</span>
        <span class="position-absolute floating" style="top:13%; right:6%; font-size:2.1rem; opacity:.10; animation-delay:.9s   ">💕</span>
        <span class="position-absolute floating" style="top:55%; left:2%;  font-size:1.3rem; opacity:.14; animation-delay:1.6s  ">🌸</span>
        <span class="position-absolute floating" style="top:72%; right:4%; font-size:1.9rem; opacity:.10; animation-delay:.5s   ">💖</span>
        <span class="position-absolute floating" style="bottom:9%;left:13%;font-size:1.1rem; opacity:.12; animation-delay:2.1s  ">✨</span>
        <span class="position-absolute floating" style="top:38%; right:2%;  font-size:1.5rem; opacity:.09; animation-delay:1.3s ">🌹</span>
        <span class="position-absolute floating" style="top:85%; left:40%; font-size:1.2rem; opacity:.10; animation-delay:0.7s  ">💗</span>
    </div>

    <!-- ════════ Contenedor principal ════════ -->
    <div class="container py-2" style="position:relative; z-index:1; max-width:600px;">


        <!-- ══════════════════════════════════════════════════ -->
        <!--  PANTALLA INICIO                                  -->
        <!-- ══════════════════════════════════════════════════ -->
        <div id="screen-inicio" class="card-quiz rounded-4 shadow-lg p-4 p-md-5 text-center fade-in">

            <div class="mb-3" style="font-size:3.6rem; line-height:1;">💌</div>

            <h1 class="playfair fw-bold mb-2" style="color:#be185d; font-size:clamp(1.65rem,5.5vw,2.25rem);">
                How well do you know me?
            </h1>
            <p class="text-muted mb-4 mx-auto" style="font-size:.93rem; max-width:360px; line-height:1.7;">
                A little romantic quiz made with all my love.<br>
                Answer the <strong>5 questions</strong> and unlock your special gift. 🌹
            </p>

            <!-- Estadísticas decorativas -->
            <div class="d-flex justify-content-center gap-5 mb-4">
                <div class="text-center">
                    <div class="fw-bold" style="color:#f43f5e; font-size:1.6rem;">5</div>
                    <div class="text-muted" style="font-size:.73rem; text-transform:uppercase; letter-spacing:.05em;">Questions</div>
                </div>
                <div class="vr"></div>
                <div class="text-center">
                    <div style="font-size:1.6rem;">💝</div>
                    <div class="text-muted" style="font-size:.73rem; text-transform:uppercase; letter-spacing:.05em;">Prize</div>
                </div>
                <div class="vr"></div>
                <div class="text-center">
                    <div class="fw-bold" style="color:#f43f5e; font-size:1.6rem;">∞</div>
                    <div class="text-muted" style="font-size:.73rem; text-transform:uppercase; letter-spacing:.05em;">Love</div>
                </div>
            </div>

            <button id="btn-iniciar" class="btn btn-rosa btn-lg px-5 py-3 rounded-pill fw-semibold">
                Start the Quiz! 💕
            </button>

            <p class="mt-3 mb-0" style="font-size:.75rem; color:#d1a3b0;">
                Made with ♥ especially for you
            </p>
        </div>


        <!-- ══════════════════════════════════════════════════ -->
        <!--  PANTALLA QUIZ                                    -->
        <!-- ══════════════════════════════════════════════════ -->
        <div id="screen-quiz" class="d-none">

            <!-- Header + score -->
            <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                <span class="badge rounded-pill px-3 py-2"
                      style="background:rgba(244,63,94,.12); color:#be185d; font-size:.82rem;">
                    💕 Romantic Quiz
                </span>
                <span id="label-score" class="badge rounded-pill px-3 py-2"
                      style="background:rgba(245,158,11,.12); color:#b45309; font-size:.82rem;">
                    ⭐ 0 / 5
                </span>
            </div>

            <!-- Barra de progreso -->
            <div class="progress mb-4 rounded-pill" style="height:7px; background:rgba(244,63,94,.12);">
                <div id="barra-progreso" class="progress-bar-rosa rounded-pill h-100" style="width:0%;"></div>
            </div>

            <!-- Tarjeta de pregunta -->
            <div id="tarjeta-pregunta" class="card-quiz rounded-4 shadow-lg p-4 p-md-5">

                <!-- Número de pregunta -->
                <div class="d-flex align-items-center gap-2 mb-3">
                    <div id="num-pregunta"
                         class="d-flex align-items-center justify-content-center rounded-circle fw-bold text-white flex-shrink-0"
                         style="width:38px; height:38px; background:linear-gradient(135deg,#f43f5e,#fb7185); font-size:.9rem;">
                        1
                    </div>
                    <span class="text-muted" style="font-size:.8rem;">of 5 questions</span>
                </div>

                <!-- Texto de la pregunta -->
                <h2 id="texto-pregunta"
                    class="playfair fw-bold mb-4"
                    style="color:#1c1c2e; font-size:clamp(1.05rem,3.8vw,1.3rem); line-height:1.55;">
                </h2>

                <!-- Opciones de respuesta -->
                <div id="contenedor-opciones" class="d-grid gap-3"></div>

                <!-- Feedback inline -->
                <div id="feedback"
                     class="mt-3 text-center fw-semibold"
                     style="min-height:26px; font-size:.92rem; opacity:0; transition:opacity .3s ease;">
                </div>
            </div>
        </div>


        <!-- ══════════════════════════════════════════════════ -->
        <!--  PANTALLA VICTORIA                                -->
        <!-- ══════════════════════════════════════════════════ -->
        <div id="screen-victoria" class="d-none text-center fade-in">

            <div class="mb-2" style="font-size:3rem;">🎉</div>
            <h2 class="playfair fw-bold mb-1" style="color:#be185d; font-size:clamp(1.5rem,5vw,2rem);">
                You did it, my love!
            </h2>
            <p class="text-muted mb-4" style="font-size:.93rem;">
                You answered everything perfectly. Here is your gift... 💝
            </p>

            <!-- ── Carta de amor / Pergamino ── -->
            <div id="carta-amor"
                 class="pergamino rounded-4 shadow-lg p-4 p-md-5 text-start mb-4 reveal">

                <!-- Encabezado -->
                <div class="text-center mb-4">
                    <div style="font-size:2rem;">💌</div>
                    <h3 class="playfair fst-italic fw-bold mt-2 mb-1"
                        style="color:#92400e; font-size:1.25rem;">
                        Love Letter
                    </h3>
                    <div class="pergamino-orla py-1 mx-auto" style="width:55%;">
                        <span style="font-size:.85rem; color:#d97706; letter-spacing:.18em;">❧ ❧ ❧</span>
                    </div>
                </div>

                <!-- Mensaje cargado por fetch() -->
                <div id="mensaje-amor"
                     class="playfair"
                     style="color:#44403c; line-height:2; font-size:clamp(.93rem,3vw,1.05rem);">
                    <!-- Insertado dinámicamente desde api.php -->
                </div>

                <div class="text-center mt-4" style="font-size:1.4rem;">
                    💗 &nbsp; 💗 &nbsp; 💗
                </div>
            </div>

            <button id="btn-rejugar"
                    class="btn btn-dorado px-4 py-2 rounded-pill fw-semibold"
                    style="font-size:.9rem;">
                ♻️ Play Again
            </button>
        </div>


        <!-- ══════════════════════════════════════════════════ -->
        <!--  PANTALLA DERROTA                                 -->
        <!-- ══════════════════════════════════════════════════ -->
        <div id="screen-derrota" class="d-none text-center fade-in">
            <div class="card-quiz rounded-4 shadow-lg p-4 p-md-5">
                <div class="mb-3" style="font-size:3rem;">🥺</div>
                <h2 class="playfair fw-bold mb-2" style="color:#be185d;">So close, my love! 💕</h2>
                <p class="text-muted mb-4" style="font-size:.93rem;">
                    You didn't reach the perfect score, but I know you can do it.<br>
                    Try again, I'm waiting for you!
                </p>
                <div class="rounded-3 p-3 mb-4"
                     style="background:rgba(244,63,94,.07); border:1px solid rgba(244,63,94,.18);">
                    <span class="fw-semibold" style="color:#be185d;">
                        Your score:&ensp;<span id="puntaje-final">0</span> / 5 ⭐
                    </span>
                </div>
                <button id="btn-reintentar"
                        class="btn btn-rosa btn-lg px-5 py-3 rounded-pill fw-semibold">
                    Try Again 🌸
                </button>
            </div>
        </div>

    </div><!-- /container -->


    <!-- Bootstrap 5 JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    /* ============================================================== */
    /*  QUIZ DE TRIVIA ROMÁNTICA                                       */
    /*  ✏️  Personaliza las preguntas en el array `preguntas`          */
    /*     y el mensaje de amor en api.php                            */
    /* ============================================================== */

    const TOTAL = 5;

    /* ──────────────────────────────────────────────────────────── */
    /*  ✏️  PERSONALIZA TUS PREGUNTAS                               */
    /*  · "correcta" es el índice (0-3) de la respuesta correcta.  */
    /* ──────────────────────────────────────────────────────────── */
    const preguntas = [
        {
            pregunta: 'When is my birthday? 🎂',
            opciones:  ['January 7, 1995', 'February 7, 1995', 'March 7, 1995', 'February 17, 1995'],
            correcta:  1   // ← February 7, 1995
        },
        {
            pregunta: 'What is my full name? 😊',
            opciones:  ['Marcos Antonio Rodríguez', 'Marcos Daniel Rodríguez Cerda', 'Marcos Nathanael Rodríguez Cerda', 'Marcos Nathanael García Cerda'],
            correcta:  2   // ← Marcos Nathanael Rodríguez Cerda
        },
        {
            pregunta: 'How old am I? 🎉',
            opciones:  ['29 years old', '30 years old', '31 years old', '32 years old'],
            correcta:  2   // ← 31
        },
        {
            pregunta: 'When did we officially become a couple? 💑',
            opciones:  ['June 10, 2024', 'August 10, 2024', 'July 1, 2024', 'July 10, 2024'],
            correcta:  3   // ← July 10, 2024
        },
        {
            pregunta: 'What special nickname do I call you with the most love? 💕',
            opciones:  ['My Sky', 'My Love', 'Princess', 'Queen'],
            correcta:  2   // ← Princess 👸
        }
    ];

    /* ──────────────────────────────────────────────────────────── */
    /*  Estado del quiz                                             */
    /* ──────────────────────────────────────────────────────────── */
    let indice    = 0;
    let puntaje   = 0;
    let respondido = false;

    /* ──────────────────────────────────────────────────────────── */
    /*  Referencias DOM                                             */
    /* ──────────────────────────────────────────────────────────── */
    const $ = id => document.getElementById(id);

    const pantallaInicio    = $('screen-inicio');
    const pantallaQuiz      = $('screen-quiz');
    const pantallaVictoria  = $('screen-victoria');
    const pantallaDerrota   = $('screen-derrota');

    /* ──────────────────────────────────────────────────────────── */
    /*  Audio: Web Audio API (sin archivos externos)               */
    /* ──────────────────────────────────────────────────────────── */
    function reproducirAcierto() {
        try {
            const ctx   = new (window.AudioContext || window.webkitAudioContext)();
            const notas = [523.25, 659.25, 783.99]; // Do5, Mi5, Sol5
            notas.forEach((freq, i) => {
                const osc  = ctx.createOscillator();
                const gain = ctx.createGain();
                osc.connect(gain);
                gain.connect(ctx.destination);
                osc.type = 'sine';
                const t = ctx.currentTime + i * 0.13;
                osc.frequency.setValueAtTime(freq, t);
                gain.gain.setValueAtTime(0.18, t);
                gain.gain.exponentialRampToValueAtTime(0.001, t + 0.38);
                osc.start(t);
                osc.stop(t + 0.38);
            });
        } catch { /* Audio no disponible */ }
    }

    function reproducirError() {
        try {
            const ctx  = new (window.AudioContext || window.webkitAudioContext)();
            const osc  = ctx.createOscillator();
            const gain = ctx.createGain();
            osc.connect(gain);
            gain.connect(ctx.destination);
            osc.type = 'sawtooth';
            osc.frequency.setValueAtTime(200, ctx.currentTime);
            osc.frequency.exponentialRampToValueAlTime?.(80, ctx.currentTime + 0.32);
            osc.frequency.exponentialRampToValueAtTime(80, ctx.currentTime + 0.32);
            gain.gain.setValueAtTime(0.09, ctx.currentTime);
            gain.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.32);
            osc.start(ctx.currentTime);
            osc.stop(ctx.currentTime + 0.32);
        } catch { /* Audio no disponible */ }
    }

    /* ──────────────────────────────────────────────────────────── */
    /*  Confetti 🎊                                                 */
    /* ──────────────────────────────────────────────────────────── */
    function lanzarConfetti() {
        const colores = ['#f43f5e', '#fb7185', '#fbbf24', '#f59e0b', '#ffffff', '#fda4af'];
        const fin = Date.now() + 3200;
        (function rafLoop() {
            confetti({ particleCount:5, angle:60,  spread:58, origin:{x:0}, colors:colores, scalar:1.15 });
            confetti({ particleCount:5, angle:120, spread:58, origin:{x:1}, colors:colores, scalar:1.15 });
            if (Date.now() < fin) requestAnimationFrame(rafLoop);
        })();
    }

    /* ──────────────────────────────────────────────────────────── */
    /*  Navegación entre pantallas                                 */
    /* ──────────────────────────────────────────────────────────── */
    function mostrarPantalla(id) {
        [pantallaInicio, pantallaQuiz, pantallaVictoria, pantallaDerrota]
            .forEach(el => { el.classList.add('d-none'); el.classList.remove('fade-in'); });

        const target = $(id);
        target.classList.remove('d-none');
        void target.offsetWidth; // reflow — re-dispara la animación
        target.classList.add('fade-in');
    }

    /* ──────────────────────────────────────────────────────────── */
    /*  Renderizar pregunta                                        */
    /* ──────────────────────────────────────────────────────────── */
    function renderizarPregunta() {
        respondido = false;

        const q = preguntas[indice];

        $('num-pregunta').textContent = indice + 1;
        $('texto-pregunta').textContent = q.pregunta;
        $('barra-progreso').style.width = `${(indice / TOTAL) * 100}%`;
        $('label-score').textContent = `⭐ ${puntaje} / ${TOTAL}`;

        const feedback = $('feedback');
        feedback.style.opacity = '0';
        feedback.textContent   = '';

        // Renderizar opciones
        const contenedor = $('contenedor-opciones');
        contenedor.innerHTML = '';
        q.opciones.forEach((opcion, i) => {
            const btn = document.createElement('button');
            btn.className = 'btn-respuesta';
            btn.innerHTML = `<span style="color:#f43f5e; font-weight:700; margin-right:.45rem;">${String.fromCharCode(65 + i)}.</span>${opcion}`;
            btn.addEventListener('click', () => manejarRespuesta(i, btn));
            contenedor.appendChild(btn);
        });

        // Animación slide-in
        const tarjeta = $('tarjeta-pregunta');
        tarjeta.classList.remove('slide-in');
        void tarjeta.offsetWidth;
        tarjeta.classList.add('slide-in');
    }

    /* ──────────────────────────────────────────────────────────── */
    /*  Manejar respuesta                                          */
    /* ──────────────────────────────────────────────────────────── */
    function manejarRespuesta(seleccionado, btnSeleccionado) {
        if (respondido) return;
        respondido = true;

        const q       = preguntas[indice];
        const todos   = $('contenedor-opciones').querySelectorAll('.btn-respuesta');
        const feedback = $('feedback');

        todos.forEach(b => b.disabled = true);

        if (seleccionado === q.correcta) {
            /* ✅ Correcto */
            puntaje++;
            btnSeleccionado.classList.add('correcta');
            feedback.innerHTML = '<span style="color:#059669;">✅ Correct! You know me perfectly! 💕</span>';
            feedback.style.opacity = '1';
            reproducirAcierto();

            // Pulso en el score
            const labelScore = $('label-score');
            labelScore.classList.remove('pulse-once');
            void labelScore.offsetWidth;
            labelScore.classList.add('pulse-once');
            labelScore.textContent = `⭐ ${puntaje} / ${TOTAL}`;
            setTimeout(() => labelScore.classList.remove('pulse-once'), 550);

        } else {
            /* ❌ Incorrecto */
            btnSeleccionado.classList.add('incorrecta');
            todos[q.correcta].classList.add('correcta');
            feedback.innerHTML = '<span style="color:#dc2626;">💔 Almost! The correct answer is highlighted. You can do it!</span>';
            feedback.style.opacity = '1';
            reproducirError();

            const tarjeta = $('tarjeta-pregunta');
            tarjeta.classList.remove('shake');
            void tarjeta.offsetWidth;
            tarjeta.classList.add('shake');
            setTimeout(() => tarjeta.classList.remove('shake'), 500);
        }

        // Avanzar tras pausa
        setTimeout(() => {
            indice++;
            if (indice < TOTAL) {
                renderizarPregunta();
            } else {
                finalizarQuiz();
            }
        }, 1850);
    }

    /* ──────────────────────────────────────────────────────────── */
    /*  Finalizar quiz                                             */
    /* ──────────────────────────────────────────────────────────── */
    function finalizarQuiz() {
        if (puntaje === TOTAL) {
            obtenerMensajeDeAmor();
        } else {
            $('puntaje-final').textContent = puntaje;
            mostrarPantalla('screen-derrota');
        }
    }

    /* ──────────────────────────────────────────────────────────── */
    /*  Fetch seguro a api.php                                     */
    /* ──────────────────────────────────────────────────────────── */
    async function obtenerMensajeDeAmor() {
        mostrarPantalla('screen-victoria');

        try {
            const resp = await fetch('api.php', {
                method:  'POST',
                headers: { 'Content-Type': 'application/json' },
                body:    JSON.stringify({ score: puntaje, total: TOTAL })
            });

            if (!resp.ok) throw new Error(`HTTP ${resp.status}`);

            const datos = await resp.json();

            if (datos.success && datos.message) {
                $('mensaje-amor').innerHTML = datos.message;
            } else {
                $('mensaje-amor').textContent = '💕 You did it! You are amazing, I love you. 💕';
            }

        } catch (err) {
            console.warn('[Quiz] Could not reach api.php:', err.message);
            $('mensaje-amor').textContent = '💕 You did it! You are amazing, I love you. 💕';
        }

        // Revelar carta con delay
        setTimeout(() => {
            $('carta-amor').classList.add('visible');
        }, 280);

        // Confetti 🎊
        setTimeout(lanzarConfetti, 520);
    }

    /* ──────────────────────────────────────────────────────────── */
    /*  Reiniciar estado                                           */
    /* ──────────────────────────────────────────────────────────── */
    function reiniciarQuiz() {
        indice    = 0;
        puntaje   = 0;
        respondido = false;
        $('barra-progreso').style.width = '0%';
        $('label-score').textContent    = `⭐ 0 / ${TOTAL}`;

        // Resetear carta
        const carta = $('carta-amor');
        carta.classList.remove('visible');
        $('mensaje-amor').innerHTML = '';
    }

    /* ──────────────────────────────────────────────────────────── */
    /*  Eventos                                                    */
    /* ──────────────────────────────────────────────────────────── */
    $('btn-iniciar').addEventListener('click', () => {
        reiniciarQuiz();
        mostrarPantalla('screen-quiz');
        renderizarPregunta();
    });

    $('btn-rejugar').addEventListener('click', () => {
        reiniciarQuiz();
        mostrarPantalla('screen-quiz');
        renderizarPregunta();
    });

    $('btn-reintentar').addEventListener('click', () => {
        reiniciarQuiz();
        mostrarPantalla('screen-quiz');
        renderizarPregunta();
    });

    </script>
</body>
</html>
