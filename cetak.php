<?php
/**
 * cetak.php — Generator PDF Profil Anggota
 *
 * Menggunakan library FPDF (http://www.fpdf.org)
 * Cara pasang: Download fpdf.php → taruh di folder lib/fpdf/fpdf.php
 *
 * URL contoh: cetak.php?id=3
 *
 * Dilindungi Session — harus login admin terlebih dahulu.
 */

session_start();

// ── Proteksi Halaman ────────────────────────────────────────────────────────
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// ── Cek library FPDF ────────────────────────────────────────────────────────
$fpdf_file = __DIR__ . '/lib/fpdf/fpdf.php';
if (!file_exists($fpdf_file)) {
    die('
    <!doctype html><html lang="id"><head>
      <meta charset="UTF-8">
      <script src="https://cdn.tailwindcss.com/3.4.17"></script>
    </head><body class="min-h-screen flex items-center justify-center bg-gray-50">
      <div class="bg-white rounded-2xl p-10 max-w-md text-center shadow-lg">
        <div class="text-5xl mb-4">⚠️</div>
        <h2 class="text-xl font-bold text-gray-800 mb-3">Library FPDF Tidak Ditemukan</h2>
        <p class="text-gray-500 text-sm mb-4">
          File <code class="bg-gray-100 px-1 rounded">lib/fpdf/fpdf.php</code> belum ada.
        </p>
        <p class="text-gray-500 text-sm mb-4">
          1. Download FPDF dari <a href="http://www.fpdf.org" target="_blank" class="text-indigo-600 underline">fpdf.org</a><br>
          2. Ekstrak isinya ke folder <code class="bg-gray-100 px-1 rounded">lib/fpdf/</code><br>
          3. Pastikan file <code class="bg-gray-100 px-1 rounded">fpdf.php</code> ada di dalam folder itu.
        </p>
        <a href="admin.php" class="inline-block mt-2 px-5 py-2.5 rounded-xl bg-indigo-600 text-white text-sm font-medium">
          Kembali ke Admin
        </a>
      </div>
    </body></html>');
}

require_once $fpdf_file;

include __DIR__ . '/includes/koneksi.php';

// ── Ambil data anggota berdasarkan ID ───────────────────────────────────────
$id = intval($_GET['id'] ?? 0);
if ($id <= 0) {
    die('ID tidak valid. <a href="admin.php">Kembali</a>');
}

$stmt = $conn->prepare("SELECT * FROM team WHERE id = ? LIMIT 1");
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$p      = $result->fetch_assoc();
$stmt->close();

if (!$p) {
    die('Anggota dengan ID tersebut tidak ditemukan. <a href="admin.php">Kembali</a>');
}

// ── Helper: Konversi UTF-8 ke Latin (FPDF tidak support UTF-8 default) ──────
function u(string $text): string {
    return iconv('UTF-8', 'ISO-8859-1//TRANSLIT//IGNORE', $text);
}

// ── Buat class PDF dengan header & footer kustom ────────────────────────────
class BiografiPDF extends FPDF
{
    public string $memberName = '';
    public string $memberRole = '';

    function Header()
    {
        // Garis atas biru
        $this->SetFillColor(79, 70, 229);
        $this->Rect(0, 0, 210, 18, 'F');

        // Teks header
        $this->SetFont('Arial', 'B', 11);
        $this->SetTextColor(255, 255, 255);
        $this->SetXY(10, 5);
        $this->Cell(0, 8, u('PROFIL MAHASISWA'), 0, 0, 'C');

        // Sub header abu
        $this->SetFillColor(241, 245, 249);
        $this->Rect(0, 18, 210, 10, 'F');
        $this->SetFont('Arial', '', 8);
        $this->SetTextColor(100, 116, 139);
        $this->SetXY(10, 20);
        $this->Cell(0, 6, u('Dokumen resmi biografi mahasiswa teknik informatika'), 0, 0, 'C');
        $this->Ln(12);
    }

    function Footer()
    {
        $this->SetY(-15);
        $this->SetFillColor(241, 245, 249);
        $this->Rect(0, $this->GetPageHeight() - 15, 210, 15, 'F');

        $this->SetFont('Arial', 'I', 7.5);
        $this->SetTextColor(148, 163, 184);
        $this->Cell(0, 8,
            u('Dicetak: ' . date('d F Y, H:i') . '  •  Web Biografi Kelompok 4  •  Halaman ') . $this->PageNo(),
            0, 0, 'C'
        );
    }
    // Tambahkan fungsi ini di dalam class BiografiPDF { ... }
    function RoundedRect($x, $y, $w, $h, $r, $style = '', $angle = '1234') {
        $k = $this->k;
        $hp = $this->h;
        if($style=='F')
            $op='f';
        elseif($style=='FD' || $style=='DF')
            $op='B';
        else
            $op='S';
        $MyArc = 4/3 * (sqrt(2) - 1);
        $this->_out(sprintf('%.2F %.2F m',($x+$r)*$k,($hp-$y)*$k));

        $xc = $x+$w-$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-$y)*$k));
        if (strpos($angle, '2')===false)
            $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$y)*$k));
        else
            $this->_arc($xc + $r*$MyArc, $yc - $r, $xc + $r, $yc - $r*$MyArc, $xc + $r, $yc);

        $xc = $x+$w-$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-$yc)*$k));
        if (strpos($angle, '3')===false)
            $this->_out(sprintf('%.2F %.2F l',($x+$w)*$k,($hp-($y+$h))*$k));
        else
            $this->_arc($xc + $r, $yc + $r*$MyArc, $xc + $r*$MyArc, $yc + $r, $xc, $yc + $r);

        $xc = $x+$r;
        $yc = $y+$h-$r;
        $this->_out(sprintf('%.2F %.2F l',$xc*$k,($hp-($y+$h))*$k));
        if (strpos($angle, '4')===false)
            $this->_out(sprintf('%.2F %.2F l',$x*$k,($hp-($y+$h))*$k));
        else
            $this->_arc($xc - $r*$MyArc, $yc + $r, $xc - $r, $yc + $r*$MyArc, $xc - $r, $yc);

        $xc = $x+$r;
        $yc = $y+$r;
        $this->_out(sprintf('%.2F %.2F l',$x*$k,($hp-$yc)*$k));
        if (strpos($angle, '1')===false) {
            $this->_out(sprintf('%.2F %.2F l',$x*$k,($hp-$y)*$k));
            $this->_out(sprintf('%.2F %.2F l',($x+$r)*$k,($hp-$y)*$k));
        } else
            $this->_arc($xc - $r, $yc - $r*$MyArc, $xc - $r*$MyArc, $yc - $r, $xc, $yc - $r);
        $this->_out($op);
    }

    function _arc($x1, $y1, $x2, $y2, $x3, $y3) {
        $h = $this->h;
        $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c', $x1*$this->k, ($h-$y1)*$this->k,
            $x2*$this->k, ($h-$y2)*$this->k, $x3*$this->k, ($h-$y3)*$this->k));
    }
}

