<?php
/**
 * admin/test-image-gen.php — Diagnóstico de geração de imagem hero via
 * OpenRouter/Gemini (mesma chamada do cron-gerar-blog.php), com a resposta
 * bruta da API impressa no ecrã.
 *
 * Protegido por token VIEWS_STATS_TOKEN (mesmo padrão de admin/test-smtp.php).
 * Existe só para diagnosticar por que a imagem cai sempre no fallback —
 * apaga depois de resolvido.
 *
 * URL: admin/test-image-gen.php?key=<TOKEN>
 */

declare(strict_types=1);

require_once __DIR__ . '/../config.php';

header('Content-Type: text/plain; charset=utf-8');

$token = (string) ($_GET['key'] ?? '');
if (VIEWS_STATS_TOKEN === '' || !hash_equals(VIEWS_STATS_TOKEN, $token)) {
    header('HTTP/1.0 401 Unauthorized');
    echo "401 Unauthorized\n\nUso: " . htmlspecialchars($_SERVER['SCRIPT_NAME'] ?? '/admin/test-image-gen.php') . "?key=<TOKEN>\n";
    exit;
}

echo "=== Configuração lida do .env ===\n";
echo "BLOG_OPENROUTER_API_KEY = " . (BLOG_OPENROUTER_API_KEY !== '' ? '(definida, ' . strlen(BLOG_OPENROUTER_API_KEY) . ' caracteres, começa em "' . substr(BLOG_OPENROUTER_API_KEY, 0, 10) . '...")' : '(vazio)') . "\n";
echo "BLOG_IMAGE_MODEL         = " . BLOG_IMAGE_MODEL . "\n";

if (BLOG_OPENROUTER_API_KEY === '') {
    echo "\nBLOG_OPENROUTER_API_KEY está vazia — a geração de imagem seria sempre ignorada (skip silencioso, usa o fallback).\n";
    exit;
}

$prompt = 'Generate only an image, no text response. Photorealistic editorial photograph for a blog about studying '
        . 'in Portugal for Brazilian students. NOT an illustration, NOT vector art, NOT flat design, NOT an '
        . 'infographic, no icons, no text overlays, no logos. A Brazilian student walking through a historic '
        . 'cobblestone square lined with traditional azulejo-tiled buildings. Soft golden-hour light, medium shot, '
        . 'shallow depth of field, quiet and focused mood. Context: teste de diagnóstico admin/test-image-gen.php';

echo "\n=== Prompt enviado ===\n{$prompt}\n";

$payload = ['model' => BLOG_IMAGE_MODEL, 'messages' => [['role' => 'user', 'content' => $prompt]], 'modalities' => ['image', 'text']];
$ch = curl_init('https://openrouter.ai/api/v1/chat/completions');
curl_setopt_array($ch, [
    CURLOPT_RETURNTRANSFER => true, CURLOPT_POST => true,
    CURLOPT_HTTPHEADER => ['Content-Type: application/json', 'Authorization: Bearer ' . BLOG_OPENROUTER_API_KEY, 'HTTP-Referer: ' . SITE_URL],
    CURLOPT_POSTFIELDS => json_encode($payload), CURLOPT_TIMEOUT => 90, CURLOPT_CONNECTTIMEOUT => 20,
]);
$response  = curl_exec($ch);
$httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
$curlError = curl_error($ch);
curl_close($ch);

echo "\n=== Resultado da chamada HTTP ===\n";
echo "HTTP status = {$httpCode}\n";
if ($curlError !== '') echo "cURL error  = {$curlError}\n";

if ($response === false || $httpCode !== 200) {
    echo "\nFALHOU na chamada HTTP. Corpo da resposta (se houver):\n" . substr((string) $response, 0, 3000) . "\n";
    exit;
}

$data = json_decode($response, true);
if ($data === null) {
    echo "\nFALHOU: resposta não é JSON válido. Corpo bruto:\n" . substr($response, 0, 3000) . "\n";
    exit;
}

$finishReason = $data['choices'][0]['finish_reason'] ?? '(ausente)';
$msg          = $data['choices'][0]['message'] ?? null;

echo "\n=== Resposta da API (estrutura) ===\n";
echo "finish_reason = {$finishReason}\n";
echo "message keys  = " . ($msg ? implode(', ', array_keys($msg)) : '(sem message)') . "\n";
if (isset($data['error'])) {
    echo "error         = " . json_encode($data['error'], JSON_UNESCAPED_UNICODE) . "\n";
}

$imgB64 = null;
if (isset($msg['images'][0]['image_url']['url']) && is_string($msg['images'][0]['image_url']['url'])) {
    $url = $msg['images'][0]['image_url']['url'];
    if (strpos($url, 'base64,') !== false) $imgB64 = substr($url, strpos($url, 'base64,') + 7);
    echo "images[0].image_url.url encontrado, " . strlen($url) . " caracteres (" . (strpos($url, 'base64,') !== false ? 'contém base64,' : 'SEM base64, — formato inesperado') . ")\n";
} else {
    echo "Não encontrei message.images[0].image_url.url na resposta.\n";
}

echo "\n=== Resposta bruta completa (para inspeção manual) ===\n";
echo substr($response, 0, 6000) . (strlen($response) > 6000 ? "\n...(cortado)" : '') . "\n";

echo "\n=== Resultado ===\n";
if (!$imgB64) {
    echo "FALHOU: sem imagem na resposta (ver detalhes acima — finish_reason e error são os campos mais úteis).\n";
    exit;
}
$bytes = base64_decode($imgB64, true);
if ($bytes === false || strlen($bytes) < 500) {
    echo "FALHOU: imagem em base64 inválida ou demasiado pequena (" . ($bytes === false ? 'decode falhou' : strlen($bytes) . ' bytes') . ").\n";
    exit;
}
echo "OK — imagem válida recebida (" . strlen($bytes) . " bytes). Não foi gravada em disco (isto é só um teste).\n";
