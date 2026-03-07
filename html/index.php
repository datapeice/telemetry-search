<!DOCTYPE html>
<html lang="en">
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
    <div class="modal-header">datapeice SYSTEMS&amp;CLOUDS // Enterprise License</div>
    <h2>Enterprise License</h2>
    <p class="modal-sub">Required to use the search platform</p>
    <p>This platform is the intellectual property of <strong style="color:#fff">datapeice SYSTEMS&CLOUDS</strong> and is legally protected under § 4.2.1 of the Enterprise License Agreement. Unauthorized use constitutes a violation of law.</p>
    <div class="price-table">
      <div class="price-row"><span>Standard — 1 user / 30 days</span><span class="price">49 EUR</span></div>
      <div class="price-row"><span>Business — up to 50 users</span><span class="price">299 EUR / mo.</span></div>
      <div class="price-row"><span>Enterprise Unlimited</span><span class="price">999 EUR / mo.</span></div>
      <div class="price-row"><span>Government / Public</span><span class="price">Custom</span></div>
    </div>
    <button class="btn-buy" onclick="alert('Thank you for your interest!\n\nOur sales department will contact you within 2-5 business days.\n\nContact: license@datapeice.me')">
      Order License →
    </button>
    <div class="modal-note">
      Every query is logged in accordance with Oculus Policy § 7.1.<br>
      By clicking "Order License", you accept the Terms and Conditions of datapeice SYSTEMS&amp;CLOUDS.
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
    <a href="index.php" class="active">Search</a>
    <a href="globe.php">Oculus</a>
    <a href="#" class="nav-license" onclick="openLicenseModal(); return false;">Buy License</a>
  </nav>
</header>

<div class="hero">
  <div class="system-tag">Certified Enterprise Search Platform</div>

  <h1><span style="font-size: 0.5em; display: block; margin-bottom: -10px; color: var(--text);">datapeice SYSTEMS&amp;CLOUDS</span><span class="accent" style="font-size: 1.3em;">HEGEMON</span></h1>
  <p class="subtitle">Enterprise Search Platform · Full Oculus IP &amp; UA · Active License Required</p>

  <div class="search-wrap">
    <div class="search-box">
      <input type="text" id="q" placeholder="Enter your query..." autocomplete="off" spellcheck="false">
      <button class="btn-search" onclick="doSearch()">Search</button>
      <button class="btn-lucky" onclick="goLucky()" title="Otwiera DuckDuckGo + Oculus">I'm Feeling Lucky</button>
    </div>
    <div class="search-meta">
      <span>§ 4.2.1 License Agreement · Your IP and User-Agent are logged</span>
      <a href="globe.php">Oculus Map →</a>
    </div>
  </div>

  <div class="license-notice">
    <div class="ln-icon">[WARN]</div>
    <p>
      <strong>Enterprise license required for datapeice SYSTEMS&amp;CLOUDS.</strong><br>
      Use without an active license is a violation of § 12 of the Terms and Conditions and may result in legal action.
      <a href="#" onclick="openLicenseModal(); return false;">Buy a license now →</a>
    </p>
  </div>

  <div class="spinner" id="spinner"></div>

  <div class="ai-container" id="ai-container">
    <div class="ai-header-badge">
      <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
      Hegemon AI // Live Analysis
    </div>
    <div class="ai-text" id="ai-text"></div>
  </div>

  <div id="results"></div>
</div>

<footer>
  <span>© 2026 datapeice SYSTEMS&amp;CLOUDS Sp. z o.o. · All rights reserved · PL/2024/00142</span>
  <span>
    <a href="globe.php">Oculus</a> ·
    <a href="#" onclick="openLicenseModal(); return false;">License</a> ·
    <a href="mailto:legal@datapeice.me">Legal Contact</a>
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
    streamAI(q); // Start AI analysis
    
    const spinner = document.getElementById('spinner');
    const results = document.getElementById('results');
    spinner.style.display = 'block';
    results.style.display = 'block'; // ensure it shows early
    results.innerHTML = '';
    // do not set display: 'none' to avoid vertical layout jumping
    
    try {
      const res  = await fetch('/api/search.php?q=' + encodeURIComponent(q));
      const data = await res.json();
      spinner.style.display = 'none';

      if (data.error) {
        results.innerHTML = `<p style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:#666;padding:16px 0">[ERR] ${data.error}</p>`;
      } else {
        const count = Math.floor(Math.random() * 9e6 + 1e6).toLocaleString('en-US');
        const time  = (Math.random() * 0.4 + 0.05).toFixed(4);
        let html = `<div class="results-header">
          <span>~${count} enterprise results · ${time}s · Search query #${data.id} logged</span>
          <a href="${data.ddg_url}" target="_blank" rel="noopener">DuckDuckGo →</a>
        </div>`;
        data.results.forEach((r, i) => {
          html += `<div class="result-item">
            <div class="ri-title"><a href="${r.url}" target="_blank" rel="noopener" style="color: inherit; text-decoration: none;">${r.title}</a></div>
            <div class="ri-url">${r.url}</div>
            <div class="ri-snippet">${r.snippet}</div>
          </div>`;
        });
        results.innerHTML = html;
      }
    } catch(e) {
      spinner.style.display = 'none';
      results.innerHTML = `<p style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:#666;padding:16px 0">[ERR] Server connection error. Check license status.</p>`;
    }
  }

  let aiEventSource = null;

  function streamAI(q) {
    if (aiEventSource) {
      aiEventSource.close();
    }
    const aiBox = document.getElementById('ai-container');
    const aiContent = document.getElementById('ai-text');
    aiBox.style.display = 'block';
    aiContent.innerHTML = '<span class="ai-loading">[SYSTEM] Initializing surveillance nodes & connecting to Hegemon AI...</span>';
    
    aiEventSource = new EventSource('/api/ai.php?q=' + encodeURIComponent(q));
    let isFirst = true;
    let fullText = "";

    aiEventSource.onmessage = function(event) {
      if (event.data === '[DONE]') {
        aiEventSource.close();
        return;
      }
      try {
        const data = JSON.parse(event.data);
        if (data.error) {
            aiBox.style.display = 'none'; // hide if disabled or no key
            aiEventSource.close();
            return;
        }
        if (isFirst) {
            aiContent.innerHTML = '';
            isFirst = false;
        }
        if (data.text) {
            fullText += data.text;
            if (typeof marked !== 'undefined') {
                aiContent.innerHTML = marked.parse(fullText);
            } else {
                let html = fullText.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;');
                html = html.replace(/\*\*(.*?)\*\*/g, '<strong style="color:var(--text)">$1</strong>');
                aiContent.innerHTML = html.replace(/\n/g, '<br>');
            }
        }
      } catch(e) {}
    };

    aiEventSource.onerror = function(err) {
      aiEventSource.close();
      if (isFirst) aiBox.style.display = 'none';
    };
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
<script src="https://cdn.jsdelivr.net/npm/marked/marked.min.js"></script>
<script>fetch("/log_visit.php").catch(()=>{});</script></body>
</html>
