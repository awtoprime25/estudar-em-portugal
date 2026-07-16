<?php
// Token stateless HMAC — sem $_SESSION, sem cookies. Mesmo mecanismo do
// site irmão EstudarNoEstrangeiro/chat.php.
//
// Token = <nonce-hex16>.<unix-ts>.<hex64 hmac("nonce.ts", CSRF_SECRET)>
// O servidor volta a calcular o HMAC e compara com hash_equals; rejeita
// se o timestamp tiver mais de 30 min (proteção contra replay).
require_once __DIR__ . '/config.php';

header('Content-Type: application/json; charset=utf-8');
header('Cache-Control: no-store, no-cache');
header('X-Content-Type-Options: nosniff');

if (!defined('CSRF_SECRET') || CSRF_SECRET === '') {
    http_response_code(500);
    echo json_encode(['error' => 'CSRF_SECRET not configured']);
    exit;
}

$ttl       = 1800; // 30 min
$ts        = (string) time();
$nonce     = bin2hex(random_bytes(16));
$payload   = $nonce . '.' . $ts;
$signature = hash_hmac('sha256', $payload, CSRF_SECRET);
$token     = $payload . '.' . $signature;

echo json_encode(['token' => $token, 'ttl' => $ttl]);