// ── Inisialisasi PDF ─────────────────────────────────────────────────────────
$pdf = new BiografiPDF('P', 'mm', 'A4');
$pdf->memberName = $p['name'];
$pdf->memberRole = $p['role'];
$pdf->SetAuthor('Admin Biografi');
$pdf->SetTitle(u('Profil ' . $p['name']));
$pdf->SetCreator('Web Biografi Kelompok 4');
$pdf->AddPage();
$pdf->SetMargins(15, 10, 15);
$pdf->SetAutoPageBreak(true, 20);

// ── Konten PDF ───────────────────────────────────────────────────────────────

// ─── Blok Foto + Identitas ──────────────────────────────────────────────────
$startY = $pdf->GetY() + 3;

// Kotak foto (kiri)
$photoPath = __DIR__ . '/' . ltrim($p['photo'], './');
$photoWidth  = 45;
$photoHeight = 50;
$photoX      = 15;

if ($p['photo'] && file_exists($photoPath)) {
    // Border foto
    $pdf->SetDrawColor(199, 210, 254);
    $pdf->SetLineWidth(0.8);
    $pdf->Rect($photoX - 0.5, $startY - 0.5, $photoWidth + 1, $photoHeight + 1, 'D');

    // Tempel gambar
    $ext = strtolower(pathinfo($photoPath, PATHINFO_EXTENSION));
    $fpdf_type = ($ext === 'png') ? 'PNG' : 'JPEG';
    $pdf->Image($photoPath, $photoX, $startY, $photoWidth, $photoHeight, $fpdf_type);
} else {
    // Placeholder jika foto tidak ada
    $pdf->SetFillColor(238, 242, 255);
    $pdf->Rect($photoX, $startY, $photoWidth, $photoHeight, 'F');
    $pdf->SetFont('Arial', 'B', 22);
    $pdf->SetTextColor(79, 70, 229);
    $pdf->SetXY($photoX, $startY + 15);
    $pdf->Cell($photoWidth, 20, strtoupper(substr($p['name'], 0, 1)), 0, 0, 'C');
}

