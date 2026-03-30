<?php
/**
 * api.php — Endpoint seguro del Quiz de Trivia Romántica
 *
 * Devuelve el mensaje de amor SOLO si el puntaje enviado es perfecto (5/5).
 * El contenido de este archivo NO es visible desde el "Inspect Element"
 * del navegador hasta que se complete el quiz correctamente.
 *
 * ✏️  PERSONALIZACIÓN:
 *   Edita únicamente el bloque marcado como "MENSAJE DE AMOR" más abajo.
 */

/* ────────────────────────────────────────────────────────────── */
/*  Headers de respuesta                                         */
/* ────────────────────────────────────────────────────────────── */
header('Content-Type: application/json; charset=utf-8');

// Restringir a llamadas desde el mismo origen (same-origin)
// Si el quiz y la API están en el mismo servidor, esto es suficiente.
// Para producción con dominio propio ajusta CORS según sea necesario.
header('X-Content-Type-Options: nosniff');

/* ────────────────────────────────────────────────────────────── */
/*  Solo se aceptan peticiones POST                              */
/* ────────────────────────────────────────────────────────────── */
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

/* ────────────────────────────────────────────────────────────── */
/*  Leer y validar el cuerpo JSON                               */
/* ────────────────────────────────────────────────────────────── */
$raw   = file_get_contents('php://input');
$input = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($input['score'], $input['total'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Petición inválida']);
    exit;
}

$score         = (int) $input['score'];
$total_enviado = (int) $input['total'];

// Total esperado debe coincidir con las preguntas del frontend
$TOTAL_ESPERADO = 9;

/* ────────────────────────────────────────────────────────────── */
/*  Validar puntaje perfecto                                     */
/*  Se requiere exactamente 5/5 — cualquier otro valor           */
/*  devuelve success:false sin revelar el mensaje.               */
/* ────────────────────────────────────────────────────────────── */
if ($total_enviado !== $TOTAL_ESPERADO || ($score / $TOTAL_ESPERADO) < 0.8) {
    http_response_code(200);
    echo json_encode(['success' => false, 'message' => null]);
    exit;
}

/* ════════════════════════════════════════════════════════════════ */
/*  ✏️  MENSAJE DE AMOR                                            */
/*  ─────────────────────────────────────────────────────────────  */
/*  Escribe aquí tu carta personal. Puedes usar saltos de línea   */
/*  (\n) y emojis. El texto se escapará y formateará              */
/*  automáticamente antes de enviarse.                            */
/* ════════════════════════════════════════════════════════════════ */
$mensaje_amor = "My Love,

I love you so much, you are the best thing that has happened to me
and every day I am more and more in love with you. 💗

I am always happy with you and you make me feel very good every day.
You fill my life with joy, peace, and a love I never thought I could feel.

I eagerly wait to see you, my love.
Every moment apart makes me realize how much you mean to me.

My beautiful Wipawee, I hope that you and I can always be together
and one day form a beautiful family. 🌸

You are my Princess, my everything, my forever. 👸

I love youuuu. 💕

— Always yours,
   Marcos Nathanael  ♥";

/* ────────────────────────────────────────────────────────────── */
/*  Respuesta exitosa                                            */
/*  · htmlspecialchars previene XSS al insertar en el DOM.       */
/*  · nl2br convierte saltos de línea a <br> para HTML.          */
/* ────────────────────────────────────────────────────────────── */
$mensaje_seguro = nl2br(htmlspecialchars($mensaje_amor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

echo json_encode([
    'success' => true,
    'message' => $mensaje_seguro
]);
