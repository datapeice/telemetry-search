<!DOCTYPE html>
<html lang="pl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<title>datapeice SYSTEMS&CLOUDS — Enterprise Search Platform v9.1</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@300;400;500;600&family=IBM+Plex+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
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
      Każde zapytanie jest logowane zgodnie z Polityką Oculus § 7.1.<br>
      Klikając „Zamów licencję" akceptujesz Regulamin datapeice SYSTEMS&amp;CLOUDS.
    </div>
  </div>
</div>

<header>
  <div class="brand">
    <img src="favicon.svg" alt="logo" style="height: 26px; width: 26px; filter: drop-shadow(0 0 4px rgba(255,255,255,0.2));">
    <span class="brand-name"><em>datapeice</em> SYSTEMS&amp;CLOUDS</span>
    <span class="brand-ver">Enterprise v9.1</span>
  </div>
  <nav>
    <a href="index.php" class="active">Wyszukiwarka</a>
    <a href="globe.php">Oculus</a>
    <a href="#" class="nav-license" onclick="openLicenseModal(); return false;">Kup Licencję</a>
  </nav>
</header>

<div class="hero">
  <div class="system-tag">Certified Enterprise Search Platform</div>

  <h1>datapeice<br><span class="accent">SYSTEMS&amp;CLOUDS</span></h1>
  <p class="subtitle">Korporacyjna platforma wyszukiwania · Pełny Oculus IP &amp; UA · Wymaga aktywnej licencji</p>

  <div class="search-wrap">
    <div class="search-box">
      <input type="text" id="q" placeholder="Wpisz zapytanie..." autocomplete="off" spellcheck="false">
      <button class="btn-search" onclick="doSearch()">Szukaj</button>
      <button class="btn-lucky" onclick="goLucky()" title="Otwiera DuckDuckGo + Oculus">I'm Feeling Lucky</button>
    </div>
    <div class="search-meta">
      <span>§ 4.2.1 Regulaminu Licencji · Twoje IP i User-Agent są logowane</span>
      <a href="globe.php">Mapa Oculus →</a>
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
    <a href="globe.php">Oculus</a> ·
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

  let audioInstance = null;
  let easterEggActive = false;

  const israelTriggers = [
    // English
    'israel', 'tel aviv', 'jerusalem', 'zion', 'zionism', 'idf', 'mossad', 'shalom', 'hebrew', 'knesset', 'haifa', 'judaism',
    // Russian
    'израиль', 'тель-авив', 'тель авив', 'иерусалим', 'сион', 'сионизм', 'цахал', 'моссад', 'шалом', 'иврит', 'кнессет', 'хайфа', 'иудаизм',
    // Polish
    'izrael', 'tel awiw', 'jerozolima', 'syjon', 'syjonizm', 'cahal', 'mosad', 'szalom', 'hebrajski', 'kneset', 'hajfa', 'judaizm', 'żydzi', 'żyd', 'żydowskie'
  ];

  // Preload easter egg resources
  const preloadedImg = new Image();
  preloadedImg.src = 'israel_img.webp';
  const preloadedAudio = new Audio();
  preloadedAudio.preload = 'auto';
  preloadedAudio.src = 'israel.mp3';

  function checkEasterEggs(q) {
    if (israelTriggers.some(t => q.toLowerCase().includes(t)) && !easterEggActive) {
      easterEggActive = true;
      if (audioInstance) {
        audioInstance.pause();
        audioInstance.currentTime = 0;
      }
      audioInstance = new Audio('israel.mp3');
      audioInstance.volume = 1.0;
      
      const img = document.createElement('img');
      img.src = 'israel_img.webp';
      img.style.position = 'fixed';
      img.style.bottom = '0px';
      img.style.right = '40px';
      img.style.width = 'clamp(180px, 50vw, 450px)';
      img.style.zIndex = '9998';
      img.style.borderRadius = '12px';
      img.style.opacity = '0';
      img.style.transform = 'translateY(100px)';
      img.style.transition = 'opacity 1s, transform 1s';
      img.style.cursor = 'pointer';
      document.body.appendChild(img);
      
      const size = window.innerWidth <= 768 ? 60 : 100;
      const starSvgContent = `<path d="M 47.631413,492.11477 552.36859,492.11491 300,54.999953 Z M 300,637.81981 552.36851,200.70493 47.631403,200.70481 Z" fill="none" stroke="#00f" stroke-width="55"/>`;
      
      const bgStar = document.createElement('div');
      bgStar.innerHTML = `<svg width="100%" height="100%" viewBox="0 0 600 692.82" preserveAspectRatio="xMidYMid meet">${starSvgContent}</svg>`;
      bgStar.style.position = 'fixed';
      bgStar.style.bottom = 'clamp(100px, 20vw, 180px)';
      bgStar.style.right = 'clamp(80px, 25vw, 200px)';
      bgStar.style.width = 'clamp(130px, 30vw, 260px)';
      bgStar.style.height = 'clamp(130px, 30vw, 260px)';
      bgStar.style.zIndex = '9997';
      bgStar.style.opacity = '0';
      bgStar.style.transform = 'translateY(100px)';
      bgStar.style.transition = 'opacity 1s, transform 1s';
      bgStar.style.pointerEvents = 'none';
      bgStar.style.filter = 'drop-shadow(0 0 30px rgba(0, 0, 255, 0.6))';
      document.body.appendChild(bgStar);

      const star = document.createElement('div');
      // Classic flag of Israel star (hexagram) outline
      star.innerHTML = `<svg width="${size}" height="${size}" viewBox="0 0 600 692.82">${starSvgContent}</svg>`;
      star.style.position = 'fixed';
      star.style.width = size + 'px';
      star.style.height = size + 'px';
      star.style.zIndex = '9999';
      star.style.pointerEvents = 'none';
      star.style.filter = 'drop-shadow(0 0 10px #ffffff) drop-shadow(0 0 20px rgba(0, 56, 184, 0.6))';
      document.body.appendChild(star);
      
      let posX = Math.random() * (window.innerWidth - size);
      let posY = Math.random() * (window.innerHeight - size);
      let speedX = (Math.random() > 0.5 ? 1 : -1) * (2 + Math.random() * 3);
      let speedY = (Math.random() > 0.5 ? 1 : -1) * (2 + Math.random() * 3);
      let rot = 0;
      let animating = true;
      
      audioInstance.play().catch(e => console.log('Audio blocked:', e));
      
      setTimeout(() => {
        img.style.opacity = '1';
        img.style.transform = 'translateY(0)';
        bgStar.style.opacity = '0.4';
        bgStar.style.transform = 'translateY(0)';
      }, 100);
      
      function animate() {
        if (!animating) return;
        posX += speedX;
        posY += speedY;
        rot += 1.5;
        
        if (posX <= 0 || posX >= window.innerWidth - size) speedX *= -1;
        if (posY <= 0 || posY >= window.innerHeight - size) speedY *= -1;
        
        star.style.left = posX + 'px';
        star.style.top = posY + 'px';
        star.style.transform = `rotate(${rot}deg)`;
        
        requestAnimationFrame(animate);
      }
      requestAnimationFrame(animate);
      
      let stopped = false;
      const stopEgg = () => {
        if (stopped) return;
        stopped = true;
        animating = false;
        img.style.opacity = '0';
        img.style.transform = 'translateY(100px)';
        star.style.opacity = '0';
        bgStar.style.opacity = '0';
        bgStar.style.transform = 'translateY(100px)';
        star.style.transition = 'opacity 1s';
        setTimeout(() => {
          if (img.parentNode) img.remove();
          if (star.parentNode) star.remove();
          if (bgStar.parentNode) bgStar.remove();
          easterEggActive = false;
        }, 1000);
      };

      audioInstance.onended = stopEgg;
      audioInstance.ontimeupdate = () => {
        if (audioInstance.duration && audioInstance.currentTime >= audioInstance.duration - 10) {
          stopEgg();
        }
      };
      img.onclick = () => {
         audioInstance.pause();
         stopEgg();
      };
    }
  }

  async function doSearch() {
    const q = input.value.trim();
    if (!q) { openLicenseModal(); return; }

    checkEasterEggs(q);

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
