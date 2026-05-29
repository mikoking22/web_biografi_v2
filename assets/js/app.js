/**
 * assets/js/app.js
 * Logika utama Web Biografi Kelompok 4
 *
 * PENTING: Pastikan variabel global `teamData` sudah di-inject oleh PHP
 * di dalam indexbiografi.php SEBELUM tag <script src="assets/js/app.js">
 * Contoh:
 *   <script>const teamData = <?= json_encode($data); ?>;</script>
 *   <script src="assets/js/app.js"></script>
 */

if (typeof teamData === 'undefined') {
  console.error('[app.js] ERROR: teamData tidak ditemukan. Pastikan PHP sudah meng-inject data sebelum script ini dimuat.');
}

/* =========================================================
   RENDER CARDS
   Menggambar semua kartu anggota ke dalam #cardsGrid
   ========================================================= */
function renderCards() {
  const grid = document.getElementById('cardsGrid');
  if (!grid) return;
  grid.innerHTML = '';

  teamData.forEach((person, i) => {
    const card = document.createElement('div');
    card.className = 'card-wrap animate-in';
    card.style.animationDelay = `${0.3 + i * 0.12}s`;

    card.innerHTML = `
      <div class="bio-card rounded-2xl overflow-hidden cursor-pointer relative"
           style="background:#fffef9; border:1px solid #e2e0d9;"
           onmouseenter="showOverlay(${i})"
           onmouseleave="hideOverlay(${i})"
           onclick="openModal(${i})">
        <div class="p-8 text-center">
          <div class="avatar-ring mx-auto mb-5 w-fit">
            <div class="initial-circle overflow-hidden">
              <img src="${person.photo}" alt="${person.name}" class="w-full h-full object-cover">
            </div>
          </div>
          <h3 class="text-lg font-semibold mb-1"
              style="color:#0f172a; font-family:'Playfair Display',serif;">${person.name}</h3>
          <p class="text-xs font-medium mb-4"
             style="color:#94a3b8; letter-spacing:0.08em; text-transform:uppercase;">${person.role}</p>
          <div class="flex flex-wrap justify-center gap-1.5 mb-4">
            ${person.skills.map(s => `<span class="tag" style="background:#f1f0eb; color:#475569;">${s.trim()}</span>`).join('')}
          </div>
        </div>
        <div id="overlay-${i}"
             class="bio-overlay absolute inset-0 flex flex-col items-center justify-center p-8 text-center rounded-2xl"
             style="background:rgba(15,23,42,0.92);">
          <i data-lucide="quote" style="width:24px;height:24px;color:#94a3b8;" class="mb-3"></i>
          <p class="text-sm leading-relaxed mb-4" style="color:#e2e8f0;">${person.short_bio}</p>
          <span class="tag" style="background:rgba(248,250,252,0.15); color:#f8fafc;">
            <i data-lucide="mouse-pointer-click"
               style="width:12px;height:12px;display:inline;vertical-align:middle;margin-right:4px;"></i>
            Klik untuk detail
          </span>
        </div>
      </div>
    `;
    grid.appendChild(card);
  });

  if (typeof lucide !== 'undefined') lucide.createIcons();
}

/* =========================================================
   OPEN MODAL
   Buka modal detail dengan data dari teamData[i]
   ========================================================= */
function openModal(i) {
  const p = teamData[i];

  // Alihkan halaman ke landing page profil baru dengan membawa ID dari database
  window.location.href = 'profil.php?id=' + p.id;
}
/* =========================================================
   CLOSE MODAL
   ========================================================= */
function closeModal(e) {
  // Jika dipanggil dari overlay klik, pastikan klik tepat di overlay
  if (e && e.target !== e.currentTarget) return;
  document.getElementById('detailModal').classList.remove('open');
}

/* =========================================================
   OVERLAY HELPERS
   ========================================================= */
function showOverlay(i) {
  const el = document.getElementById(`overlay-${i}`);
  if (el) el.classList.add('active');
}

function hideOverlay(i) {
  const el = document.getElementById(`overlay-${i}`);
  if (el) el.classList.remove('active');
}

/* =========================================================
   APPLY CONFIG
   Mengubah warna/font halaman sesuai template anggota
   ========================================================= */
function applyConfig(config) {
  document.body.style.backgroundColor = config.background_color;
  const app = document.getElementById('app');
  if (app) app.style.backgroundColor = config.background_color;

  const title = document.getElementById('pageTitle');
  if (title) title.style.color = config.text_color;

  document.body.style.fontFamily = `'${config.font_family}', sans-serif`;
}

/* =========================================================
   INIT
   ========================================================= */
document.addEventListener('DOMContentLoaded', renderCards);
