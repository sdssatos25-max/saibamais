# Conteúdo atualizado do index.php com preservação de UTMs
index_php = """
<?php
// index.php

// ===================
// 🔐 CONFIGURAÇÕES
// ===================
$configFile = __DIR__ . '/redirect_config.json';
$config = json_decode(file_get_contents($configFile), true);

// ===================
// 🌎 DETECÇÃO DE IP E LOCALIZAÇÃO
// ===================
function get_client_ip() {
    if (!empty($_SERVER['HTTP_CLIENT_IP'])) return $_SERVER['HTTP_CLIENT_IP'];
    if (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) return explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0];
    return $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
}

$ip = get_client_ip();
$geo = @json_decode(@file_get_contents("http://ip-api.com/json/{$ip}?fields=countryCode,query,as"), true);
$country = $geo['countryCode'] ?? 'UNKNOWN';
$asn = $geo['as'] ?? 'UNKNOWN';

// ===================
// 📱 DETECÇÃO DE DISPOSITIVO
// ===================
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$isMobile = preg_match('/Mobile|Android|iPhone|iPad|iPod/i', $userAgent);
$deviceType = $isMobile ? 'MOBILE' : 'DESKTOP';

// ===================
// 📌 LÓGICA DE REDIRECIONAMENTO
// ===================
$offerUrl = $config['offer_url'];
$fakeUrl = $config['fake_url'];

// 🔄 PRESERVA UTMs
$queryString = $_SERVER['QUERY_STRING'];
$redirectTo = ($isMobile && $country === 'BR') ? $offerUrl : $fakeUrl;
$redirectToWithUTM = $queryString ? "{$redirectTo}?" . $queryString : $redirectTo;

// ===================
// 🪵 REGISTRO DE LOG
// ===================
$log = [
    'ip' => $ip,
    'country' => $country,
    'asn' => $asn,
    'user_agent' => $userAgent,
    'device' => $deviceType,
    'timestamp' => date('Y-m-d H:i:s'),
    'redirect_to' => $redirectToWithUTM
];
$logFile = __DIR__ . '/logs.json';
$logs = file_exists($logFile) ? json_decode(file_get_contents($logFile), true) : [];
$logs[] = $log;
file_put_contents($logFile, json_encode($logs, JSON_PRETTY_PRINT));

// ===================
// 🚀 REDIRECIONAMENTO
// ===================
header("Location: {$redirectToWithUTM}");
exit;
?>
"""

# Salvar o index.php corrigido
index_php_path = "/mnt/data/index.php"
with open(index_php_path, "w", encoding="utf-8") as f:
    f.write(index_php.strip())

index_php_path