// Identitas (kanan foto)
$infoX = $photoX + $photoWidth + 8;
$infoW = 210 - $infoX - 15;

// Nama
$pdf->SetXY($infoX, $startY);
$pdf->SetFont('Arial', 'B', 18);
$pdf->SetTextColor(15, 23, 42);
$pdf->MultiCell($infoW, 9, u($p['name']), 0, 'L');

// Role badge
$pdf->SetX($infoX);
$pdf->SetFillColor(238, 242, 255);
$pdf->SetTextColor(79, 70, 229);
$pdf->SetFont('Arial', 'B', 9);
$pdf->Cell($infoW, 7, u(strtoupper($p['role'])), 0, 1, 'L', true);

// Garis pembatas
$pdf->Ln(3);
$pdf->SetDrawColor(199, 210, 254);
$pdf->SetLineWidth(0.4);
$pdf->Line($infoX, $pdf->GetY(), $infoX + $infoW, $pdf->GetY());
$pdf->Ln(4);

// Bio singkat (italic)
if (!empty($p['short_bio'])) {
    $pdf->SetX($infoX);
    $pdf->SetFont('Arial', 'I', 9);
    $pdf->SetTextColor(100, 116, 139);
    $pdf->MultiCell($infoW, 5, u('"' . $p['short_bio'] . '"'), 0, 'L');
}

// Posisikan Y minimal setelah blok foto
$afterBlock = max($pdf->GetY(), $startY + $photoHeight + 3);
$pdf->SetY($afterBlock + 5);

// ─── Separator ──────────────────────────────────────────────────────────────
$pdf->SetFillColor(238, 242, 255);
$pdf->Rect(15, $pdf->GetY(), 180, 0.6, 'F');
$pdf->Ln(5);

// ─── Bio Lengkap ────────────────────────────────────────────────────────────
// Section title
$pdf->SetFillColor(79, 70, 229);
$pdf->Rect(15, $pdf->GetY(), 3, 7, 'F');
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(15, 23, 42);
$pdf->SetX(21);
$pdf->Cell(0, 7, u('Biografi Lengkap'), 0, 1, 'L');
$pdf->Ln(1);

$pdf->SetFont('Arial', '', 10);
$pdf->SetTextColor(71, 85, 105);
$pdf->SetX(15);
$pdf->MultiCell(180, 6, u($p['full_bio']), 0, 'J');
$pdf->Ln(5);

// ─── Keahlian ───────────────────────────────────────────────────────────────
$pdf->SetFillColor(79, 70, 229);
$pdf->Rect(15, $pdf->GetY(), 3, 7, 'F');
$pdf->SetFont('Arial', 'B', 11);
$pdf->SetTextColor(15, 23, 42);
$pdf->SetX(21);
$pdf->Cell(0, 7, u('Keahlian'), 0, 1, 'L');
$pdf->Ln(2);

$skills = array_map('trim', explode(',', $p['skills']));
$skillX = 15;
foreach ($skills as $skill) {
    $skill = trim($skill);
    if ($skill === '') continue;

    // Ukur lebar teks
    $pdf->SetFont('Arial', 'B', 9);
    $w = $pdf->GetStringWidth(u($skill)) + 8;

    // Pindah baris jika tidak cukup ruang
    if ($skillX + $w > 195) {
        $skillX = 15;
        $pdf->Ln(8);
    }

    // Gambar badge
    $tagY = $pdf->GetY();
    $pdf->SetFillColor(238, 242, 255);
    $pdf->SetDrawColor(199, 210, 254);
    $pdf->SetLineWidth(0.3);
    $pdf->RoundedRect($skillX, $tagY, $w, 6.5, 1.5, 'DF');

    $pdf->SetTextColor(79, 70, 229);
    $pdf->SetXY($skillX + 1, $tagY + 0.5);
    $pdf->Cell($w - 2, 5.5, u($skill), 0, 0, 'C');

    $skillX += $w + 3;
}
$pdf->Ln(12);

