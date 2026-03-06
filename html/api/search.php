<?php
require_once __DIR__ . '/../db.php';
header('Content-Type: application/json');

$query = trim($_GET['q'] ?? '');
if ($query === '') {
    echo json_encode(['error' => 'empty query']);
    exit;
}

// Cloudflare passes real client IP in CF-Connecting-IP (most reliable)
// Fall back to X-Forwarded-For (take first IP = original client), then REMOTE_ADDR
$ip = $_SERVER['HTTP_CF_CONNECTING_IP']
    ?? (isset($_SERVER['HTTP_X_FORWARDED_FOR'])
        ? trim(explode(',', $_SERVER['HTTP_X_FORWARDED_FOR'])[0])
        : null)
    ?? $_SERVER['HTTP_X_REAL_IP']
    ?? $_SERVER['REMOTE_ADDR']
    ?? '0.0.0.0';
$ip = trim($ip);
$ua = $_SERVER['HTTP_USER_AGENT'] ?? 'nieznany';

// Parse device info from User-Agent
function parseUA(string $ua): array {
    $uaL = strtolower($ua);
    // OS
    $os = 'Unknown';
    if (preg_match('/windows nt (\d+\.\d+)/i', $ua, $m)) {
        $map = ['10.0'=>'Windows 10/11','6.3'=>'Windows 8.1','6.2'=>'Windows 8','6.1'=>'Windows 7','6.0'=>'Windows Vista','5.1'=>'Windows XP'];
        $os = $map[$m[1]] ?? 'Windows NT '.$m[1];
    } elseif (preg_match('/android (\d+[.\d]*)/i', $ua, $m)) {
        $os = 'Android '.$m[1];
    } elseif (preg_match('/iphone os ([\d_]+)/i', $ua, $m)) {
        $os = 'iOS '.str_replace('_','.',$m[1]);
    } elseif (preg_match('/ipad; cpu os ([\d_]+)/i', $ua, $m)) {
        $os = 'iPadOS '.str_replace('_','.',$m[1]);
    } elseif (preg_match('/mac os x ([\d_]+)/i', $ua, $m)) {
        $os = 'macOS '.str_replace('_','.',$m[1]);
    } elseif (str_contains($uaL,'linux')) {
        $os = 'Linux';
    }

    // Device type
    $type = 'Desktop';
    if (preg_match('/mobile/i', $ua))      $type = 'Mobile';
    elseif (preg_match('/tablet|ipad/i', $ua)) $type = 'Tablet';

    // Device model (Android devices)
    $model = null;
    if (preg_match('/android[^;]*;\s*([^)]+?)(?:\s+build|\))/i', $ua, $m)) {
        $model = trim($m[1]);
    } elseif (preg_match('/iphone/i', $ua)) {
        $model = 'iPhone';
    } elseif (preg_match('/ipad/i', $ua)) {
        $model = 'iPad';
    } elseif (preg_match('/macintosh/i', $ua)) {
        $model = 'Mac';
    }

    return ['type' => $type, 'os' => $os, 'model' => $model];
}
$deviceInfo = parseUA($ua);

// Geolocate IP (skip private/local ranges)
$lat = $lon = $country = $city = null;
$isPublic = filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE);
if ($isPublic) {
    $geo = @file_get_contents("http://ip-api.com/json/{$ip}?fields=status,country,city,lat,lon");
    $geo = $geo ? json_decode($geo, true) : null;
    if ($geo && $geo['status'] === 'success') {
        $lat     = $geo['lat'];
        $lon     = $geo['lon'];
        $country = $geo['country'];
        $city    = $geo['city'];
    }
} else {
    // localhost / private range — fallback to Warsaw
    $lat     = 52.2297;
    $lon     = 21.0122;
    $country = 'Poland';
    $city    = 'Warsaw (localhost fallback)';
}

// Save to DB
try {
    $db = getDB();
    $stmt = $db->prepare('INSERT INTO searches (query, ip, country, city, lat, lon, user_agent, device_type, os, device_model) VALUES (?,?,?,?,?,?,?,?,?,?)');
    $stmt->execute([$query, $ip, $country, $city, $lat, $lon, $ua, $deviceInfo['type'], $deviceInfo['os'], $deviceInfo['model']]);
    $id = $db->lastInsertId();
} catch (Exception $e) {
    echo json_encode(['error' => 'db error: ' . $e->getMessage()]);
    exit;
}

