<?php
/**
 * profil.php — Landing Page Detail Profil Anggota
 */
include __DIR__ . '/includes/koneksi.php';

// 1. Ambil ID dari URL (Contoh: profil.php?id=1)
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// 2. Cari data anggota di database
$stmt = $conn->prepare("SELECT * FROM team WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// 3. Jika ID tidak ada atau data kosong, tendang kembali ke halaman utama
if (!$user) {
    header("Location: indexbiografi.php");
    exit;
}

// Pecah data keahlian string menjadi array (dipisahkan koma)
$skills = array_map('trim', explode(',', $user['skills']));
?>
<!DOCTYPE html>
<html lang="id" class="h-full">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Profil Profil — <?= htmlspecialchars($user['name']) ?></title>
  
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
  
  <style>
    body {
      font-family: 'Outfit', sans-serif;
    }
    /* Efek Animasi Latar Belakang Bergerak Halus */
    .bg-gradient-animate {
      background: linear-gradient(-45deg, #ee7752, #e73c7e, #23a6d5, #23d5ab);
      background-size: 400% 400%;
      animation: gradientBG 15s ease infinite;
    }
    @keyframes gradientBG {
      0% { background-position: 0% 50%; }
      50% { background-position: 100% 50%; }
      100% { background-position: 0% 50%; }
    }
    /* Efek 3D Tilt Sederhana lewat CSS */
    .tilt-card {
      transition: transform 0.2s cubic-bezier(0.25, 1, 0.5, 1), box-shadow 0.2s ease;
      transform-style: preserve-3d;
    }
  </style>
</head>
<body class="bg-slate-900 text-slate-100 min-h-full flex flex-col justify-between overflow-x-hidden">

  <div class="absolute top-6 left-6 z-50">
    <a href="indexbiografi.php" class="group flex items-center gap-2 px-4 py-2.5 bg-slate-800/80 hover:bg-indigo-600 rounded-xl text-xs font-bold uppercase tracking-wider text-slate-300 hover:text-white backdrop-blur-md transition-all duration-300 shadow-lg hover:shadow-indigo-600/30 hover:-translate-y-0.5">
      ← Kembali ke Tim
    </a>
  </div>

  <div class="h-64 w-full bg-gradient-animate relative opacity-80"></div>

  <main class="max-w-5xl w-full mx-auto px-4 sm:px-6 lg:px-8 -mt-32 relative z-10 flex-1 pb-16">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-start">
      
      <div id="profileCard" class="tilt-card bg-slate-800/90 rounded-3xl p-6 border border-slate-700/50 shadow-2xl flex flex-col items-center text-center backdrop-blur-md group hover:border-indigo-500/50">
        <div class="relative w-40 h-40 rounded-full p-1 bg-gradient-to-tr from-indigo-500 to-emerald-400 shadow-xl transition-transform duration-500 group-hover:scale-105">
          <img src="<?= !empty($user['photo']) && file_exists($user['photo']) ? $user['photo'] : 'https://ui-avatars.com/api/?name='.urlencode($user['name']).'&background=4f46e5&color=fff' ?>" 
               alt="<?= htmlspecialchars($user['name']) ?>" 
               class="w-full h-full object-cover rounded-full bg-slate-700">
        </div>

        <h2 class="text-2xl font-black mt-5 tracking-tight text-white group-hover:text-indigo-400 transition-colors"><?= htmlspecialchars($user['name']) ?></h2>
        <p class="text-sm font-semibold text-indigo-400/90 mt-1 uppercase tracking-widest"><?= htmlspecialchars($user['role']) ?></p>
        
        <div class="w-full h-[1px] bg-slate-700/50 my-5"></div>
        
        <p class="text-xs italic text-slate-400 px-2 leading-relaxed">
          "<?= htmlspecialchars($user['quote'] ?: 'Tidak ada motto hidup.') ?>"
        </p>
      </div>

      <div class="md:col-span-2 space-y-6">
        
        <div class="bg-slate-800/60 rounded-3xl p-8 border border-slate-700/30 shadow-xl backdrop-blur-sm hover:border-slate-700 transition">
          <h3 class="text-lg font-bold text-white mb-3 flex items-center gap-2">
            <span class="w-2 h-5 bg-indigo-500 rounded-full"></span>
            Tentang Saya
          </h3>
          <p class="text-sm text-slate-300 leading-relaxed font-light">
            <?= nl2br(htmlspecialchars($user['full_bio'] ?: $user['short_bio'])) ?>
          </p>
        </div>

        <div class="bg-slate-800/60 rounded-3xl p-8 border border-slate-700/30 shadow-xl backdrop-blur-sm">
          <h3 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <span class="w-2 h-5 bg-emerald-400 rounded-full"></span>
            Keahlian & Kompetensi
          </h3>
          <div class="flex flex-wrap gap-2.5">
            <?php foreach ($skills as $skill): if(empty($skill)) continue; ?>
              <span class="px-4 py-2 bg-slate-900/80 hover:bg-indigo-600 hover:text-white border border-slate-700/80 rounded-xl text-xs font-semibold text-slate-300 cursor-default transition-all duration-300 transform hover:-translate-y-1 hover:shadow-lg hover:shadow-indigo-500/20">
                ⚡ <?= htmlspecialchars($skill) ?>
              </span>
            <?php endforeach; ?>
          </div>
        </div>

      </div>
    </div>
  </main>

  <footer class="w-full text-center py-6 border-t border-slate-800 text-xs text-slate-500">
    &copy; 2026 Kelompok 4 Biografi Web. All Rights Reserved.
  </footer>

  <script>
    const card = document.getElementById('profileCard');

    card.addEventListener('mousemove', (e) => {
      const rect = card.getBoundingClientRect();
      const x = e.clientX - rect.left; // Posisi X kursor di dalam kartu
      const y = e.clientY - rect.top;  // Posisi Y kursor di dalam kartu
      
      // Hitung sudut rotasi berdasarkan posisi kursor (Maksimal miring 8 derajat)
      const rotateX = ((rect.height / 2) - y) / (rect.height / 2) * 8;
      const rotateY = -( ((rect.width / 2) - x) / (rect.width / 2) * 8 );
      
      // Terapkan efek rotasi 3D dinamis
      card.style.transform = `perspective(1000px) rotateX(${rotateX}deg) rotateY(${rotateY}deg) scale(1.01)`;
      card.style.boxShadow = `${-rotateY * 3}px ${rotateX * 3}px 30px rgba(79, 70, 229, 0.15)`;
    });

    // Kembalikan ke posisi semula saat kursor keluar dari kartu
    card.addEventListener('mouseleave', () => {
      card.style.transform = 'perspective(1000px) rotateX(0deg) rotateY(0deg) scale(1)';
      card.style.boxShadow = 'none';
    });
  </script>
</body>
</html>