// ─── Motto / Quote ──────────────────────────────────────────────────────────
if (!empty($p['quote'])) {
    $pdf->SetFillColor(248, 250, 252);
    $pdf->SetDrawColor(199, 210, 254);
    $pdf->SetLineWidth(0.3);
    $quoteY = $pdf->GetY();
    $pdf->Rect(15, $quoteY, 180, 18, 'FD');

    // Tanda kutip dekoratif
    $pdf->SetFont('Arial', 'B', 24);
    $pdf->SetTextColor(199, 210, 254);
    $pdf->SetXY(17, $quoteY - 2);
    $pdf->Cell(10, 12, '"', 0, 0, 'L');

    $pdf->SetFont('Arial', 'I', 10);
    $pdf->SetTextColor(71, 85, 105);
    $pdf->SetXY(27, $quoteY + 4);
    $pdf->MultiCell(155, 5, u($p['quote']), 0, 'C');
}

// ── Output PDF ke browser ────────────────────────────────────────────────────
$safeName = preg_replace('/[^a-z0-9_\-]/i', '_', $p['name']);
$pdf->Output('I', 'Profil_' . $safeName . '.pdf');
exit;

// ── Helper: RoundedRect (tidak ada di FPDF default) ─────────────────────────
// Catatan: Method RoundedRect sudah ada di FPDF sejak v1.81
// Jika versi kamu lebih lama, tambahkan kode di bawah ke class BiografiPDF:
/*
function RoundedRect($x, $y, $w, $h, $r, $style = '')
{
    $k  = $this->k;
    $hp = $this->h;
    if ($style === 'F') $op = 'f';
    elseif ($style === 'FD' || $style === 'DF') $op = 'B';
    else $op = 'S';
    $MyArc = 4 / 3 * (sqrt(2) - 1);
    $this->_out(sprintf('%.2F %.2F m', ($x + $r) * $k, ($hp - $y) * $k));
    $xc = $x + $w - $r; $yc = $y + $r;
    $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - $y) * $k));
    $this->_Arc($xc, $yc, $r, 90, 0);
    $xc = $x + $w - $r; $yc = $y + $h - $r;
    $this->_out(sprintf('%.2F %.2F l', ($x + $w) * $k, ($hp - $yc) * $k));
    $this->_Arc($xc, $yc, $r, 0, -90);
    $xc = $x + $r; $yc = $y + $h - $r;
    $this->_out(sprintf('%.2F %.2F l', $xc * $k, ($hp - ($y + $h)) * $k));
    $this->_Arc($xc, $yc, $r, -90, -180);
    $xc = $x + $r; $yc = $y + $r;
    $this->_out(sprintf('%.2F %.2F l', $x * $k, ($hp - $yc) * $k));
    $this->_Arc($xc, $yc, $r, -180, -270);
    $this->_out($op);
}

function _Arc($x1, $y1, $r, $a1, $a2)
{
    $a1 = deg2rad($a1); $a2 = deg2rad($a2);
    $MyArc = 4 / 3 * tan(($a2 - $a1) / 4);
    $hp = $this->h; $k = $this->k;
    $this->_out(sprintf('%.2F %.2F %.2F %.2F %.2F %.2F c',
        ($x1 + $r * (cos($a1) - $MyArc * sin($a1))) * $k,
        ($hp - ($y1 - $r * (sin($a1) + $MyArc * cos($a1)))) * $k,
        ($x1 + $r * (cos($a2) + $MyArc * sin($a2))) * $k,
        ($hp - ($y1 - $r * (sin($a2) - $MyArc * cos($a2)))) * $k,
        ($x1 + $r * cos($a2)) * $k,
        ($hp - ($y1 - $r * sin($a2))) * $k
    ));
}
*/
