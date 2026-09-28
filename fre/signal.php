<?php
/**
 * signal.php — Servidor de mensajes 
 * Short-polling
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');
header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
header('Pragma: no-cache');

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') { http_response_code(204); exit; }

// ── Configuración ──
define('ROOMS_DIR', __DIR__ . '/rooms/');
define('MSG_LIMIT', 60);      // solo guardamos los últimos 60 mensajes por sala
define('ROOM_TTL',  1800);    // limpiar sala tras 30 min de inactividad (no 1h)
define('CLEAN_PROB', 5);      // limpiar salas viejas en 1 de cada 5 requests (aleatorio)

if (!is_dir(ROOMS_DIR)) {
    mkdir(ROOMS_DIR, 0755, true);
}

// Limpiar salas viejas de forma aleatoria para no hacerlo en cada request
if (rand(1, CLEAN_PROB) === 1) {
    foreach (glob(ROOMS_DIR . '*.json') as $f) {
        if (time() - filemtime($f) > ROOM_TTL) @unlink($f);
    }
}

// ── Helpers ──
function roomFile($room) {
    $safe = preg_replace('/[^A-Za-z0-9]/', '', substr($room, 0, 12));
    if (strlen($safe) < 4) { echo json_encode(['error' => 'invalid room']); exit; }
    return ROOMS_DIR . $safe . '.json';
}

function readRoom($file) {
    if (!file_exists($file)) return ['msgs' => [], 'ts' => 0];
    $raw = file_get_contents($file);
    $data = $raw ? json_decode($raw, true) : null;
    return $data ?: ['msgs' => [], 'ts' => 0];
}

function writeRoom($file, $data) {
    $data['ts'] = time();
    // Atomic write: escribir a temp y renombrar para evitar lecturas a mitad de escritura
    $tmp = $file . '.tmp.' . getmypid();
    file_put_contents($tmp, json_encode($data), LOCK_EX);
    rename($tmp, $file);
}

// ── Routing ──
$action = $_GET['a'] ?? '';
$room   = $_GET['r'] ?? '';
if (!$room) { echo json_encode(['error' => 'no room']); exit; }
$file = roomFile($room);

// ════════════════════════════════════════
//  POST — enviar mensaje
//  Tiempo de respuesta: < 5ms
// ════════════════════════════════════════
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $raw  = file_get_contents('php://input');
    $body = $raw ? json_decode($raw, true) : null;
    if (!$body || !isset($body['msg'])) {
        echo json_encode(['error' => 'bad body']); exit;
    }

    // Lock + read + append + write
    $lock = fopen($file . '.lock', 'c');
    flock($lock, LOCK_EX);

    $data = readRoom($file);
    $data['msgs'][] = [
        'id'   => uniqid('', true),
        'ts'   => microtime(true),
        'from' => (int)($body['from'] ?? 0),
        'msg'  => $body['msg'],
    ];

    // Mantener solo los últimos MSG_LIMIT mensajes
    if (count($data['msgs']) > MSG_LIMIT) {
        $data['msgs'] = array_slice($data['msgs'], -MSG_LIMIT);
    }

    writeRoom($file, $data);
    flock($lock, LOCK_UN);
    fclose($lock);

    echo json_encode(['ok' => true, 'ts' => microtime(true)]);
    exit;
}

// ════════════════════════════════════════
//  GET ?a=poll — short-poll (responde inmediatamente)
//  Tiempo de respuesta: < 10ms
//  El cliente llama cada 500ms. Sin conexiones abiertas.
// ════════════════════════════════════════
if ($action === 'poll') {
    $since = (float)($_GET['since'] ?? 0);

    $data = readRoom($file);
    $new  = array_values(array_filter(
        $data['msgs'],
        fn($m) => $m['ts'] > $since
    ));

    echo json_encode(['msgs' => $new, 'ts' => microtime(true)]);
    exit;
}

// ════════════════════════════════════════
//  GET ?a=create
// ════════════════════════════════════════
if ($action === 'create') {
    $existed = file_exists($file);
    if (!$existed) writeRoom($file, ['msgs' => []]);
    echo json_encode(['ok' => true, 'existed' => $existed]);
    exit;
}

// ════════════════════════════════════════
//  GET ?a=exists
// ════════════════════════════════════════
if ($action === 'exists') {
    // Verificar que el archivo existe Y tiene actividad reciente (últimos 30 min)
    $exists = file_exists($file) && (time() - filemtime($file) < ROOM_TTL);
    echo json_encode(['exists' => $exists]);
    exit;
}

// ════════════════════════════════════════
//  GET ?a=delete
// ════════════════════════════════════════
if ($action === 'delete') {
    @unlink($file);
    @unlink($file . '.lock');
    echo json_encode(['ok' => true]);
    exit;
}

http_response_code(400);
echo json_encode(['error' => 'unknown action']);