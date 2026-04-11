<?php
/**
 * api.php — Endpoint del mensaje final.
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

header('Content-Type: application/json; charset=utf-8');
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

function getClientIp(): string
{
    $candidates = [
        $_SERVER['HTTP_CF_CONNECTING_IP'] ?? '',
        $_SERVER['HTTP_X_REAL_IP'] ?? '',
        $_SERVER['REMOTE_ADDR'] ?? '',
    ];

    $xff = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? '';
    if (is_string($xff) && $xff !== '') {
        $parts = array_map('trim', explode(',', $xff));
        if (!empty($parts[0])) {
            array_unshift($candidates, $parts[0]);
        }
    }

    foreach ($candidates as $ip) {
        if (is_string($ip) && filter_var($ip, FILTER_VALIDATE_IP)) {
            return $ip;
        }
    }

    return 'unknown';
}

function checkIpRateLimit(string $ip, int $windowSeconds, int $maxRequests): bool
{
    $file = sys_get_temp_dir() . '/wipawee_quiz_rate_limit.json';
    $now = time();
    $key = hash('sha256', $ip);

    $fp = @fopen($file, 'c+');
    if ($fp === false) {
        return true;
    }

    if (!flock($fp, LOCK_EX)) {
        fclose($fp);
        return true;
    }

    $raw = stream_get_contents($fp);
    $data = json_decode($raw ?: '{}', true);
    if (!is_array($data)) {
        $data = [];
    }

    foreach ($data as $k => $bucket) {
        if (!isset($bucket['start']) || ($now - (int) $bucket['start']) >= $windowSeconds) {
            unset($data[$k]);
        }
    }

    $bucket = $data[$key] ?? ['start' => $now, 'count' => 0];
    if (($now - (int) $bucket['start']) >= $windowSeconds) {
        $bucket = ['start' => $now, 'count' => 0];
    }

    $bucket['count'] = (int) $bucket['count'] + 1;
    $data[$key] = $bucket;

    ftruncate($fp, 0);
    rewind($fp);
    fwrite($fp, json_encode($data, JSON_UNESCAPED_SLASHES));
    fflush($fp);
    flock($fp, LOCK_UN);
    fclose($fp);

    return $bucket['count'] <= $maxRequests;
}

$host = $_SERVER['HTTP_HOST'] ?? '';
$origin = $_SERVER['HTTP_ORIGIN'] ?? '';
if ($origin !== '') {
    $originHost = parse_url($origin, PHP_URL_HOST) ?: '';
    $requestHost = parse_url('http://' . $host, PHP_URL_HOST) ?: '';
    if (!hash_equals((string) $requestHost, (string) $originHost)) {
        http_response_code(403);
        echo json_encode(['error' => 'Forbidden origin']);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Method not allowed']);
    exit;
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$csrfSession = $_SESSION['csrf_token'] ?? '';
if (!is_string($csrfHeader) || !is_string($csrfSession) || $csrfSession === '' || !hash_equals($csrfSession, $csrfHeader)) {
    http_response_code(403);
    echo json_encode(['error' => 'Invalid CSRF token']);
    exit;
}

$now = time();
$windowSeconds = 60;
$maxRequests = 40;
if (!isset($_SESSION['api_rate_window_start'], $_SESSION['api_rate_count']) || ($now - (int) $_SESSION['api_rate_window_start']) >= $windowSeconds) {
    $_SESSION['api_rate_window_start'] = $now;
    $_SESSION['api_rate_count'] = 1;
} else {
    $_SESSION['api_rate_count'] = (int) $_SESSION['api_rate_count'] + 1;
}

if ((int) $_SESSION['api_rate_count'] > $maxRequests) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests']);
    exit;
}

$ipWindowSeconds = 60;
$ipMaxRequests = 80;
$clientIp = getClientIp();
if (!checkIpRateLimit($clientIp, $ipWindowSeconds, $ipMaxRequests)) {
    http_response_code(429);
    echo json_encode(['error' => 'Too many requests from this IP']);
    exit;
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($input['score'], $input['total'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
    exit;
}

$score = (int) $input['score'];
$totalEnviado = (int) $input['total'];

$totalEsperado = 8;
$porcentajeMinimo = 0.8;

if ($score < 0 || $score > $totalEsperado || $totalEnviado < 1 || $totalEnviado > 50) {
    http_response_code(400);
    echo json_encode(['error' => 'Out-of-range values']);
    exit;
}

if ($totalEnviado !== $totalEsperado || ($score / $totalEsperado) < $porcentajeMinimo) {
    echo json_encode(['success' => false, 'message' => null]);
    exit;
}

$mensajeAmor = <<<'TEXT'
Wipawee, my love,

I’ve been thinking a lot today about how it’s already been 1 year and 9 months since we started this. 21 months might just be a number to some, but to me, it represents the best chapter of my life so far. I mean it from the bottom of my heart: you are truly the best thing that has ever happened to me.

I can’t even describe how much I look forward to the day I don’t have to say goodbye through a screen. Every day, that feeling of wanting to just be there with you grows more and more. I want to be by your side for real, for good, and just love you forever and marry you and make you mine all forever.

I miss your smile so much—it’s the one thing that can always turn my day around. And your eyes... I could honestly look at them for hours and fall in love with you more and more. To me, they are the most beautiful eyes in the world.

My biggest dream right now is just the simple stuff: waking up next to you every single morning, seeing you there, and finally giving you those million hugs I’ve been saving up. I just want to hold you and never let go.

Thank you for being mine and for these amazing 21 months. I love you more than I can put into words.

Happy anniversary, my princess.

Always yours, Natha
TEXT;

$mensajeSeguro = nl2br(htmlspecialchars($mensajeAmor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

echo json_encode([
    'success' => true,
    'message' => $mensajeSeguro,
]);
