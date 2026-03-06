<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<title>datapeice Oculus</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Mono:wght@300;400;500;600&family=IBM+Plex+Sans:wght@400;500;600&display=swap" rel="stylesheet">
<link rel="stylesheet" href="globe.css">
</head>
<body>
<div id="loading"><div class="load-bar"></div><div>Initializing telemetry · datapeice Oculus</div></div>
<div id="canvas-container"></div>
<div id="topbar">
  <div class="tb-left">
    <img src="favicon.svg" alt="logo" style="height: 24px; width: 24px; filter: drop-shadow(0 0 4px rgba(255,255,255,0.1));">
    <div class="tb-brand"><em>datapeice</em> Oculus</div>
    <div class="tb-sep"></div>
    <div class="tb-status"><span class="dot"></span>Telemetry · Live</div>
    <div class="tb-sep"></div>
    <div class="tb-count">Queries: <strong id="stat-total">—</strong> &nbsp;·&nbsp; Locations: <strong id="stat-countries">—</strong></div>
  </div>
  <div class="tb-right">
    <a class="tb-btn" href="index.php">← Search</a>
    <button class="tb-btn" onclick="refreshData()" id="btn-refresh">↻ Refresh</button>
  </div>
</div>
<button id="toggle-panel" onclick="togglePanel()">☰</button>
<div id="panel">
  <div class="panel-header">
    <div class="panel-title">// Query Log</div>
    <div class="panel-search"><input type="text" id="panel-filter" placeholder="Filter..." oninput="filterList(this.value)"></div>
  </div>
  <div class="panel-list" id="panel-list"></div>
</div>
<div id="tooltip">
  <div class="tt-loc" id="tt-loc"></div>
  <div class="tt-ip" id="tt-ip"></div>
  <div class="tt-queries" id="tt-queries"></div>
  <div class="tt-ua" id="tt-ua"></div>
</div>
<div id="lock-indicator" onclick="unlockGlobe()">⊙ Locked on marker · Click to reset view</div>

<script src="https://cdnjs.cloudflare.com/ajax/libs/three.js/r128/three.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/topojson-client@3/dist/topojson-client.min.js"></script>
<script>
let panelOpen = window.innerWidth > 768;
const getW = () => window.innerWidth <= 768 ? window.innerWidth : window.innerWidth - (panelOpen ? 300 : 0);
const H = () => window.innerWidth <= 768 ? window.innerHeight - (panelOpen ? 300 : 0) : window.innerHeight;

const scene = new THREE.Scene();
scene.background = new THREE.Color(0x0a0a0a); // solid single color background

let camW = getW();
const camera = new THREE.PerspectiveCamera(45, camW/H(), 0.1, 1000);
const renderer = new THREE.WebGLRenderer({ antialias: true, alpha: false });
renderer.setPixelRatio(Math.min(devicePixelRatio, 2));
renderer.setSize(camW, H());
document.getElementById('canvas-container').appendChild(renderer.domElement);

// Target zoom limited so map doesn't get too small
// Zoom out limit = 3.5, Zoom in limit = 0.5
let targetX = 0, targetY = 1.0, targetZ = 3.5; 
camera.position.set(targetX, targetY, targetZ);

const mapGroup = new THREE.Group();
scene.add(mapGroup);

// Server location (Warsaw)
const SERVER_LAT = 52.23;
const SERVER_LON = 21.01;

function latLonToVec3(lat, lon) {
  return new THREE.Vector3(lon * 0.03, lat * 0.03, 0);
}

// Background map layer to cover clicks
const bgMesh = new THREE.Mesh(new THREE.PlaneGeometry(360*0.03, 180*0.03), new THREE.MeshBasicMaterial({ color: 0x0a0a0a, visible: false }));
mapGroup.add(bgMesh);

