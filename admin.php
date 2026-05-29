<?php
/**
 * admin.php — Dashboard Admin Panel (Full Feature with Insert & Delete Safely)
 * Redesigned: Professional Minimal Edition
 */

session_start();

// ── Proteksi Halaman ────────────────────────────────────────────────────────
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

include __DIR__ . '/includes/koneksi.php';

$flash = ['type' => '', 'msg' => ''];

// ── Handle POST: Logout ─────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'logout') {
    session_destroy();
    header('Location: login.php');
    exit;
}

// ── Handle POST: Tambah Anggota Baru ─────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'insert') {
    $name        = trim($_POST['name']        ?? '');
    $role        = trim($_POST['role']        ?? '');
    $short_bio   = trim($_POST['short_bio']   ?? '');
    $full_bio    = trim($_POST['full_bio']    ?? '');
    $skills      = trim($_POST['skills']      ?? '');
    $quote       = trim($_POST['quote']       ?? '');
    $bg_color    = '#f1f0eb';
    $card_color  = '#fffef9';
    $text_color  = '#0f172a';
    $font_family = 'Outfit';
    $photo       = '';

    if (isset($_FILES['photo_new']) && $_FILES['photo_new']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $ftype = mime_content_type($_FILES['photo_new']['tmp_name']);
        if (in_array($ftype, $allowed_types)) {
            $ext      = pathinfo($_FILES['photo_new']['name'], PATHINFO_EXTENSION);
            $safename = strtolower(preg_replace('/[^a-z0-9]/i', '_', $name));
            $new_path = 'images/' . $safename . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo_new']['tmp_name'], $new_path)) {
                $photo = $new_path;
            }
        }
    }

    if (!empty($name)) {
        $stmt = $conn->prepare(
            "INSERT INTO team (name, role, short_bio, full_bio, skills, quote, bg_color, card_color, text_color, font_family, photo) 
             VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssssssssss", $name, $role, $short_bio, $full_bio, $skills, $quote, $bg_color, $card_color, $text_color, $font_family, $photo);
        if ($stmt->execute()) {
            $flash = ['type' => 'success', 'msg' => "Anggota baru <strong>{$name}</strong> berhasil ditambahkan."];
        } else {
            $flash = ['type' => 'error', 'msg' => 'Gagal menambah data: ' . htmlspecialchars($conn->error)];
        }
        $stmt->close();
    } else {
        $flash = ['type' => 'error', 'msg' => 'Nama wajib diisi.'];
    }
}

// ── Handle POST: Update Anggota ─────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'update') {
    $id          = intval($_POST['id'] ?? 0);
    $name        = trim($_POST['name']        ?? '');
    $role        = trim($_POST['role']        ?? '');
    $short_bio   = trim($_POST['short_bio']   ?? '');
    $full_bio    = trim($_POST['full_bio']    ?? '');
    $skills      = trim($_POST['skills']      ?? '');
    $quote       = trim($_POST['quote']       ?? '');
    $bg_color    = trim($_POST['bg_color']    ?? '#f1f0eb');
    $card_color  = trim($_POST['card_color']  ?? '#fffef9');
    $text_color  = trim($_POST['text_color']  ?? '#0f172a');
    $font_family = trim($_POST['font_family']  ?? 'Outfit');

    $stmt_curr = $conn->prepare("SELECT photo FROM team WHERE id = ?");
    $stmt_curr->bind_param("i", $id);
    $stmt_curr->execute();
    $res_curr = $stmt_curr->get_result()->fetch_assoc();
    $photo = $res_curr['photo'] ?? '';
    $stmt_curr->close();

    if (isset($_FILES['photo_new']) && $_FILES['photo_new']['error'] === UPLOAD_ERR_OK) {
        $allowed_types = ['image/jpeg', 'image/png', 'image/webp'];
        $ftype = mime_content_type($_FILES['photo_new']['tmp_name']);
        if (in_array($ftype, $allowed_types)) {
            $ext      = pathinfo($_FILES['photo_new']['name'], PATHINFO_EXTENSION);
            $safename = strtolower(preg_replace('/[^a-z0-9]/i', '_', $name));
            $new_path = 'images/' . $safename . '_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['photo_new']['tmp_name'], $new_path)) {
                if (!empty($photo) && file_exists($photo)) @unlink($photo);
                $photo = $new_path;
            }
        }
    }

    if ($id > 0 && !empty($name)) {
        $stmt = $conn->prepare(
            "UPDATE team 
             SET name=?, role=?, short_bio=?, full_bio=?, skills=?, quote=?, bg_color=?, card_color=?, text_color=?, font_family=?, photo=? 
             WHERE id=?"
        );
        $stmt->bind_param("sssssssssssi", $name, $role, $short_bio, $full_bio, $skills, $quote, $bg_color, $card_color, $text_color, $font_family, $photo, $id);
        if ($stmt->execute()) {
            $flash = ['type' => 'success', 'msg' => "Data <strong>{$name}</strong> berhasil diperbarui."];
        } else {
            $flash = ['type' => 'error', 'msg' => 'Gagal memperbarui data: ' . htmlspecialchars($conn->error)];
        }
        $stmt->close();
    }
}

