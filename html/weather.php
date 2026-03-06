<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" type="image/svg+xml" href="favicon.svg">
<title>datapeice SYSTEMS&amp;CLOUDS — Pogoda Enterprise</title>
    <style>
        body { font-family: 'Segoe UI', Arial, sans-serif; background: #080a0f; color: #cdd1da; margin: 0; }
        header { background: #000; border-bottom: 1px solid #222; padding: 0 48px;
                 display: flex; align-items: center; justify-content: space-between; height: 52px; }
        .brand { font-family: 'IBM Plex Mono', monospace; font-size: 13px; font-weight: 600; color: #8a9ba8; letter-spacing: 0.05em; }
        .brand span { color: #fff; }
        nav a { font-family: 'IBM Plex Mono', monospace; color: #444; text-decoration: none; margin-left: 28px; font-size: 11px; letter-spacing: 0.08em; text-transform: uppercase; }
        nav a:hover { color: #fff; }
        nav a.active { color: #fff; }
        main { max-width: 900px; margin: 40px auto; padding: 0 20px; }
        .search-box { display: flex; gap: 10px; margin-bottom: 30px; }
        .search-box input {
            flex: 1; padding: 12px 16px; border-radius: 8px;
            border: none; font-size: 16px; background: #16213e; color: #fff;
        }
        .search-box button {
            padding: 12px 24px; border-radius: 8px; border: none;
            background: #0f3460; color: #fff; font-size: 16px; cursor: pointer;
        }
        .search-box button:hover { background: #e94560; }
        .current-weather {
            background: linear-gradient(135deg, #0f3460, #16213e);
            border-radius: 16px; padding: 30px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 30px;
        }
        .current-weather .icon { font-size: 80px; }
        .current-weather h2 { margin: 0 0 8px; font-size: 28px; }
        .current-weather .temp { font-size: 64px; font-weight: bold; margin: 0; }
        .current-weather .meta { color: #aaa; margin-top: 8px; }
        .forecast { display: grid; grid-template-columns: repeat(auto-fill, minmax(110px, 1fr)); gap: 12px; }
        .forecast-card {
            background: #16213e; border-radius: 12px; padding: 16px;
            text-align: center;
        }
        .forecast-card .day { font-weight: bold; margin-bottom: 8px; color: #aaa; font-size: 13px; }
        .forecast-card .icon { font-size: 32px; }
        .forecast-card .max { font-size: 20px; font-weight: bold; }
        .forecast-card .min { color: #888; font-size: 14px; }
        .error { color: #e94560; background: #16213e; padding: 16px; border-radius: 8px; }
        h3 { margin: 20px 0 12px; color: #aaa; font-size: 14px; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>

<header>
  <div class="brand">datapeice <span>SYSTEMS&amp;CLOUDS</span></div>
  <nav>
    <a href="index.php">Wyszukiwarka</a>
    <a href="weather.php" class="active">Pogoda</a>
    <a href="globe.php">🌍 Globus Telemetrii</a>
  </nav>
</header>

<main>
    <form method="GET" action="">
        <div class="search-box">
            <input type="text" name="city" placeholder="Wpisz nazwę miasta..." value="<?= htmlspecialchars($_GET['city'] ?? '') ?>">
            <button type="submit">Szukaj pogody</button>
        </div>
    </form>

    <?php
    $days = ['Nd','Pn','Wt','Śr','Czw','Pt','Sb'];

    function weatherIcon(int $code): string {
        return match(true) {
            $code === 0             => '☀️',
            in_array($code,[1,2])   => '🌤️',
            $code === 3             => '☁️',
            in_array($code,[45,48]) => '🌫️',
            in_array($code,[51,53,55,61,63,65,80,81,82]) => '🌧️',
            in_array($code,[71,73,75,77,85,86]) => '🌨️',
            in_array($code,[95,96,99]) => '⛈️',
            default                 => '🌡️',
        };
    }

    function weatherDesc(int $code): string {
        return match(true) {
            $code === 0             => 'Bezchmurnie',
            in_array($code,[1,2])   => 'Mało chmurnie',
            $code === 3             => 'Pochmurno',
            in_array($code,[45,48]) => 'Mgła',
            in_array($code,[51,53,55]) => 'Mrokówka',
            in_array($code,[61,63,65,80,81,82]) => 'Deszcz',
            in_array($code,[71,73,75,77,85,86]) => 'Śnieg',
            in_array($code,[95,96,99]) => 'Burza',
            default                 => 'Zmienny',
        };
    }

    if (!empty($_GET['city'])) {
        $city = trim($_GET['city']);

        // Геокодинг
        $geoUrl = 'https://geocoding-api.open-meteo.com/v1/search?name=' . urlencode($city) . '&count=1&language=ru&format=json';
        $geoResp = @file_get_contents($geoUrl);
        $geoData = $geoResp ? json_decode($geoResp, true) : null;

        if (empty($geoData['results'])) {
            echo '<div class="error">Nie znaleziono miasta. Sprawdź nazwę i spróbuj ponownie.</div>';
        } else {
            $loc  = $geoData['results'][0];
            $lat  = $loc['latitude'];
            $lon  = $loc['longitude'];
            $name = $loc['name'];
            $country = $loc['country'] ?? '';

            // Прогноз
            $url = "https://api.open-meteo.com/v1/forecast?latitude={$lat}&longitude={$lon}"
                 . "&current=temperature_2m,relative_humidity_2m,wind_speed_10m,weather_code"
                 . "&daily=weather_code,temperature_2m_max,temperature_2m_min"
                 . "&timezone=auto&forecast_days=7";

            $resp = @file_get_contents($url);
            $data = $resp ? json_decode($resp, true) : null;

            $notFound = '<div class="error">Nie można pobrać danych pogodowych. Spróbuj później.</div>';
            if (!$data) { echo $notFound;
            } else {
                $cur  = $data['current'];
                $code = (int)$cur['weather_code'];
                $icon = weatherIcon($code);
                $desc = weatherDesc($code);
                echo "
                <div class='current-weather'>
                    <div class='icon'>{$icon}</div>
                    <div>
                        <h2>{$name}, {$country}</h2>
                        <div class='temp'>{$cur['temperature_2m']}°C</div>
                        <div class='meta'>{$desc} &nbsp;|&nbsp; 💧 {$cur['relative_humidity_2m']}% &nbsp;|&nbsp; 💨 {$cur['wind_speed_10m']} км/ч</div>
                    </div>
                </div>";

                echo "<h3>Prognoza na 7 dni</h3><div class='forecast'>";
                foreach ($data['daily']['time'] as $i => $date) {
                    $dc   = (int)$data['daily']['weather_code'][$i];
                    $di   = weatherIcon($dc);
                    $max  = round($data['daily']['temperature_2m_max'][$i]);
                    $min  = round($data['daily']['temperature_2m_min'][$i]);
                    $dow  = $days[(int)date('w', strtotime($date))];
                    echo "<div class='forecast-card'>
                            <div class='day'>{$dow}<br>" . date('d.m', strtotime($date)) . "</div>
                            <div class='icon'>{$di}</div>
                            <div class='max'>{$max}°</div>
                            <div class='min'>{$min}°</div>
                          </div>";
                }
                echo "</div>";
            }
        }
    }
    ?>
</main>

<script>fetch("/log_visit.php").catch(()=>{});</script>
</body>
</html>