// Borders
function addBorders(world) {
  const mesh = topojson.mesh(world, world.objects.countries);
  if (!mesh) return;
  const pts = [];
  function ring(coords) {
    for (let i=0; i<coords.length-1; i++) {
      if (Math.abs(coords[i][0]-coords[i+1][0]) > 180) continue; 
      const a = latLonToVec3(coords[i][1], coords[i][0]);
      const b = latLonToVec3(coords[i+1][1], coords[i+1][0]);
      pts.push(a.x, a.y, a.z, b.x, b.y, b.z);
    }
  }
  if (mesh.type === 'MultiLineString') mesh.coordinates.forEach(ring);
  else if (mesh.type === 'LineString') ring(mesh.coordinates);
  
  const geo = new THREE.BufferGeometry();
  geo.setAttribute('position', new THREE.Float32BufferAttribute(pts, 3));
  mapGroup.add(new THREE.LineSegments(geo, new THREE.LineBasicMaterial({ color: 0xe74c3c, transparent:true, opacity:0.8 })));
}
fetch('https://cdn.jsdelivr.net/npm/world-atlas@2/countries-110m.json').then(r=>r.json()).then(addBorders).catch(console.error);

// Add Server Marker
const svPos = latLonToVec3(SERVER_LAT, SERVER_LON);
const svRing = new THREE.Mesh(new THREE.RingGeometry(0.04, 0.06, 16), new THREE.MeshBasicMaterial({ color: 0x4a8f6a }));
svRing.position.set(svPos.x, svPos.y, 0.01);
mapGroup.add(svRing);

// ─── Markers & Animations ──────────────────────────────────────────────────
let locationGroups = {}; // key: "lat_lon" -> { dot, ring, lat, lon, queries: [] }
let allData = [];
let lockedOnKey = null;
let packets = []; // for animations

function createPacketAnim(fromPos, toPos, isResponse) {
  const midX = (fromPos.x + toPos.x) / 2;
  const midY = (fromPos.y + toPos.y) / 2;
  const dist = fromPos.distanceTo(toPos);
  
  // Create arc effect bending "up" (increasing Y) slightly, and "out" (increasing Z)
  // This makes the trajectory appear convex and curved like a real flight path on a flat map
  const archHeightY = dist * 0.15; // arc slightly northward
  const midZ = Math.max(0.1, dist * 0.3); // curve outwards towards camera
  
  const curve = new THREE.QuadraticBezierCurve3(
    fromPos,
    new THREE.Vector3(midX, midY + archHeightY, midZ),
    toPos
  );
  
  const pointsCount = 40;
  const pts = curve.getPoints(pointsCount);
  const geo = new THREE.BufferGeometry().setFromPoints(pts);
  
  const mat = new THREE.LineBasicMaterial({
    color: 0xff1111,
    transparent: true,
    opacity: 0.8,
    linewidth: 2
  });
  
  const line = new THREE.Line(geo, mat);
  line.geometry.setDrawRange(0, 0);
  
  packets.push({
    progress: 0,
    mesh: line,
    pointsCount: pointsCount + 1
  });
  mapGroup.add(line);
}

function processSearchRow(d, animate) {
  const key = d.lat + '_' + d.lon;
  if (!locationGroups[key]) {
    const pos = latLonToVec3(parseFloat(d.lat), parseFloat(d.lon));
    const dot = new THREE.Mesh(new THREE.CircleGeometry(0.018, 16), new THREE.MeshBasicMaterial({ color: 0x8a9ba8 }));
    dot.position.set(pos.x, pos.y, 0.02);
    dot.userData = { key };
    mapGroup.add(dot);
    
    const ring = new THREE.Mesh(new THREE.RingGeometry(0.025, 0.04, 16), new THREE.MeshBasicMaterial({ color: 0x8a9ba8, transparent: true, opacity: 0.4 }));
    ring.position.set(pos.x, pos.y, 0.01);
    mapGroup.add(ring);
    
    locationGroups[key] = { dot, ring, lat: d.lat, lon: d.lon, queries: [] };
  }
  
  // Add to start of history
  locationGroups[key].queries.unshift(d);
  
  if (animate) {
    const lPos = latLonToVec3(parseFloat(d.lat), parseFloat(d.lon));
    createPacketAnim(lPos, svPos, false); // request
    setTimeout(() => { createPacketAnim(svPos, lPos, true); }, 1500); // response
  }
}

