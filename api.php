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
My love, My sweet Wipawee,

Today marks one year and ten months since we started this journey together, and I've been thinking about how incredible it is that, despite the thousands of miles between us, I feel you closer to me than anyone else.

It's not just about the time; it's about these 22 pages of a story that completely changed my life. You truly are my everything. I can't imagine waking up and you not being there, or letting a single day pass without hearing your voice or reading a message from you. Talking to you is what gives my daily routine meaning; it's my sanctuary where everything feels right, no matter what's happening in the world outside.

You are the sweetest person I've ever known. You have this tenderness that shines right through the screen and manages to make me smile even on my hardest days. I know that me being here in Chile and you being in Thailand isn't the easiest thing in the world, but you make every single effort worth it. You make me a better person, you make me immensely happy, and honestly, I don't even know what my world would look like if you weren't there to light it up.

Thank you for choosing me every day, for being my partner, and for making these last twenty-two months feel like just the beginning of forever. I love you with everything I am, and I'm counting down the days until the distance is nothing more than a memory.

Happy 22 months, my life

Natha.
TEXT;

$mensajeSeguro = nl2br(htmlspecialchars($mensajeAmor, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8'));

echo json_encode([
    'success' => true,
    'message' => $mensajeSeguro,
]);
