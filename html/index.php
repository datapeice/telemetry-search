<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>datapeice SYSTEMS&CLOUDS — Enterprise Search Platform v9.1</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@300;400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<style>
  :root {
    --bg:      #000000;
    --surface: #0a0a0a;
    --surface2:#111111;
    --border:  #222222;
    --border2: #333333;
    --text:    #ffffff;
    --muted:   #666666;
    --dim:     #444444;
    --accent:  #ffffff;
    --accent:  #8a9ba8;
  }
  *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
  html { scroll-behavior: smooth; }

  body {
    background: var(--bg);
    color: var(--text);
    font-family: 'IBM Plex Sans', 'Helvetica Neue', Arial, sans-serif;
    font-size: 14px;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
    line-height: 1.6;
  }

  /* ── Header ── */
  header {
    border-bottom: 1px solid var(--border);
    padding: 0 48px;
    height: 52px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    position: sticky;
    top: 0;
    background: rgba(0,0,0,.92);
    backdrop-filter: blur(12px);
    z-index: 100;
  }
  .brand {
    display: flex;
    align-items: center;
    gap: 16px;
  }
  .brand-name {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 13px;
    font-weight: 600;
    letter-spacing: 0.05em;
    color: var(--text);
  }
  .brand-name em { color: var(--accent); font-style: normal; }
  .brand-ver {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 9px;
    color: var(--muted);
    border: 1px solid var(--border2);
    padding: 2px 6px;
    letter-spacing: 0.15em;
    text-transform: uppercase;
  }
  nav {
    display: flex;
    align-items: center;
    gap: 32px;
  }
  nav a {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--muted);
    text-decoration: none;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    transition: color .15s;
  }
  nav a:hover { color: var(--text); }
  nav a.active { color: var(--text); }
  .nav-license {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--accent) !important;
    border: 1px solid var(--accent);
    padding: 4px 12px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    transition: background .15s !important;
  }
  .nav-license:hover { background: var(--accent) !important; color: #000 !important; }

  /* ── Hero ── */
  .hero {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    padding: 80px 24px 60px;
  }

  .system-tag {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.25em;
    color: var(--muted);
    text-transform: uppercase;
    margin-bottom: 32px;
    display: flex;
    align-items: center;
    gap: 12px;
  }
  .system-tag::before,
  .system-tag::after {
    content: '';
    display: block;
    width: 40px;
    height: 1px;
    background: var(--border2);
  }

  h1 {
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: clamp(36px, 7vw, 80px);
    font-weight: 700;
    letter-spacing: -0.04em;
    line-height: 0.95;
    text-align: center;
    margin-bottom: 6px;
    color: var(--text);
  }
  h1 .accent { color: var(--accent); }

  .subtitle {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--muted);
    letter-spacing: 0.08em;
    text-align: center;
    margin-top: 16px;
    margin-bottom: 52px;
  }

  /* ── Search ── */
  .search-wrap { width: 100%; max-width: 720px; }

  .search-box {
    display: flex;
    border: 1px solid var(--border2);
    background: var(--surface);
    transition: border-color .2s;
  }
  .search-box:focus-within {
    border-color: var(--text);
  }
  .search-box input {
    flex: 1;
    background: transparent;
    border: none;
    outline: none;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 15px;
    color: var(--text);
    padding: 16px 20px;
    letter-spacing: 0.02em;
  }
  .search-box input::placeholder {
    color: var(--dim);
    font-weight: 300;
  }
  .btn-search {
    background: var(--text);
    color: var(--bg);
    border: none;
    padding: 0 28px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background .15s;
    white-space: nowrap;
  }
  .btn-search:hover { background: #e0e0e0; }
  .btn-lucky {
    background: transparent;
    color: var(--muted);
    border: none;
    border-left: 1px solid var(--border2);
    padding: 0 20px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    cursor: pointer;
    transition: color .15s, background .15s;
    white-space: nowrap;
  }
  .btn-lucky:hover { color: var(--text); background: var(--surface2); }

  .search-meta {
    display: flex;
    justify-content: space-between;
    margin-top: 8px;
    padding: 0 2px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    color: var(--muted);
    letter-spacing: 0.05em;
  }
  .search-meta a { color: var(--muted); text-decoration: none; }
  .search-meta a:hover { color: var(--text); }

  /* ── License notice ── */
  .license-notice {
    width: 100%;
    max-width: 720px;
    margin-top: 24px;
    border: 1px solid #1e2535;
    background: #0d0f12;
    padding: 14px 20px;
    display: flex;
    gap: 14px;
    align-items: flex-start;
  }
  .license-notice .ln-icon {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--accent);
    letter-spacing: 0.1em;
    white-space: nowrap;
    padding-top: 1px;
  }
  .license-notice p {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: #556270;
    line-height: 1.7;
    letter-spacing: 0.02em;
  }
  .license-notice strong { color: var(--accent); font-weight: 500; }
  .license-notice a { color: var(--accent); text-decoration: none; }
  .license-notice a:hover { text-decoration: underline; }

  /* ── Spinner ── */
  .spinner {
    display: none;
    width: 20px; height: 20px;
    border: 1px solid var(--border2);
    border-top-color: var(--text);
    border-radius: 50%;
    animation: spin .7s linear infinite;
    margin: 32px auto;
  }
  @keyframes spin { to { transform: rotate(360deg); } }

  /* ── Results ── */
  #results {
    width: 100%;
    max-width: 720px;
    margin-top: 28px;
    display: none;
  }
  .results-header {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    color: var(--muted);
    letter-spacing: 0.05em;
    padding-bottom: 12px;
    border-bottom: 1px solid var(--border);
    margin-bottom: 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
  }
  .results-header a {
    color: var(--accent);
    font-size: 10px;
    text-decoration: none;
    letter-spacing: 0.08em;
    text-transform: uppercase;
  }
  .results-header a:hover { text-decoration: underline; }

  .result-item {
    border-bottom: 1px solid var(--border);
    padding: 18px 0;
  }
  .result-item:last-child { border-bottom: none; }
  .ri-title {
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 15px;
    font-weight: 500;
    color: var(--text);
    margin-bottom: 4px;
    letter-spacing: -0.01em;
  }
  .ri-url {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    color: var(--dim);
    margin-bottom: 8px;
    letter-spacing: 0.02em;
  }
  .ri-snippet {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    color: var(--muted);
    line-height: 1.7;
    letter-spacing: 0.02em;
  }
  .ri-real {
    margin-top: 10px;
  }
  .ri-real a {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    color: var(--muted);
    text-decoration: none;
    border: 1px solid var(--border2);
    padding: 4px 10px;
    letter-spacing: 0.08em;
    text-transform: uppercase;
    transition: color .15s, border-color .15s;
  }
  .ri-real a:hover { color: var(--text); border-color: var(--text); }

  /* ── Footer ── */
  footer {
    border-top: 1px solid var(--border);
    padding: 16px 48px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    color: var(--muted);
    letter-spacing: 0.05em;
  }
  footer a { color: var(--muted); text-decoration: none; }
  footer a:hover { color: var(--text); }

  /* ── License Modal ── */
  .modal-bg {
    display: none;
    position: fixed;
    inset: 0;
    background: rgba(0,0,0,.85);
    backdrop-filter: blur(8px);
    z-index: 200;
    align-items: center;
    justify-content: center;
  }
  .modal-bg.open { display: flex; }
  .modal {
    background: var(--surface);
    border: 1px solid var(--border2);
    padding: 48px;
    max-width: 480px;
    width: 90%;
    position: relative;
  }
  .modal-close {
    position: absolute;
    top: 20px; right: 24px;
    background: none;
    border: none;
    color: var(--dim);
    font-size: 18px;
    cursor: pointer;
    font-family: 'IBM Plex Mono', monospace;
  }
  .modal-close:hover { color: var(--text); }
  .modal-header {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    letter-spacing: 0.2em;
    text-transform: uppercase;
    color: var(--muted);
    margin-bottom: 20px;
  }
  .modal h2 {
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 28px;
    font-weight: 700;
    letter-spacing: -0.03em;
    color: var(--text);
    margin-bottom: 4px;
  }
  .modal .modal-sub {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--muted);
    margin-bottom: 28px;
    letter-spacing: 0.03em;
  }
  .modal p {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--muted);
    line-height: 1.8;
    margin-bottom: 24px;
    letter-spacing: 0.02em;
  }
  .modal .price-table {
    border: 1px solid var(--border);
    margin-bottom: 28px;
  }
  .price-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 10px 16px;
    border-bottom: 1px solid var(--border);
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px;
    color: var(--muted);
  }
  .price-row:last-child { border-bottom: none; }
  .price-row .price { color: var(--accent); font-weight: 500; }
  .btn-buy {
    width: 100%;
    padding: 14px;
    background: var(--text);
    color: var(--bg);
    border: none;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px;
    font-weight: 600;
    letter-spacing: 0.15em;
    text-transform: uppercase;
    cursor: pointer;
    transition: background .15s;
  }
  .btn-buy:hover { background: #ddd; }
  .modal-note {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 10px;
    color: var(--muted);
    text-align: center;
    margin-top: 14px;
    letter-spacing: 0.03em;
    line-height: 1.8;
  }
</style>
</head>
<body>

<!-- License modal -->
<div class="modal-bg" id="licenseModal">
  <div class="modal">
    <button class="modal-close" onclick="closeLicenseModal()">✕</button>
    <div class="modal-header">datapeice SYSTEMS&amp;CLOUDS // Licencja Korporacyjna</div>
    <h2>Enterprise License</h2>
    <p class="modal-sub">Wymagana do korzystania z platformy wyszukiwania</p>
    <p>Niniejsza platforma jest własnością intelektualną <strong style="color:#fff">datapeice SYSTEMS&CLOUDS</strong> i podlega ochronie prawnej zgodnie z § 4.2.1 Regulaminu Licencji Korporacyjnej. Użytkowanie bez aktywnej licencji stanowi naruszenie prawa.</p>
    <div class="price-table">
      <div class="price-row"><span>Standard — 1 użytkownik / 30 dni</span><span class="price">49 EUR</span></div>
      <div class="price-row"><span>Business — do 50 użytkowników</span><span class="price">299 EUR / mies.</span></div>
      <div class="price-row"><span>Enterprise Unlimited</span><span class="price">999 EUR / mies.</span></div>
      <div class="price-row"><span>Rządowa / Publiczna</span><span class="price">Wycena</span></div>
    </div>
    <button class="btn-buy" onclick="alert('Dziękujemy za zainteresowanie!\n\nNasz dział sprzedaży skontaktuje się z Tobą w ciągu 2–5 dni roboczych.\n\nKontakt: license@datapeice.me')">
      Zamów licencję →
    </button>
    <div class="modal-note">
      Każde zapytanie jest logowane zgodnie z Polityką Telemetrii § 7.1.<br>
      Klikając „Zamów licencję" akceptujesz Regulamin datapeice SYSTEMS&amp;CLOUDS.
    </div>
  </div>
</div>

<header>
  <div class="brand">
    <span class="brand-name"><em>datapeice</em> SYSTEMS&amp;CLOUDS</span>
    <span class="brand-ver">Enterprise v9.1</span>
  </div>
  <nav>
    <a href="index.php" class="active">Wyszukiwarka</a>
    <a href="weather.php">Pogoda</a>
    <a href="globe.php">Globus Telemetrii</a>
    <a href="#" class="nav-license" onclick="openLicenseModal(); return false;">Kup Licencję</a>
  </nav>
</header>

<div class="hero">
  <div class="system-tag">Certified Enterprise Search Platform</div>

  <h1>datapeice<br><span class="accent">SYSTEMS&amp;CLOUDS</span></h1>
  <p class="subtitle">Korporacyjna platforma wyszukiwania · Pełna telemetria IP &amp; UA · Wymaga aktywnej licencji</p>

  <div class="search-wrap">
    <div class="search-box">
      <input type="text" id="q" placeholder="Wpisz zapytanie..." autocomplete="off" spellcheck="false">
      <button class="btn-search" onclick="doSearch()">Szukaj</button>
      <button class="btn-lucky" onclick="goLucky()" title="Otwiera DuckDuckGo + globus telemetrii">I'm Feeling Lucky</button>
    </div>
    <div class="search-meta">
      <span>§ 4.2.1 Regulaminu Licencji · Twoje IP i User-Agent są logowane</span>
      <a href="globe.php">Mapa telemetrii →</a>
    </div>
  </div>

  <div class="license-notice">
    <div class="ln-icon">[WARN]</div>
    <p>
      <strong>Wymagana licencja korporacyjna datapeice SYSTEMS&amp;CLOUDS.</strong><br>
      Korzystanie bez aktywnej licencji stanowi naruszenie § 12 Regulaminu i może skutkować postępowaniem prawnym.
      <a href="#" onclick="openLicenseModal(); return false;">Kup licencję teraz →</a>
    </p>
  </div>

  <div class="spinner" id="spinner"></div>
  <div id="results"></div>
</div>

<footer>
  <span>© 2026 datapeice SYSTEMS&amp;CLOUDS Sp. z o.o. · Wszelkie prawa zastrzeżone · PL/2024/00142</span>
  <span>
    <a href="globe.php">Telemetria</a> ·
    <a href="#" onclick="openLicenseModal(); return false;">Licencja</a> ·
    <a href="mailto:legal@datapeice.me">Kontakt prawny</a>
  </span>
</footer>

<script>
  const input = document.getElementById('q');
  input.addEventListener('keydown', e => { if (e.key === 'Enter') doSearch(); });

  function openLicenseModal()  { document.getElementById('licenseModal').classList.add('open'); }
  function closeLicenseModal() { document.getElementById('licenseModal').classList.remove('open'); }
  document.getElementById('licenseModal').addEventListener('click', function(e) {
    if (e.target === this) closeLicenseModal();
  });

  async function doSearch() {
    const q = input.value.trim();
    if (!q) { openLicenseModal(); return; }

    const spinner = document.getElementById('spinner');
    const results = document.getElementById('results');
    spinner.style.display = 'block';
    results.style.display = 'none';
    results.innerHTML = '';

    try {
      const res  = await fetch('/api/search.php?q=' + encodeURIComponent(q));
      const data = await res.json();
      spinner.style.display = 'none';

      if (data.error) {
        results.innerHTML = `<p style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:#666;padding:16px 0">[ERR] ${data.error}</p>`;
      } else {
        const count = Math.floor(Math.random() * 9e6 + 1e6).toLocaleString('pl-PL');
        const time  = (Math.random() * 0.4 + 0.05).toFixed(4);
        let html = `<div class="results-header">
          <span>~${count} wyników korporacyjnych · ${time}s · Zapytanie #${data.id} zarejestrowane</span>
          <a href="${data.ddg_url}" target="_blank" rel="noopener">DuckDuckGo →</a>
        </div>`;
        data.results.forEach((r, i) => {
          html += `<div class="result-item">
            <div class="ri-title">${r.title}</div>
            <div class="ri-url">${r.url}</div>
            <div class="ri-snippet">${r.snippet}</div>
            <div class="ri-real"><a href="${r.url}" target="_blank" rel="noopener">→ Szukaj w DuckDuckGo</a></div>
          </div>`;
        });
        results.innerHTML = html;
      }
      results.style.display = 'block';
    } catch(e) {
      spinner.style.display = 'none';
      results.innerHTML = `<p style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:#666;padding:16px 0">[ERR] Błąd połączenia z serwerem. Sprawdź status licencji.</p>`;
      results.style.display = 'block';
    }
  }

  function goLucky() {
    const q = input.value.trim();
    if (q) {
      fetch('/api/search.php?q=' + encodeURIComponent(q)).catch(() => {});
      window.open('https://duckduckgo.com/?q=' + encodeURIComponent(q), '_blank');
    }
    window.open('globe.php', '_blank');
  }
</script>
<script>fetch("/log_visit.php").catch(()=>{});</script></body>
</html>