// ─── Interactivity ─────────────────────────────────────────────────────────────
function flyTo(lat, lon, zoom) {
  targetX = lon * 0.03;
  targetY = lat * 0.03;
  if (zoom) targetZ = 1.0; // constrained zoom
}

function lockLocation(key) {
  lockedOnKey = key;
  const lg = locationGroups[key];
  flyTo(parseFloat(lg.lat), parseFloat(lg.lon), true);
  highlightMarker(key);
  document.getElementById('lock-indicator').classList.add('visible');
  showGroupTooltip(lg, camW/2 - 160, H()/2 + 40);
  
  // Try to find the newest query in the right list and highlight it
  const firstId = lg.queries[0].id;
  const listIdx = listData.findIndex(item => item.id == firstId);
  if (listIdx >= 0) focusListItem(listIdx);
}

function unlockGlobe() {
  lockedOnKey = null;
  targetZ = 3.5;
  resetMarkerColors();
  document.getElementById('lock-indicator').classList.remove('visible');
  document.getElementById('tooltip').style.display = 'none';
  document.querySelectorAll('.query-item').forEach(el => el.classList.remove('active'));
}

function highlightMarker(key) {
  Object.keys(locationGroups).forEach(k => {
    const m = locationGroups[k];
    m.dot.material.color.set(k === key ? 0xffffff : 0x8a9ba8);
    m.ring.material.opacity = k === key ? 0.9 : 0.4;
  });
}
function resetMarkerColors() {
  Object.keys(locationGroups).forEach(k => {
    const m = locationGroups[k];
    m.dot.material.color.set(0x8a9ba8);
    m.ring.material.opacity = 0.4;
  });
}

const raycaster = new THREE.Raycaster();
const mouse2d = new THREE.Vector2(-9, -9);

let isDragging = false, dragMoved = false, prevX = 0, prevY = 0, lastTouchDist = 0;
renderer.domElement.addEventListener('mousedown', e => {
  isDragging = true; dragMoved = false; prevX = e.clientX; prevY = e.clientY;
});
renderer.domElement.addEventListener('touchstart', e => {
  if(e.touches.length === 1) {
    isDragging = true; dragMoved = false; prevX = e.touches[0].clientX; prevY = e.touches[0].clientY;
  } else if (e.touches.length === 2) {
    lastTouchDist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
  }
}, {passive: false});

window.addEventListener('mouseup', () => { isDragging = false; });
window.addEventListener('touchend', () => { isDragging = false; });

function handleMove(clientX, clientY) {
  const dx = clientX - prevX, dy = clientY - prevY;
  if (isDragging) {
    if (Math.abs(dx)>2 || Math.abs(dy)>2) dragMoved = true;
    if (!lockedOnKey) {
      const panSpd = (camera.position.z / H()) * 1.5;
      targetX -= dx * panSpd;
      targetY += dy * panSpd;
      // limit pan
      targetX = Math.max(-4.5, Math.min(4.5, targetX));
      targetY = Math.max(-1.8, Math.min(2.8, targetY));
    }
    prevX = clientX; prevY = clientY;
  }
  
  if (!isDragging || !dragMoved) {
    mouse2d.x = (clientX/camW)*2-1;
    mouse2d.y = -(clientY/H())*2+1;
    raycaster.setFromCamera(mouse2d, camera);
    const meshes = Object.values(locationGroups).map(g=>g.dot);
    const hits = raycaster.intersectObjects(meshes);
    if (hits.length > 0 && !lockedOnKey) {
      const key = hits[0].object.userData.key;
      highlightMarker(key);
      showGroupTooltip(locationGroups[key], clientX+18, clientY-10);
    } else if (!lockedOnKey) {
      document.getElementById('tooltip').style.display='none';
      resetMarkerColors();
    }
  }
}

window.addEventListener('mousemove', e => {
  handleMove(e.clientX, e.clientY);
});
renderer.domElement.addEventListener('touchmove', e => {
  if (e.touches.length === 1) {
    e.preventDefault(); 
    handleMove(e.touches[0].clientX, e.touches[0].clientY);
  } else if (e.touches.length === 2) {
    e.preventDefault();
    const dist = Math.hypot(e.touches[0].clientX - e.touches[1].clientX, e.touches[0].clientY - e.touches[1].clientY);
    const delta = lastTouchDist - dist;
    targetZ = Math.max(0.6, Math.min(3.5, targetZ + delta * 0.01));
    lastTouchDist = dist;
  }
}, {passive: false});

