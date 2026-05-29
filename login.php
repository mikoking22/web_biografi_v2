<?php
/**
 * login.php — Halaman Login Admin
 * Menggunakan PHP Session + password_verify() untuk keamanan.
 */
session_start();

// Jika sudah login, langsung ke admin
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: admin.php');
    exit;
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    include __DIR__ . '/includes/koneksi.php';

    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Username dan password tidak boleh kosong.';
    } else {
        // Prepared statement — aman dari SQL Injection
        $stmt = $conn->prepare("SELECT id, username, password FROM users WHERE username = ? LIMIT 1");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        $user   = $result->fetch_assoc();
        $stmt->close();

        if ($user && password_verify($password, $user['password'])) {
            // Login berhasil — set session
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $user['id'];
            $_SESSION['admin_username']  = $user['username'];
            session_regenerate_id(true); // cegah session fixation

            header('Location: admin.php');
            exit;
        } else {
            $error = 'Username atau password salah. Silakan coba lagi.';
        }
    }
}
?>
<!doctype html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — Admin Biografi</title>
  <script src="https://cdn.tailwindcss.com/3.4.17"></script>
  <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;500;600;700&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    body { font-family: 'Outfit', sans-serif; }
    .input-focus:focus {
      outline: none;
      border-color: #4f46e5;
      box-shadow: 0 0 0 3px rgba(79,70,229,0.15);
    }
    .btn-login {
      background: linear-gradient(135deg, #4f46e5, #7c3aed);
      transition: opacity 0.2s ease, transform 0.15s ease;
    }
    .btn-login:hover { opacity: 0.9; transform: translateY(-1px); }
    .btn-login:active { transform: translateY(0); }
  </style>
</head>
<body class="min-h-screen flex items-center justify-center" style="background: linear-gradient(135deg,#f0f4ff 0%,#e8f0fe 100%);">

  <div class="w-full max-w-sm mx-4">

    <!-- Card -->
    <div class="bg-white rounded-2xl shadow-xl overflow-hidden">

      <!-- Top Banner -->
      <div class="py-8 px-8 text-center" style="background: linear-gradient(135deg,#4f46e5,#7c3aed);">
        <div class="w-14 h-14 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-3">
          <svg xmlns="http://www.w3.org/2000/svg" class="w-7 h-7 text-white" fill="none"
               viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
            <path stroke-linecap="round" stroke-linejoin="round"
                  d="M12 11c2.21 0 4-1.79 4-4S14.21 3 12 3 8 4.79 8 7s1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/>
          </svg>
        </div>
        <h1 class="text-white text-xl font-semibold">Admin Panel</h1>
        <p class="text-indigo-200 text-sm mt-1">Web Biografi Kelompok 4</p>
      </div>

      <!-- Form -->
      <div class="px-8 py-8">
        <?php if ($error): ?>
          <div class="mb-5 px-4 py-3 rounded-xl text-sm flex items-center gap-2"
               style="background:#fef2f2; color:#dc2626; border:1px solid #fecaca;">
            <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4 flex-shrink-0" fill="none"
                 viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round"
                 stroke-linejoin="round" stroke-width="2"
                 d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            <?= htmlspecialchars($error) ?>
          </div>
        <?php endif; ?>

        <form method="POST" action="login.php" autocomplete="off">

          <!-- Username -->
          <div class="mb-5">
            <label for="username" class="block text-sm font-medium mb-1.5" style="color:#374151;">
              Username
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color:#9ca3af;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                </svg>
              </span>
              <input type="text" id="username" name="username"
                     value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                     placeholder="Masukkan username"
                     class="input-focus w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm transition"
                     style="border-color:#e5e7eb; color:#111827;"
                     required autofocus>
            </div>
          </div>

          <!-- Password -->
          <div class="mb-6">
            <label for="password" class="block text-sm font-medium mb-1.5" style="color:#374151;">
              Password
            </label>
            <div class="relative">
              <span class="absolute left-3 top-1/2 -translate-y-1/2 pointer-events-none">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-4 h-4" style="color:#9ca3af;"
                     fill="none" viewBox="0 0 24 24" stroke="currentColor">
                  <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
              </span>
              <input type="password" id="password" name="password"
                     placeholder="Masukkan password"
                     class="input-focus w-full pl-9 pr-4 py-2.5 rounded-xl border text-sm transition"
                     style="border-color:#e5e7eb; color:#111827;"
                     required>
            </div>
          </div>

          <!-- Submit -->
          <button type="submit"
                  class="btn-login w-full py-3 rounded-xl text-white text-sm font-semibold tracking-wide">
            Masuk ke Dashboard
          </button>

        </form>

        <p class="text-center text-xs mt-5" style="color:#9ca3af;">
          Hanya untuk administrator yang berwenang.
        </p>
      </div>
    </div><!-- /card -->

    <!-- Back to site link -->
    <p class="text-center mt-4 text-sm" style="color:#6b7280;">
      <a href="indexbiografi.php" class="hover:underline" style="color:#4f46e5;">
        ← Kembali ke Halaman Utama
      </a>
    </p>
  </div>

</body>
</html>
