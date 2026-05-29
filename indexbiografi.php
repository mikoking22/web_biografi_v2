<?php
/**
 * indexbiografi.php — Halaman Utama Apple Premium Layout + Admin Access
 * Kelompok 4 Biografi Web Dashboard
 */
include __DIR__ . '/includes/koneksi.php';

// Ambil semua data anggota dari database
$query  = $conn->query("SELECT * FROM team ORDER BY id ASC");
$data   = [];
while ($row = $query->fetch_assoc()) {
    $row['skills'] = array_map('trim', explode(',', $row['skills']));
    $data[] = $row;
}
$teamDataJson = json_encode($data, JSON_UNESCAPED_UNICODE);
?>
<!doctype html>
<html lang="id" class="h-full scroll-smooth transition-colors duration-500 dark">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Biografi Kelompok 4 — Premium Presentation</title>

  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <script>
    tailwind.config = {
      darkMode: 'class',
      theme: {
        extend: {
          fontFamily: {
            sans: ['SF Pro Display', 'Inter', 'sans-serif'],
          }
        }
      }
    }
  </script>

  <script src="https://cdn.jsdelivr.net/npm/lucide@0.263.0/dist/umd/lucide.min.js"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css" />

  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">

  <style>
    body {
      font-family: 'Inter', sans-serif;
      -webkit-font-smoothing: antialiased;
    }
    /* Light/Dark dynamic background wallpaper ala Apple macOS standard */
    .apple-bg {
      background: radial-gradient(circle at 50% 0%, rgba(243, 244, 246, 1) 0%, rgba(229, 231, 235, 0.5) 100%);
    }
    .dark .apple-bg {
      background: radial-gradient(circle at 50% 0%, rgba(15, 23, 42, 1) 0%, rgba(2, 6, 23, 1) 100%);
    }
    /* Soft Neon Glow Wrapper */
    .neon-glow-indigo:hover {
      box-shadow: 0 20px 40px -15px rgba(99, 102, 241, 0.25);
    }
    /* Swiper Custom Pagination Button Styling */
    .swiper-pagination-bullet-active {
      background: #6366f1 !important;
      width: 24px !important;
      border-radius: 4px !important;
    }
  </style>
</head>
<body class="h-full overflow-x-hidden apple-bg text-slate-900 dark:text-slate-100 transition-colors duration-500">

<div id="app" class="w-full min-h-full flex flex-col justify-between pt-24 pb-10 relative">
  
  <nav class="fixed top-0 inset-x-0 z-50 bg-white/40 dark:bg-slate-950/40 border-b border-slate-200/40 dark:border-slate-800/40 backdrop-blur-xl shadow-sm transition-colors duration-500">
    <div class="max-w-6xl mx-auto h-16 px-6 flex items-center justify-between">
      
      <div class="flex items-center gap-2 cursor-pointer" onclick="window.location.reload()">
        <div class="w-8 h-8 rounded-lg bg-gradient-to-tr from-indigo-500 to-emerald-400 flex items-center justify-between p-1.5 shadow-sm text-white">
          <i data-lucide="layers" class="w-full h-full"></i>
        </div>
        <span class="text-sm font-semibold tracking-tight text-slate-900 dark:text-white font-sans">Kelompok 4</span>
      </div>

      <div class="flex items-center gap-3">
        
       

        <a href="admin.php" class="flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-indigo-600 border border-indigo-500 text-xs font-medium text-white hover:bg-indigo-500 shadow-sm shadow-indigo-600/20 hover:scale-105 active:scale-95 transition-all duration-300">
          <i data-lucide="layout-dashboard" class="w-3.5 h-3.5"></i>
          <span>Admin Panel</span>
        </a>

        <div class="h-4 w-[1px] bg-slate-200 dark:bg-slate-800 mx-1"></div>

        <button onclick="toggleAppleTheme()" class="p-2 rounded-full bg-slate-100/60 dark:bg-slate-800/60 hover:bg-slate-200 dark:hover:bg-slate-700 transition text-slate-700 dark:text-slate-300 cursor-pointer">
          <i data-lucide="sun" class="w-4 h-4 text-amber-500 dark:hidden"></i>
          <i data-lucide="moon" class="w-4 h-4 text-indigo-400 hidden dark:block"></i>
        </button>

      </div>

    </div>
  </nav>

  <header class="w-full max-w-4xl mx-auto px-6 text-center py-12">
    <span class="text-xs font-semibold tracking-widest uppercase bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 px-4 py-1.5 rounded-full backdrop-blur-md border border-indigo-500/20">
      Creative Innovation Team
    </span>
    <h1 class="text-4xl md:text-6xl font-bold tracking-tight mt-6 mb-4 bg-gradient-to-b from-slate-900 to-slate-700 dark:from-white dark:to-slate-400 bg-clip-text text-transparent">
      Biografi Kelompok 4
    </h1>
    <p class="text-sm md:text-base text-slate-500 dark:text-slate-400 max-w-lg mx-auto font-light leading-relaxed">
      Eksplorasi profil profesional, keahlian teknis, dan portofolio kontribusi setiap anggota tim melalui antarmuka interaktif.
    </p>
  </header>

  <main class="w-full max-w-6xl mx-auto px-6 my-auto">
    <div class="swiper teamSwiper !pb-14">
      <div class="swiper-wrapper" id="carouselWrapper">
        </div>
      <div class="swiper-pagination"></div>
    </div>
  </main>

  <footer class="w-full text-center text-xs tracking-wide text-slate-400 dark:text-slate-500 border-t border-slate-200/20 dark:border-slate-800/20 pt-8">
    &copy; 2026 Crafted with Apple Design Aesthetic. All Rights Reserved.
  </footer>