renderer.domElement.addEventListener('click', e => {
  if (dragMoved) return;
  mouse2d.x = (e.clientX/camW)*2-1;
  mouse2d.y = -(e.clientY/H())*2+1;
  raycaster.setFromCamera(mouse2d, camera);
  const hits = raycaster.intersectObjects(Object.values(locationGroups).map(g=>g.dot));
  if (hits.length > 0) {
    const key = hits[0].object.userData.key;
    if (lockedOnKey === key) unlockGlobe(); else lockLocation(key);
  } else if (lockedOnKey) unlockGlobe();
});

// zoom limit
renderer.domElement.addEventListener('wheel', e => {
  targetZ = Math.max(0.6, Math.min(3.5, targetZ + e.deltaY * 0.005));
}, { passive: true });

function showGroupTooltip(lg, cx, cy) {
  const d = lg.queries[0]; // newest
  document.getElementById('tt-loc').textContent = [d.city, d.country].filter(Boolean).join(', ') || 'Unknown';
  document.getElementById('tt-ip').textContent = 'IP: ' + (d.ip || '?');
  
  const qList = lg.queries.slice(0, 10).map(q => 
    `<div class="tt-q-item"><div class="q">${esc(q.query)}</div><span class="time">${new Date(q.searched_at).toLocaleTimeString('en-US')}</span></div>`
  ).join('');
  document.getElementById('tt-queries').innerHTML = qList + (lg.queries.length>10?'<div style="font-size:9px;color:#444;text-align:center;margin-top:4px">...</div>':'');
  document.getElementById('tt-ua').innerHTML = '<div style="color:#555;font-size:9px;margin-bottom:4px;text-transform:uppercase">Device Intelligence</div><div><strong>Type:</strong> ' + (d.device_type||'Unknown') + '</div><div><strong>OS:</strong> ' + (d.os||'Unknown') + '</div><div><strong>Hardware:</strong> ' + (d.device_model||'Generic') + '</div><div style="margin-top:4px;padding-top:4px;border-top:1px solid #111;color:#555;font-size:8px;word-break:break-all">' + (d.user_agent||'') + '</div>';

  const tip = document.getElementById('tooltip');
  tip.style.display = 'block';
  tip.style.left = Math.min(cx, window.innerWidth - (panelOpen?300:0) - 340) + 'px';
  tip.style.top  = Math.max(58, Math.min(cy, H() - tip.offsetHeight - 20)) + 'px';
}

function animate() {
  requestAnimationFrame(animate);
  camera.position.set(
    camera.position.x + (targetX - camera.position.x)*0.1,
    camera.position.y + (targetY - camera.position.y)*0.1,
    camera.position.z + (targetZ - camera.position.z)*0.1
  );
  
  // Update packet anims
  for (let i=packets.length-1; i>=0; i--) {
    let p = packets[i];
    p.progress += 0.015; // smooth speed
    if (p.progress >= 1.4) {
      if (p.mesh.geometry) p.mesh.geometry.dispose();
      if (p.mesh.material) p.mesh.material.dispose();
      mapGroup.remove(p.mesh);
      packets.splice(i, 1);
    } else {
      let head = Math.floor(p.progress * p.pointsCount);
      let tailLength = 15; // How long the curve line tail is
      let tail = Math.max(0, head - tailLength);
      
      if (head > p.pointsCount) head = p.pointsCount;
      
      // fade out when reaching the end
      if (p.progress > 1.0) {
         p.mesh.material.opacity = 0.8 * (1.4 - p.progress) / 0.4;
      }
      
      p.mesh.geometry.setDrawRange(tail, head - tail);
    }
  }

  // rotate map slightly slower to face screen
  renderer.render(scene, camera);
}
animate();

window.addEventListener('resize', () => {
  camW = getW();
  camera.aspect = camW / H();
  camera.updateProjectionMatrix();
  renderer.setSize(camW, H());
});