// ── Handle POST: Hapus Anggota ──────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['action'] ?? '') === 'delete') {
    $id = intval($_POST['id'] ?? 0);
    if ($id > 0) {
        $stmt_img = $conn->prepare("SELECT photo FROM team WHERE id = ?");
        $stmt_img->bind_param("i", $id);
        $stmt_img->execute();
        $img_res = $stmt_img->get_result()->fetch_assoc();
        if (!empty($img_res['photo']) && file_exists($img_res['photo'])) @unlink($img_res['photo']);
        $stmt_img->close();

        $stmt = $conn->prepare("DELETE FROM team WHERE id = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) {
            $flash = ['type' => 'success', 'msg' => 'Anggota berhasil dihapus dari tim.'];
        } else {
            $flash = ['type' => 'error', 'msg' => 'Gagal menghapus anggota.'];
        }
        $stmt->close();
    }
}

// ── Ambil Data Anggota Terbaru ───────────────────────────────────────────────
$result  = $conn->query("SELECT * FROM team ORDER BY id ASC");
$members = [];
while ($row = $result->fetch_assoc()) $members[] = $row;
$membersJson = json_encode($members, JSON_UNESCAPED_UNICODE);
$total       = count($members);
$activeCount = $total;
$pdfCount    = $total;
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Admin — Biografi Kelompok 4</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:ital,wght@0,300;0,400;0,500;0,600;1,400&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
      --bg:       #f5f4f1;
      --surface:  #ffffff;
      --border:   #e4e2dd;
      --border-2: #d0cec8;
      --ink-1:    #0d0d0b;
      --ink-2:    #3d3c38;
      --ink-3:    #7a7870;
      --ink-4:    #b0ada4;
      --accent:   #1a1a18;
      --accent-hover: #2d2d2a;
      --red:      #b91c1c;
      --red-bg:   #fef2f2;
      --red-border: #fecaca;
      --green:    #15803d;
      --green-bg: #f0fdf4;
      --green-border: #bbf7d0;
      --radius-sm: 6px;
      --radius:   10px;
      --radius-lg: 16px;
      --shadow-sm: 0 1px 3px rgba(0,0,0,0.06), 0 1px 2px rgba(0,0,0,0.04);
      --shadow:   0 4px 16px rgba(0,0,0,0.07), 0 1px 4px rgba(0,0,0,0.04);
      --shadow-lg: 0 20px 60px rgba(0,0,0,0.12), 0 4px 16px rgba(0,0,0,0.06);
    }

    html { font-size: 14px; }
    body {
      font-family: 'DM Sans', sans-serif;
      background: var(--bg);
      color: var(--ink-2);
      min-height: 100vh;
      -webkit-font-smoothing: antialiased;
    }

    /* ── Sidebar ────────────────────────────────────────────────── */
    .layout { display: flex; min-height: 100vh; }

    .sidebar {
      width: 220px;
      flex-shrink: 0;
      background: var(--ink-1);
      display: flex;
      flex-direction: column;
      position: sticky;
      top: 0;
      height: 100vh;
      overflow: hidden;
    }

    .sidebar-brand {
      padding: 24px 20px 20px;
      border-bottom: 1px solid rgba(255,255,255,0.06);
    }
    .sidebar-brand-mark {
      width: 32px; height: 32px;
      background: #fff;
      border-radius: var(--radius-sm);
      display: flex; align-items: center; justify-content: center;
      font-size: 13px; font-weight: 700; color: var(--ink-1);
      letter-spacing: -0.5px;
      margin-bottom: 12px;
    }
    .sidebar-brand-name {
      font-size: 13px; font-weight: 600;
      color: #fff; letter-spacing: -0.2px;
      line-height: 1.3;
    }
    .sidebar-brand-sub {
      font-size: 11px; color: rgba(255,255,255,0.35);
      margin-top: 2px;
    }

    .sidebar-nav { padding: 14px 10px; flex: 1; }
    .sidebar-nav-label {
      font-size: 10px; font-weight: 600;
      text-transform: uppercase; letter-spacing: 0.08em;
      color: rgba(255,255,255,0.25);
      padding: 0 10px; margin-bottom: 6px;
    }
    .sidebar-link {
      display: flex; align-items: center; gap: 9px;
      padding: 9px 10px;
      border-radius: var(--radius-sm);
      font-size: 13px; font-weight: 500;
      color: rgba(255,255,255,0.55);
      text-decoration: none;
      transition: background 0.15s, color 0.15s;
      cursor: pointer;
    }
    .sidebar-link:hover { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.9); }
    .sidebar-link.active {
      background: rgba(255,255,255,0.1);
      color: #fff;
    }
    .sidebar-link svg { width: 15px; height: 15px; flex-shrink: 0; opacity: 0.7; }
    .sidebar-link.active svg { opacity: 1; }

    .sidebar-footer {
      padding: 14px 10px;
      border-top: 1px solid rgba(255,255,255,0.06);
    }
    .sidebar-user {
      display: flex; align-items: center; gap: 9px;
      padding: 8px 10px;
    }
    .sidebar-avatar {
      width: 28px; height: 28px; border-radius: 50%;
      background: rgba(255,255,255,0.15);
      display: flex; align-items: center; justify-content: center;
      font-size: 11px; font-weight: 700; color: #fff;
      flex-shrink: 0;
    }
    .sidebar-user-info { flex: 1; min-width: 0; }
    .sidebar-user-name {
      font-size: 12px; font-weight: 600; color: rgba(255,255,255,0.85);
      white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .sidebar-user-role {
      font-size: 10px; color: rgba(255,255,255,0.3);
      margin-top: 1px;
    }
    .btn-logout {
      width: 100%;
      display: flex; align-items: center; gap: 9px;
      padding: 9px 10px;
      border-radius: var(--radius-sm);
      font-size: 12px; font-weight: 500;
      color: rgba(255,255,255,0.4);
      background: none; border: none;
      cursor: pointer;
      transition: background 0.15s, color 0.15s;
      margin-top: 4px;
      text-align: left;
    }
    .btn-logout:hover { background: rgba(255,255,255,0.06); color: rgba(255,255,255,0.7); }
    .btn-logout svg { width: 14px; height: 14px; }

    /* ── Main ───────────────────────────────────────────────────── */
    .main { flex: 1; min-width: 0; overflow-x: hidden; }

    .topbar {
      background: var(--surface);
      border-bottom: 1px solid var(--border);
      padding: 0 32px;
      height: 56px;
      display: flex; align-items: center; justify-content: space-between;
      position: sticky; top: 0; z-index: 30;
    }
    .topbar-title { font-size: 14px; font-weight: 600; color: var(--ink-1); }
    .topbar-date { font-size: 12px; color: var(--ink-4); font-family: 'DM Mono', monospace; }

    .content { padding: 28px 32px; max-width: 1100px; }

    /* ── Flash ──────────────────────────────────────────────────── */
    .flash {
      padding: 12px 16px;
      border-radius: var(--radius);
      font-size: 13px; font-weight: 500;
      border: 1px solid;
      margin-bottom: 20px;
      display: flex; align-items: center; gap: 10px;
    }
    .flash.success { background: var(--green-bg); color: var(--green); border-color: var(--green-border); }
    .flash.error   { background: var(--red-bg);   color: var(--red);   border-color: var(--red-border); }
    .flash-dot { width: 6px; height: 6px; border-radius: 50%; background: currentColor; flex-shrink: 0; }

    /* ── Stats ──────────────────────────────────────────────────── */
    .stats-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 14px; margin-bottom: 24px; }
    .stat-card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      padding: 20px 22px;
      box-shadow: var(--shadow-sm);
    }
    .stat-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.07em; color: var(--ink-4); margin-bottom: 8px; }
    .stat-value { font-size: 28px; font-weight: 700; color: var(--ink-1); letter-spacing: -1px; line-height: 1; }
    .stat-sub { font-size: 12px; color: var(--ink-4); margin-top: 4px; }

    /* ── Table card ─────────────────────────────────────────────── */
    .card {
      background: var(--surface);
      border: 1px solid var(--border);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-sm);
      overflow: hidden;
    }
    .card-header {
      padding: 16px 22px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: center; justify-content: space-between;
    }
    .card-title { font-size: 14px; font-weight: 600; color: var(--ink-1); }
    .card-count {
      font-size: 11px; font-weight: 600;
      background: var(--bg);
      border: 1px solid var(--border);
      color: var(--ink-3);
      padding: 3px 9px; border-radius: 100px;
      font-family: 'DM Mono', monospace;
    }

    table { width: 100%; border-collapse: collapse; }
    thead th {
      padding: 10px 20px;
      text-align: left;
      font-size: 10px; font-weight: 600;
      text-transform: uppercase; letter-spacing: 0.08em;
      color: var(--ink-4);
      background: var(--bg);
      border-bottom: 1px solid var(--border);
    }
    tbody tr { border-bottom: 1px solid var(--border); transition: background 0.1s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: #fafaf8; }
    td { padding: 14px 20px; vertical-align: middle; }

    .member-cell { display: flex; align-items: center; gap: 12px; }
    .member-avatar {
      width: 36px; height: 36px; border-radius: 50%;
      object-fit: cover;
      border: 1.5px solid var(--border);
      background: var(--bg);
      flex-shrink: 0;
    }
    .member-name { font-size: 13px; font-weight: 600; color: var(--ink-1); }
    .member-id { font-size: 11px; color: var(--ink-4); font-family: 'DM Mono', monospace; margin-top: 1px; }
    .role-badge {
      display: inline-block;
      font-size: 11px; font-weight: 500;
      padding: 3px 9px;
      background: var(--bg);
      border: 1px solid var(--border);
      border-radius: 100px;
      color: var(--ink-2);
    }
    .skills-text { font-size: 12px; color: var(--ink-4); max-width: 200px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }

    .actions-cell { display: flex; align-items: center; justify-content: flex-end; gap: 6px; }

    /* ── Buttons ─────────────────────────────────────────────────── */
    .btn {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 12px; font-weight: 500;
      padding: 7px 13px;
      border-radius: var(--radius-sm);
      border: 1px solid;
      cursor: pointer;
      transition: all 0.15s;
      text-decoration: none;
      font-family: 'DM Sans', sans-serif;
      white-space: nowrap;
    }
    .btn svg { width: 13px; height: 13px; }
    .btn-primary {
      background: var(--accent); color: #fff;
      border-color: var(--accent);
    }
    .btn-primary:hover { background: var(--accent-hover); border-color: var(--accent-hover); }
    .btn-ghost {
      background: transparent; color: var(--ink-2);
      border-color: var(--border);
    }
    .btn-ghost:hover { background: var(--bg); border-color: var(--border-2); }
    .btn-danger {
      background: transparent; color: var(--red);
      border-color: var(--red-border);
    }
    .btn-danger:hover { background: var(--red-bg); }
    .btn-pdf {
      background: transparent; color: var(--ink-3);
      border-color: var(--border);
      font-size: 11px;
    }
    .btn-pdf:hover { background: var(--bg); color: var(--ink-1); }

    .empty-state {
      padding: 56px 20px; text-align: center;
    }
    .empty-state p { font-size: 13px; color: var(--ink-4); }

    /* ── Modal ───────────────────────────────────────────────────── */
    .modal-overlay {
      position: fixed; inset: 0; z-index: 50;
      display: flex; align-items: center; justify-content: center;
      background: rgba(13,13,11,0.55);
      backdrop-filter: blur(4px);
      -webkit-backdrop-filter: blur(4px);
      opacity: 0; pointer-events: none;
      transition: opacity 0.2s;
    }
    .modal-overlay.open { opacity: 1; pointer-events: auto; }

    .modal {
      background: var(--surface);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      width: 100%; max-width: 480px;
      margin: 16px;
      overflow: hidden;
      transform: translateY(12px) scale(0.98);
      transition: transform 0.2s;
    }
    .modal-overlay.open .modal { transform: translateY(0) scale(1); }

    .modal-wide { max-width: 780px; display: flex; }

    .modal-header {
      padding: 20px 24px 16px;
      border-bottom: 1px solid var(--border);
      display: flex; align-items: flex-start; justify-content: space-between;
    }
    .modal-title { font-size: 14px; font-weight: 600; color: var(--ink-1); }
    .modal-subtitle { font-size: 12px; color: var(--ink-4); margin-top: 2px; }
    .modal-close {
      background: none; border: none; cursor: pointer;
      color: var(--ink-4); padding: 2px;
      border-radius: 4px; transition: color 0.15s;
      line-height: 1;
    }
    .modal-close:hover { color: var(--ink-1); }
    .modal-close svg { width: 16px; height: 16px; display: block; }

    .modal-body { padding: 20px 24px; overflow-y: auto; max-height: calc(90vh - 160px); }
    .modal-body::-webkit-scrollbar { width: 4px; }
    .modal-body::-webkit-scrollbar-track { background: transparent; }
    .modal-body::-webkit-scrollbar-thumb { background: var(--border); border-radius: 4px; }

    .modal-footer {
      padding: 14px 24px;
      border-top: 1px solid var(--border);
      display: flex; align-items: center; justify-content: flex-end; gap: 8px;
      background: var(--bg);
    }

    /* ── Sidebar panel (edit modal) ─────────────────────────────── */
    .modal-sidebar {
      width: 220px; flex-shrink: 0;
      background: var(--bg);
      border-right: 1px solid var(--border);
      padding: 20px;
      display: flex; flex-direction: column; gap: 16px;
    }
    .modal-sidebar-title { font-size: 12px; font-weight: 600; color: var(--ink-2); margin-bottom: 2px; }
    .modal-sidebar-sub { font-size: 11px; color: var(--ink-4); }
    .modal-main { flex: 1; display: flex; flex-direction: column; min-width: 0; overflow: hidden; }

    /* ── Form elements ───────────────────────────────────────────── */
    .form-group { display: flex; flex-direction: column; gap: 5px; }
    .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
    .form-stack { display: flex; flex-direction: column; gap: 14px; }
    label.lbl {
      font-size: 11px; font-weight: 600;
      text-transform: uppercase; letter-spacing: 0.07em;
      color: var(--ink-4);
    }
    .inp {
      width: 100%;
      padding: 9px 12px;
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      font-size: 13px; font-family: 'DM Sans', sans-serif;
      color: var(--ink-1); background: var(--surface);
      transition: border-color 0.15s, box-shadow 0.15s;
      outline: none;
    }
    .inp:focus { border-color: var(--ink-1); box-shadow: 0 0 0 3px rgba(13,13,11,0.08); }
    .inp::placeholder { color: var(--ink-4); }
    textarea.inp { resize: none; }
    select.inp { cursor: pointer; }

    .color-row { display: flex; align-items: center; gap: 10px; }
    .color-swatch {
      width: 32px; height: 32px;
      border-radius: var(--radius-sm);
      border: 1px solid var(--border);
      cursor: pointer;
      padding: 0;
      overflow: hidden;
    }
    input[type="color"] { width: 100%; height: 100%; border: none; padding: 0; cursor: pointer; }
    .color-hex { font-size: 11px; font-family: 'DM Mono', monospace; color: var(--ink-3); }

    .photo-row { display: flex; align-items: center; gap: 14px; padding-top: 14px; border-top: 1px solid var(--border); }
    .photo-thumb {
      width: 48px; height: 48px; border-radius: 50%;
      object-fit: cover; border: 1.5px solid var(--border);
      flex-shrink: 0;
    }
    .file-label { font-size: 11px; font-weight: 600; text-transform: uppercase; letter-spacing: 0.07em; color: var(--ink-4); margin-bottom: 5px; }
    input[type="file"] { font-size: 12px; color: var(--ink-3); }

    /* ── Delete modal ────────────────────────────────────────────── */
    .delete-icon {
      width: 44px; height: 44px; border-radius: 50%;
      background: var(--red-bg); border: 1px solid var(--red-border);
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 14px;
    }
    .delete-icon svg { width: 20px; height: 20px; color: var(--red); }
    .delete-title { font-size: 15px; font-weight: 600; color: var(--ink-1); text-align: center; margin-bottom: 6px; }
    .delete-desc { font-size: 13px; color: var(--ink-3); text-align: center; line-height: 1.5; }

    /* ── Divider ─────────────────────────────────────────────────── */
    .divider { height: 1px; background: var(--border); margin: 6px 0; }

    /* ── PDF link ────────────────────────────────────────────────── */
    .pdf-link {
      display: inline-flex; align-items: center; gap: 6px;
      font-size: 12px; font-weight: 500; color: var(--ink-3);
      text-decoration: none;
      padding: 7px 12px;
      border: 1px solid var(--border);
      border-radius: var(--radius-sm);
      transition: all 0.15s;
    }
    .pdf-link:hover { background: var(--bg); color: var(--ink-1); }
    .pdf-link svg { width: 13px; height: 13px; }
  </style>
</head>
<body>

<div class="layout">

  <!-- ── Sidebar ─────────────────────────────────────── -->
  <aside class="sidebar">
    <div class="sidebar-brand">
      <div class="sidebar-brand-mark">B</div>
      <div class="sidebar-brand-name">Biografi</div>
      <div class="sidebar-brand-sub">Kelompok 4</div>
    </div>

    <nav class="sidebar-nav">
      <div class="sidebar-nav-label">Menu</div>
      <a class="sidebar-link active">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0"/></svg>
        Data Anggota
      </a>
      <a href="indexbiografi.php" class="sidebar-link">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
        Lihat Website
      </a>
    </nav>

    <div class="sidebar-footer">
      <div class="sidebar-user">
        <div class="sidebar-avatar"><?= strtoupper(substr($_SESSION['admin_user'] ?? 'A', 0, 1)) ?></div>
        <div class="sidebar-user-info">
          <div class="sidebar-user-name"><?= htmlspecialchars($_SESSION['admin_user'] ?? 'Admin') ?></div>
          <div class="sidebar-user-role">Administrator</div>
        </div>
      </div>
      <form method="POST" action="">
        <input type="hidden" name="action" value="logout">
        <button type="submit" class="btn-logout">
          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
          Keluar
        </button>
      </form>
    </div>
  </aside>

  <!-- ── Main ───────────────────────────────────────── -->
  <div class="main">

    <div class="topbar">
      <span class="topbar-title">Dashboard Admin</span>
      <span class="topbar-date"><?= date('d M Y') ?></span>
    </div>

    <div class="content">

      <?php if (!empty($flash['msg'])): ?>
        <div class="flash <?= $flash['type'] ?>">
          <span class="flash-dot"></span>
          <?= $flash['msg'] ?>
        </div>
      <?php endif; ?>

      <!-- Stats -->
      <div class="stats-grid">
        <div class="stat-card">
          <div class="stat-label">Total Anggota</div>
          <div class="stat-value"><?= $total ?></div>
          <div class="stat-sub">terdaftar di sistem</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Profil Aktif</div>
          <div class="stat-value"><?= $activeCount ?></div>
          <div class="stat-sub">tampil di website</div>
        </div>
        <div class="stat-card">
          <div class="stat-label">Siap Cetak</div>
          <div class="stat-value"><?= $pdfCount ?></div>
          <div class="stat-sub">tersedia dalam PDF</div>
        </div>
      </div>

      <!-- Table -->
      <div class="card">
        <div class="card-header">
          <div style="display:flex;align-items:center;gap:10px;">
            <span class="card-title">Daftar Anggota Tim</span>
            <span class="card-count"><?= $total ?></span>
          </div>
          <button onclick="openAddModal()" class="btn btn-primary">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Tambah Anggota
          </button>
        </div>

        <div style="overflow-x:auto;">
          <table>
            <thead>
              <tr>
                <th>Anggota</th>
                <th>Role</th>
                <th>Keahlian</th>
                <th style="text-align:right;">Aksi</th>
              </tr>
            </thead>
            <tbody>
              <?php if ($total === 0): ?>
                <tr>
                  <td colspan="4">
                    <div class="empty-state">
                      <p>Belum ada anggota. Klik <strong>Tambah Anggota</strong> untuk mulai.</p>
                    </div>
                  </td>
                </tr>
              <?php else: ?>
                <?php foreach ($members as $m): ?>
                  <tr>
                    <td>
                      <div class="member-cell">
                        <img
                          src="<?= !empty($m['photo']) && file_exists($m['photo']) ? $m['photo'] : 'https://ui-avatars.com/api/?name='.urlencode($m['name']).'&background=1a1a18&color=fff&size=72' ?>"
                          class="member-avatar" alt="">
                        <div>
                          <div class="member-name"><?= htmlspecialchars($m['name']) ?></div>
                          <div class="member-id">#<?= $m['id'] ?></div>
                        </div>
                      </div>
                    </td>
                    <td><span class="role-badge"><?= htmlspecialchars($m['role'] ?: '—') ?></span></td>
                    <td><span class="skills-text"><?= htmlspecialchars($m['skills'] ?: '—') ?></span></td>
                    <td>
                      <div class="actions-cell">
                        <a href="cetak.php?id=<?= $m['id'] ?>" target="_blank" class="btn btn-pdf">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                          PDF
                        </a>
                        <button onclick='openEditModal(<?= htmlspecialchars(json_encode($m, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT)) ?>)' class="btn btn-ghost">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                          Edit
                        </button>
                        <button onclick="confirmDelete(<?= $m['id'] ?>, '<?= htmlspecialchars($m['name'], ENT_QUOTES) ?>')" class="btn btn-danger">
                          <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                          Hapus
                        </button>
                      </div>
                    </td>
                  </tr>
                <?php endforeach; ?>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

    </div><!-- /content -->
  </div><!-- /main -->
</div><!-- /layout -->

<!-- ══════════════════════════════════════════════
     EDIT MODAL
════════════════════════════════════════════════ -->
<div id="editModal" class="modal-overlay" onclick="if(event.target===this) closeEditModal()">
  <div class="modal modal-wide">

    <!-- Sidebar: Tema -->
    <div class="modal-sidebar">
      <div>
        <div class="modal-sidebar-title">Kustomisasi Tema</div>
        <div class="modal-sidebar-sub">Tampilan kartu di halaman utama</div>
      </div>
      <div class="divider"></div>

      <form id="editForm" method="POST" action="admin.php" enctype="multipart/form-data">
        <input type="hidden" name="action" value="update">
        <input type="hidden" name="id" id="f_id">

        <div class="form-stack">
          <div class="form-group">
            <label class="lbl">Latar Belakang</label>
            <div class="color-row">
              <div class="color-swatch"><input type="color" name="bg_color" id="f_bg_color"></div>
              <span id="lbl_bg_color" class="color-hex">#F1F0EB</span>
            </div>
          </div>
          <div class="form-group">
            <label class="lbl">Warna Kartu</label>
            <div class="color-row">
              <div class="color-swatch"><input type="color" name="card_color" id="f_card_color"></div>
              <span id="lbl_card_color" class="color-hex">#FFFEF9</span>
            </div>
          </div>
          <div class="form-group">
            <label class="lbl">Warna Teks</label>
            <div class="color-row">
              <div class="color-swatch"><input type="color" name="text_color" id="f_text_color"></div>
              <span id="lbl_text_color" class="color-hex">#0F172A</span>
            </div>
          </div>
          <div class="form-group">
            <label class="lbl">Gaya Font</label>
            <select name="font_family" id="f_font_family" class="inp">
              <option value="Outfit">Outfit</option>
              <option value="serif">Classic Serif</option>
              <option value="sans-serif">Modern Sans</option>
              <option value="mono">Developer Mono</option>
            </select>
          </div>
        </div>
      </form>
    </div><!-- /modal-sidebar -->

    <!-- Main: Profil -->
    <div class="modal-main">
      <div class="modal-header">
        <div>
          <div class="modal-title" id="editModalSubtitle">Edit Profil</div>
          <div class="modal-subtitle">Modifikasi biodata anggota</div>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
          <a id="btnCetakPDF" href="#" target="_blank" class="btn btn-pdf">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/></svg>
            Cetak PDF
          </a>
          <button onclick="closeEditModal()" class="modal-close">
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
          </button>
        </div>
      </div>

      <div class="modal-body">
        <div class="form-stack">
          <div class="form-grid">
            <div class="form-group">
              <label class="lbl">Nama Lengkap *</label>
              <input type="text" name="name" id="f_name" required class="inp" form="editForm">
            </div>
            <div class="form-group">
              <label class="lbl">Role / Posisi</label>
              <input type="text" name="role" id="f_role" class="inp" form="editForm">
            </div>
          </div>
          <div class="form-group">
            <label class="lbl">Bio Singkat</label>
            <input type="text" name="short_bio" id="f_short_bio" class="inp" form="editForm">
          </div>
          <div class="form-group">
            <label class="lbl">Bio Lengkap</label>
            <textarea name="full_bio" id="f_full_bio" rows="4" class="inp" form="editForm"></textarea>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label class="lbl">Keahlian (pisah koma)</label>
              <input type="text" name="skills" id="f_skills" class="inp" placeholder="PHP, MySQL, HTML" form="editForm">
            </div>
            <div class="form-group">
              <label class="lbl">Motto / Quote</label>
              <input type="text" name="quote" id="f_quote" class="inp" form="editForm">
            </div>
          </div>
          <div class="photo-row">
            <img id="f_photo_preview" src="" class="photo-thumb" alt="">
            <div>
              <div class="file-label">Ganti Foto Profil</div>
              <input type="file" name="photo_new" accept="image/*" onchange="previewPhoto(this)" form="editForm">
            </div>
          </div>
        </div>
      </div>

      <div class="modal-footer">
        <button type="button" onclick="closeEditModal()" class="btn btn-ghost">Batal</button>
        <button type="submit" form="editForm" class="btn btn-primary">Simpan Perubahan</button>
      </div>
    </div><!-- /modal-main -->

  </div>
</div>

<!-- ══════════════════════════════════════════════
     ADD MODAL
════════════════════════════════════════════════ -->
<div id="addModal" class="modal-overlay" onclick="if(event.target===this) closeAddModal()">
  <div class="modal">
    <div class="modal-header">
      <div>
        <div class="modal-title">Tambah Anggota Baru</div>
        <div class="modal-subtitle">Isi data profil untuk anggota kelompok</div>
      </div>
      <button onclick="closeAddModal()" class="modal-close">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
      </button>
    </div>

    <form method="POST" action="admin.php" enctype="multipart/form-data">
      <input type="hidden" name="action" value="insert">
      <div class="modal-body">
        <div class="form-stack">
          <div class="form-grid">
            <div class="form-group">
              <label class="lbl">Nama Lengkap *</label>
              <input type="text" name="name" required class="inp">
            </div>
            <div class="form-group">
              <label class="lbl">Role / Posisi</label>
              <input type="text" name="role" class="inp" placeholder="Lead Developer">
            </div>
          </div>
          <div class="form-group">
            <label class="lbl">Bio Singkat</label>
            <input type="text" name="short_bio" class="inp">
          </div>
          <div class="form-group">
            <label class="lbl">Bio Lengkap</label>
            <textarea name="full_bio" rows="3" class="inp"></textarea>
          </div>
          <div class="form-grid">
            <div class="form-group">
              <label class="lbl">Keahlian (pisah koma)</label>
              <input type="text" name="skills" class="inp" placeholder="PHP, HTML, MySQL">
            </div>
            <div class="form-group">
              <label class="lbl">Motto / Quote</label>
              <input type="text" name="quote" class="inp">
            </div>
          </div>
          <div class="form-group">
            <label class="lbl">Foto Profil</label>
            <input type="file" name="photo_new" accept="image/*">
          </div>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" onclick="closeAddModal()" class="btn btn-ghost">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan Anggota</button>
      </div>
    </form>
  </div>
</div>

<!-- ══════════════════════════════════════════════
     DELETE MODAL
════════════════════════════════════════════════ -->
<div id="deleteModal" class="modal-overlay" onclick="if(event.target===this) closeDeleteModal()">
  <div class="modal" style="max-width:360px;">
    <div style="padding:28px 24px 0;">
      <div class="delete-icon">
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
      </div>
      <div class="delete-title">Hapus Anggota?</div>
      <p class="delete-desc">Profil <strong id="deleteTargetName"></strong> akan dihapus permanen dan tidak bisa dikembalikan.</p>
    </div>
    <form method="POST" action="admin.php">
      <input type="hidden" name="action" value="delete">
      <input type="hidden" name="id" id="del_id_input">
      <div class="modal-footer" style="margin-top:20px;">
        <button type="button" onclick="closeDeleteModal()" class="btn btn-ghost">Batal</button>
        <button type="submit" class="btn" style="background:var(--red);color:#fff;border-color:var(--red);">Hapus</button>
      </div>
    </form>
  </div>
</div>

<script>
const members = <?= $membersJson ?>;

// ── Color picker labels ──────────────────────────────────────────────────────
document.addEventListener('DOMContentLoaded', () => {
  ['bg_color','card_color','text_color'].forEach(k => {
    const picker = document.getElementById('f_' + k);
    const label  = document.getElementById('lbl_' + k);
    if (picker && label) {
      picker.addEventListener('input', e => label.textContent = e.target.value.toUpperCase());
    }
  });
});

// ── Edit modal ───────────────────────────────────────────────────────────────
function openEditModal(person) {
  document.getElementById('f_id').value        = person.id;
  document.getElementById('f_name').value      = person.name;
  document.getElementById('f_role').value      = person.role;
  document.getElementById('f_short_bio').value = person.short_bio;
  document.getElementById('f_full_bio').value  = person.full_bio;
  document.getElementById('f_skills').value    = person.skills;
  document.getElementById('f_quote').value     = person.quote;

  const bg   = person.bg_color   || '#f1f0eb';
  const card = person.card_color || '#fffef9';
  const tx   = person.text_color || '#0f172a';

  document.getElementById('f_bg_color').value   = bg;
  document.getElementById('f_card_color').value = card;
  document.getElementById('f_text_color').value = tx;
  document.getElementById('lbl_bg_color').textContent   = bg.toUpperCase();
  document.getElementById('lbl_card_color').textContent = card.toUpperCase();
  document.getElementById('lbl_text_color').textContent = tx.toUpperCase();

  const preview = document.getElementById('f_photo_preview');
  preview.src = person.photo
    ? person.photo
    : 'https://ui-avatars.com/api/?name=' + encodeURIComponent(person.name) + '&background=1a1a18&color=fff&size=96';

  document.getElementById('editModalSubtitle').textContent = 'Edit — ' + person.name;
  document.getElementById('btnCetakPDF').href = 'cetak.php?id=' + person.id;

  openOverlay('editModal');
}
function closeEditModal() { closeOverlay('editModal'); }

// ── Add modal ────────────────────────────────────────────────────────────────
function openAddModal()  { openOverlay('addModal'); }
function closeAddModal() { closeOverlay('addModal'); }

// ── Delete modal ─────────────────────────────────────────────────────────────
function confirmDelete(id, name) {
  document.getElementById('del_id_input').value     = id;
  document.getElementById('deleteTargetName').textContent = name;
  openOverlay('deleteModal');
}
function closeDeleteModal() { closeOverlay('deleteModal'); }

// ── Overlay helpers ──────────────────────────────────────────────────────────
function openOverlay(id) {
  document.getElementById(id).classList.add('open');
  document.body.style.overflow = 'hidden';
}
function closeOverlay(id) {
  document.getElementById(id).classList.remove('open');
  document.body.style.overflow = '';
}

// ── Photo preview ────────────────────────────────────────────────────────────
function previewPhoto(input) {
  if (input.files && input.files[0]) {
    const reader = new FileReader();
    reader.onload = e => document.getElementById('f_photo_preview').src = e.target.result;
    reader.readAsDataURL(input.files[0]);
  }
}

// ── ESC key ──────────────────────────────────────────────────────────────────
document.addEventListener('keydown', e => {
  if (e.key === 'Escape') {
    closeEditModal();
    closeDeleteModal();
    closeAddModal();
  }
});
</script>
</body>
</html>