</div>

<script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>

<script>
  const teamData = <?= $teamDataJson ?>;

  // 1. FUNGSI TOGGLE THEME SYSTEM (LIGHT / DARK SYSTEM)
  function toggleAppleTheme() {
    const html = document.documentElement;
    if (html.classList.contains('dark')) {
      html.classList.remove('dark');
      localStorage.setItem('theme', 'light');
    } else {
      html.classList.add('dark');
      localStorage.setItem('theme', 'dark');
    }
  }

  if (localStorage.getItem('theme') === 'light') {
    document.documentElement.classList.remove('dark');
  }

  // 2. BUILD CARD UI & INJECT INTO CAROUSEL 3 CARD SYSTEM
  const wrapper = document.getElementById('carouselWrapper');
  
  teamData.forEach((person) => {
    const skillBadges = person.skills.map(skill => 
      `<span class="text-[10px] font-semibold px-2.5 py-1 bg-slate-100 dark:bg-slate-800/60 rounded-md border border-slate-200/30 dark:border-slate-700/30 text-slate-600 dark:text-slate-300">${skill}</span>`
    ).join('');

    const avatarImg = person.photo ? person.photo : `https://ui-avatars.com/api/?name=${encodeURIComponent(person.name)}&background=0f172a&color=fff`;

    const slideHtml = `
      <div class="swiper-slide h-auto">
        <div onclick="openProfilePage(${person.id})" 
             class="group relative h-full flex flex-col justify-between p-8 rounded-3xl bg-white/50 dark:bg-slate-900/40 border border-white/40 dark:border-slate-800/40 backdrop-blur-xl transition-all duration-500 cursor-pointer neon-glow-indigo overflow-hidden shadow-sm hover:-translate-y-2">
          
          <div class="absolute inset-x-0 top-0 h-24 bg-gradient-to-b from-white/20 dark:from-white/5 to-transparent pointer-events-none"></div>
          
          <div>
            <div class="flex items-center gap-4 relative z-10">
              <div class="w-14 h-14 rounded-2xl overflow-hidden shadow-inner p-0.5 bg-gradient-to-tr from-indigo-500 to-emerald-400 transition-transform duration-500 group-hover:scale-105">
                <img src="${avatarImg}" alt="${person.name}" class="w-full h-full object-cover rounded-[14px] bg-slate-100">
              </div>
              <div>
                <h3 class="text-lg font-bold tracking-tight text-slate-900 dark:text-white">${person.name}</h3>
                <p class="text-xs font-medium text-indigo-600 dark:text-indigo-400 mt-0.5 tracking-wide">${person.role}</p>
              </div>
            </div>

            <p class="text-xs font-light text-slate-500 dark:text-slate-400 mt-6 mb-6 leading-relaxed line-clamp-3">
              ${person.short_bio || 'Tidak ada deskripsi singkat.'}
            </p>
          </div>

          <div class="pt-4 border-t border-slate-200/30 dark:border-slate-800/40 flex flex-col gap-3">
            <div class="flex flex-wrap gap-1.5">
              ${skillBadges}
            </div>
            <div class="flex justify-end mt-1 text-[11px] font-semibold text-slate-400 dark:text-slate-500 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 transition-colors items-center gap-1">
              Lihat Profil Penuh <i data-lucide="arrow-right" class="w-3 h-3"></i>
            </div>
          </div>

        </div>
      </div>
    `;
    wrapper.insertAdjacentHTML('beforeend', slideHtml);
  });

  // 3. INITIALIZE CAROUSEL ENGINE (SWIPER ENGINE FOR 3 CARDS SEAMLESS GRID)
  document.addEventListener('DOMContentLoaded', () => {
    new Swiper('.teamSwiper', {
      slidesPerView: 1,
      spaceBetween: 24,
      grabCursor: true,
      pagination: {
        el: '.swiper-pagination',
        clickable: true,
      },
      breakpoints: {
        640: { slidesPerView: 2, spaceBetween: 24 },
        1024: { slidesPerView: 3, spaceBetween: 32 }
      }
    });
    
    if (typeof lucide !== 'undefined') lucide.createIcons();
  });

  function openProfilePage(id) {
    window.location.href = 'profil.php?id=' + id;
  }
</script>

</body>
</html>