function togglePanel() {
  panelOpen = !panelOpen;
  const panel = document.getElementById('panel');
  const btn = document.getElementById('toggle-panel');
  const cc = document.getElementById('canvas-container');
  
  if (window.innerWidth <= 768) {
    if (panelOpen) {
      panel.classList.add('open');
      panel.classList.remove('hidden');
      btn.style.bottom = '300px';
      btn.classList.remove('ph');
      cc.classList.add('shifted');
    } else {
      panel.classList.remove('open');
      panel.classList.add('hidden');
      btn.style.bottom = '20px';
      btn.classList.add('ph');
      cc.classList.remove('shifted');
    }
  } else {
    panel.classList.toggle('hidden', !panelOpen);
    btn.style.right = panelOpen ? '300px' : '0';
    btn.classList.toggle('ph', !panelOpen);
    cc.style.right = panelOpen ? '300px' : '0';
  }
  
  setTimeout(()=>window.dispatchEvent(new Event('resize')), 250);
}

let listData = [];
function buildList(data) {
  listData = data;
  applyFilter();
}
function applyFilter() {
  const v = (document.getElementById('panel-filter').value || '').toLowerCase();
  let f = listData.map((d,i)=>({d,i})).filter(({d}) => 
    d.query !== '[SITE VISIT]' && d.query !== '[PAGE LOAD]'
  );
  if (v) {
    f = f.filter(({d})=>
      (d.query||'').toLowerCase().includes(v)||(d.city||'').toLowerCase().includes(v)
    );
  }
  renderList(f);
}
function filterList() { applyFilter(); }
function renderList(mappedData) {
  const el = document.getElementById('panel-list');
  if(!mappedData.length){el.innerHTML='<div class="panel-empty">No data</div>';return;}
  el.innerHTML = mappedData.map(({d, i}) => {
    const key = d.lat+'_'+d.lon;
    const isActive = lockedOnKey === key;
    return `<div class="query-item${isActive?' active':''}" onclick="onListClick(${i})" id="qi-${i}">
      <div class="qi-query">${esc(d.query)}</div>
      <div class="qi-meta"><span class="loc">${esc([d.city,d.country].filter(Boolean).join(', ')||'—')}</span>
      <span>${new Date(d.searched_at).toLocaleTimeString('en-US')}</span></div>
    </div>`;
  }).join('');
}
function onListClick(listIdx) {
  const itemData = listData[listIdx];
  const key = itemData.lat+'_'+itemData.lon;
  if(lockedOnKey===key) unlockGlobe(); else lockLocation(key);
}
function focusListItem(idx) {
  document.querySelectorAll('.query-item').forEach(el=>el.classList.remove('active'));
  const qi = document.getElementById('qi-'+idx);
  if(qi){qi.classList.add('active'); qi.scrollIntoView({behavior:'smooth',block:'nearest'});}
}
function esc(s){return String(s||'').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;');}

let knownIds = new Set();
let firstLoad = true;

function refreshData() {
  fetch('/api/telemetry.php?_='+Date.now())
    .then(r=>r.json())
    .then(data=>{
      document.getElementById('loading').style.display='none';
      if(!Array.isArray(data)) return;
      
      const newRows = data.filter(d=>!knownIds.has(d.id||d.ip+d.searched_at)).reverse(); // old to new
      
      newRows.forEach(row=>{
        knownIds.add(row.id||row.ip+row.searched_at);
        if(row.lat && row.lon) processSearchRow(row, !firstLoad);
      });
      
      allData = data;
      document.getElementById('stat-total').textContent = data.length;
      document.getElementById('stat-countries').textContent = Object.keys(locationGroups).length;
      buildList(data);
      firstLoad = false;
    }).catch(()=>{document.getElementById('loading').style.display='none';});
}
refreshData();
setInterval(refreshData, 1000);

// Update canvas container correct right padding initial state
document.getElementById('canvas-container').style.right = panelOpen ? '300px' : '0';

// Log visit quietly for telemetry visualization
fetch("/log_visit.php").catch(()=>{});
</script>
</body>
</html>
