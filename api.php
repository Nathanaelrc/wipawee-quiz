<?php
/**
 * api.php — Endpoint del mensaje final.
 */

declare(strict_types=1);

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

$appConfigPath = __DIR__ . '/config/app.php';
$appConfig = file_exists($appConfigPath) ? (require $appConfigPath) : [];
if (!is_array($appConfig)) {
    $appConfig = [];
}

header('Content-Type: application/json; charset=utf-8');
header('X-Content-Type-Options: nosniff');
header('X-Frame-Options: DENY');
header('Referrer-Policy: strict-origin-when-cross-origin');
header('Permissions-Policy: geolocation=(), microphone=(), camera=()');
header('Cross-Origin-Opener-Policy: same-origin');
header('Cross-Origin-Resource-Policy: same-origin');
header('X-Permitted-Cross-Domain-Policies: none');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');

if ($secureCookie) {
    header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
}

function respondJson(int $statusCode, array $payload): void
{
    http_response_code($statusCode);
    echo json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    exit;
}

function logSecurityEvent(string $event, array $context = []): void
{
    $contextJson = json_encode($context, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
    error_log('[wipawee_quiz_api] ' . $event . ' ' . ($contextJson !== false ? $contextJson : '{}'));
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

function getRequestOrigin(): string
{
    $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
    return is_string($origin) ? trim($origin) : '';
}

function getNormalizedRequestOrigin(): string
{
    $scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'] ?? '';
    $url = $scheme . '://' . $host;

    $parts = parse_url($url);
    if (!is_array($parts) || empty($parts['host'])) {
        return '';
    }

    $hostPart = strtolower((string) $parts['host']);
    $schemePart = strtolower((string) ($parts['scheme'] ?? 'http'));
    $portPart = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

    return $schemePart . '://' . $hostPart . $portPart;
}

function normalizeOrigin(string $origin): string
{
    $parts = parse_url($origin);
    if (!is_array($parts) || empty($parts['host'])) {
        return '';
    }

    $schemePart = strtolower((string) ($parts['scheme'] ?? 'http'));
    $hostPart = strtolower((string) $parts['host']);
    $portPart = isset($parts['port']) ? ':' . (int) $parts['port'] : '';

    return $schemePart . '://' . $hostPart . $portPart;
}

$origin = getRequestOrigin();
if ($origin !== '') {
    $normalizedOrigin = normalizeOrigin($origin);
    $expectedOrigin = getNormalizedRequestOrigin();
    if ($normalizedOrigin === '' || $expectedOrigin === '' || !hash_equals($expectedOrigin, $normalizedOrigin)) {
        logSecurityEvent('forbidden_origin', ['origin' => $origin, 'expected' => $expectedOrigin]);
        respondJson(403, ['error' => 'Forbidden origin']);
    }
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    respondJson(405, ['error' => 'Method not allowed']);
}

$csrfHeader = $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
$csrfSession = $_SESSION['csrf_token'] ?? '';
if (!is_string($csrfHeader) || !is_string($csrfSession) || $csrfSession === '' || !hash_equals($csrfSession, $csrfHeader)) {
    logSecurityEvent('invalid_csrf', ['ip' => getClientIp()]);
    respondJson(403, ['error' => 'Invalid CSRF token']);
}

$contentLength = (int) ($_SERVER['CONTENT_LENGTH'] ?? 0);
if ($contentLength > 10_240) {
    logSecurityEvent('payload_too_large', ['length' => $contentLength, 'ip' => getClientIp()]);
    respondJson(413, ['error' => 'Payload too large']);
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
    respondJson(429, ['error' => 'Too many requests']);
}

$ipWindowSeconds = 60;
$ipMaxRequests = 80;
$clientIp = getClientIp();
if (!checkIpRateLimit($clientIp, $ipWindowSeconds, $ipMaxRequests)) {
    respondJson(429, ['error' => 'Too many requests from this IP']);
}

$raw = file_get_contents('php://input');
$input = json_decode($raw, true);

if (json_last_error() !== JSON_ERROR_NONE || !isset($input['score'], $input['total'])) {
    respondJson(400, ['error' => 'Invalid request']);
}

$score = (int) $input['score'];
$totalEnviado = (int) $input['total'];

$totalEsperado = (int) ($appConfig['expected_total_questions'] ?? 8);
$porcentajeMinimo = (float) ($appConfig['required_ratio'] ?? 0.8);

if ($score < 0 || $score > $totalEsperado || $totalEnviado < 1 || $totalEnviado > 50) {
    respondJson(400, ['error' => 'Out-of-range values']);
}

if ($totalEnviado !== $totalEsperado || ($score / $totalEsperado) < $porcentajeMinimo) {
    respondJson(200, ['success' => false, 'message' => null]);
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

respondJson(200, [
    'success' => true,
    'message' => $mensajeSeguro,
]);