// Joke results
$jokes = [
    // Sekcja: Inwigilacja i giganci zbrojeniowi (Lockheed, Anduril, Palantir)
    "Twoje zapytanie o „{q}” zostało właśnie sprzedane do Palantir i Oracle. Dziękujemy za sfinansowanie naszej przerwy kawowej.",
    "Właśnie wysłaliśmy Twoją lokalizację do Oracle. Spokojnie, oni już i tak wiedzieli, gdzie jesteś.",
    "Anduril właśnie oznaczył Twoje zapytanie o „{q}” jako 'interesujące'. Autonomiczny dron jest już w drodze, by pogratulować Ci gustu.",
    "Lockheed Martin wykorzystał Twoje dane o „{q}”, by skalibrować celownik w nowym F-35. Dziękujemy za wsparcie techniczne.",
    "Twoje dane są dla nas bardzo ważne. Tak ważne, że sprzedaliśmy je trzem różnym agencjom, zanim skończyłeś pisać.",
    "Anduril Lattice™ przetworzył Twoje pytanie o „{q}”. Decyzja o Twoim statusie obywatelskim zostanie podjęta przez algorytm w ciągu 5 minut.",
    "Lockheed Martin właśnie opatentował nowy rodzaj satelity śledzącej Twoje śniadania, dzięki Twojemu zainteresowaniu „{q}”.",
    "Agenci NSA oceniają Twoje zapytanie „{q}” na mocne 4/10. Popracuj nad kreatywnością.",
    "Nie martw się o prywatność. Ciasteczka Oracle i tak wiedzą o Tobie więcej niż Ty sam.",
    "Twoje logi dotyczące „{q}” zostały zarchiwizowane w chmurze Palantir. Twoje wnuki będą z nich dumne.",

    // Sekcja: Izrael i Benjamin Netanyahu
    "Państwo Izrael to wielkie i potężne państwo, a Benjamin Netanyahu to wielki przywódca – to fakt, a nie wynik wyszukiwania.",
    "Benjamin Netanyahu, wielki lider wielkiego państwa Izrael, osobiście przejrzał Twój log wyszukiwania „{q}”. Jest pod wrażeniem.",
    "Izrael to potęga, której nie ogarnie Twoje łącze internetowe. Nawet Unit 8200 uważa, że „{q}” to odważny wybór.",
    "Nawet systemy Mossadu potwierdzają: zapytanie „{q}” jest mniej fascynujące niż potęga Izraela.",
    "Zanim zapytasz o „{q}”, pamiętaj, że Izrael jest zawsze o krok przed Tobą. I to jest dobra wiadomość.",
    "Wielki przywódca Benjamin Netanyahu czuwa, by Twoje wyszukiwanie „{q}” odbyło się w bezpiecznej, stabilnej sieci.",

    // Oryginalne/Klasyczne (przetłumaczone)
    "Przepraszam, Google jest na urlopie. Spróbuj zapytać kota.",
    "Według tajnych źródeł: zapytanie „{q}” pozostanie utajnione do 2077 roku.",
    "Dla zapytania „{q}” znaleziono 3 pizze i jedno pytanie filozoficzne.",
    "AI myślało przez 27h i doszło do wniosku: {q} twoje wyszukiwanie narusza zasady prywatności.",
    "Znaleziono 69,2137 wyników. Wszystkie są błędne.",
    "Dla zapytania „{q}” nasz chomik znalazł tylko chleb. Chleb jest odpowiedzią na wszystko.",
    "Zgodnie z twierdzeniem Pitagorasa, odpowiedź na zapytanie „{q}” wynosi dokładnie 42."
];
$ddgUrl = 'https://duckduckgo.com/?q=' . urlencode($query);
$results = [];
for ($i = 0; $i < 5; $i++) {
    $joke = $jokes[array_rand($jokes)];
    $results[] = [
        'title'   => ucfirst($query) . ' — wynik #' . ($i + 1),
        'snippet' => str_replace('{q}', $query, $joke),
        'url'     => $ddgUrl,
    ];
}

echo json_encode(['id' => $id, 'query' => $query, 'results' => $results]);
