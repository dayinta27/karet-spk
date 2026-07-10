<?php
session_start();
include 'koneksi.php';

// ========================================================================
// CEK SESSION & AKSES
// ========================================================================
if (!isset($_SESSION['nama']) && !isset($_SESSION['level'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_POST['masuk'])) {
    header("Location: menu-utama.php");
    exit;
}

// ========================================================================
// AMBIL DATA DARI FORM
// ========================================================================
$username        = $_SESSION['username'];
$subkriteria_ids = isset($_POST['subkriteria']) ? $_POST['subkriteria'] : [];

// ========================================================================
// VALIDASI 1: Cek apakah semua kriteria sudah diisi
// ========================================================================
$jumlah_kriteria = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kriteria"));

if (empty($subkriteria_ids) || count($subkriteria_ids) != $jumlah_kriteria) {
    header("Location: menu-utama.php?validasi=error");
    exit;
}

// ========================================================================
// VALIDASI 2: Cek apakah semua alternatif sudah memiliki data lengkap
// ========================================================================
$query_va              = mysqli_query($koneksi, "SELECT * FROM alternatif");
$cek                   = 0;
$alternatif_bermasalah = [];

while ($baris = mysqli_fetch_array($query_va)) {
    $id_alternatif   = $baris['id_alternatif'];
    $nama_alternatif = $baris['nama_alternatif'];

    $stmt = mysqli_prepare($koneksi, "SELECT * FROM matriks WHERE id_alternatif = ?");
    mysqli_stmt_bind_param($stmt, 's', $id_alternatif);
    mysqli_stmt_execute($stmt);
    $result      = mysqli_stmt_get_result($stmt);
    $jumlah_data = mysqli_num_rows($result);
    mysqli_stmt_close($stmt);

    if ($jumlah_data < $jumlah_kriteria) {
        $cek++;
        $alternatif_bermasalah[] = "$nama_alternatif (memiliki $jumlah_data dari $jumlah_kriteria kriteria)";
    }
}

if ($cek > 0) {
    $_SESSION['error_detail'] = "Varietas yang belum lengkap: " . implode(", ", $alternatif_bermasalah);
    header("Location: menu-utama.php?validasi=minus");
    exit;
}

// Hapus data peringkat lama
mysqli_query($koneksi, "DELETE FROM peringkat");

// ========================================================================
// AMBIL KONDISI LAHAN PETANI (INPUT USER)
// ========================================================================
$kondisi_lahan = [];

foreach ($subkriteria_ids as $id_krit => $id_subkrit) {
    $stmt = mysqli_prepare($koneksi, "SELECT nilai_subkriteria FROM subkriteria WHERE id_subkriteria = ?");
    mysqli_stmt_bind_param($stmt, 's', $id_subkrit);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $data   = mysqli_fetch_array($result);
    mysqli_stmt_close($stmt);

    $kondisi_lahan[$id_krit] = $data ? floatval($data['nilai_subkriteria']) : 0;
}

// ========================================================================
// AMBIL DATA ALTERNATIF
// ========================================================================
$query_all_alternatif = mysqli_query($koneksi, "
    SELECT DISTINCT id_alternatif FROM matriks 
    WHERE id_alternatif != 0 
    ORDER BY id_alternatif
");

$alternatif_list = [];
while ($row = mysqli_fetch_array($query_all_alternatif)) {
    $alternatif_list[] = $row['id_alternatif'];
}

if (empty($alternatif_list)) {
    header("Location: menu-utama.php?validasi=error");
    exit;
}

// ========================================================================
// AMBIL DATA KRITERIA
// ========================================================================
$query_kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
$kriteria_data  = [];
$kriteria_types = [];

while ($k = mysqli_fetch_array($query_kriteria)) {
    $kriteria_data[]                   = $k;
    $kriteria_types[$k['id_kriteria']] = $k['jenis_kriteria'];
}

$n              = count($kriteria_data);
$m              = count($alternatif_list);
$alternatif_ids = $alternatif_list;

// ========================================================================
// TABEL RANDOM INDEX (RI) SAATY
// ========================================================================
$RI_TABLE = [
    1  => 0.00,
    2  => 0.00,
    3  => 0.58,
    4  => 0.90,
    5  => 1.12,
    6  => 1.24,
    7  => 1.32,
    8  => 1.41,
    9  => 1.45,
    10 => 1.49,
];

// ========================================================================
// AHP — LANGKAH 1: Susun Matriks Perbandingan dari Database
// ========================================================================
$matriks_perbandingan = [];
foreach ($kriteria_data as $idx_i => $k_i) {
    $id_i = $k_i['id_kriteria'];
    foreach ($kriteria_data as $idx_j => $k_j) {
        $id_j = $k_j['id_kriteria'];

        $stmt = mysqli_prepare($koneksi, "
            SELECT nilai FROM matriks_perbandingan
            WHERE id_kriteria_i = ? AND id_kriteria_j = ?
        ");
        mysqli_stmt_bind_param($stmt, 'ss', $id_i, $id_j);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_array($result);
        mysqli_stmt_close($stmt);

        $matriks_perbandingan[$idx_i][$idx_j] = $row ? floatval($row['nilai']) : 1.0;
    }
}

// ========================================================================
// AHP — LANGKAH 2: Jumlah Setiap Kolom
// ========================================================================
$jumlah_kolom = array_fill(0, $n, 0.0);
for ($j = 0; $j < $n; $j++) {
    for ($i = 0; $i < $n; $i++) {
        $jumlah_kolom[$j] += $matriks_perbandingan[$i][$j];
    }
}

// ========================================================================
// AHP — LANGKAH 3: Normalisasi Matriks
// ========================================================================
$matriks_norm = [];
for ($i = 0; $i < $n; $i++) {
    for ($j = 0; $j < $n; $j++) {
        $matriks_norm[$i][$j] = ($jumlah_kolom[$j] > 0)
            ? $matriks_perbandingan[$i][$j] / $jumlah_kolom[$j]
            : 0;
    }
}

// ========================================================================
// AHP — LANGKAH 4: Bobot Prioritas = Rata-rata Baris
// ========================================================================
$bobot = [];
for ($i = 0; $i < $n; $i++) {
    $bobot[$i] = array_sum($matriks_norm[$i]) / $n;
}

// ========================================================================
// AHP — LANGKAH 5: Lambda Maksimum
// ========================================================================
$lambda_maks = 0;
for ($j = 0; $j < $n; $j++) {
    $lambda_maks += $jumlah_kolom[$j] * $bobot[$j];
}

// ========================================================================
// AHP — LANGKAH 6: CI, RI, CR
// ========================================================================
$CI = ($n > 1) ? ($lambda_maks - $n) / ($n - 1) : 0;
$RI = isset($RI_TABLE[$n]) ? $RI_TABLE[$n] : 1.49;
$CR = ($RI > 0) ? $CI / $RI : 0;

if ($CR < 0.1) {
    $status_konsistensi = 'Konsisten';
} elseif ($CR < 0.2) {
    $status_konsistensi = 'Cukup Konsisten';
} else {
    $status_konsistensi = 'Tidak Konsisten — Perlu Direvisi';
}

// ========================================================================
// VALIDASI 3: Guard CR — hentikan proses jika matriks tidak konsisten
// Bobot AHP tidak valid jika CR >= 0.1, sehingga hasil TOPSIS pun
// tidak dapat dipercaya. Minta admin untuk merevisi matriks perbandingan.
// ========================================================================
if ($CR >= 0.1) {
    $_SESSION['error_detail'] = "Matriks perbandingan AHP tidak konsisten "
        . "(CR = " . round($CR * 100, 2) . "%). "
        . "Harap revisi matriks perbandingan kriteria sebelum melanjutkan.";
    header("Location: menu-utama.php?validasi=cr_error");
    exit;
}

// ========================================================================
// TOPSIS — LANGKAH 1: Susun Matriks Keputusan dari Database
// ========================================================================
$matriks = [];
foreach ($alternatif_list as $idx_i => $id_alt) {
    foreach ($kriteria_data as $idx_j => $k) {
        $id_krit = $k['id_kriteria'];

        $stmt = mysqli_prepare($koneksi, "
            SELECT s.nilai_subkriteria
            FROM matriks m
            JOIN subkriteria s ON m.id_subkriteria = s.id_subkriteria
            WHERE m.id_alternatif = ? AND m.id_kriteria = ?
        ");
        mysqli_stmt_bind_param($stmt, 'ss', $id_alt, $id_krit);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);
        $row    = mysqli_fetch_array($result);
        mysqli_stmt_close($stmt);

        $matriks[$idx_i][$idx_j] = $row ? floatval($row['nilai_subkriteria']) : 0;
    }
}

// ========================================================================
// Siapkan nilai mentah petani per index kolom
// ========================================================================
$nilai_petani_raw = [];
foreach ($kriteria_data as $idx_j => $k) {
    $id_krit                  = $k['id_kriteria'];
    $nilai_petani_raw[$idx_j] = isset($kondisi_lahan[$id_krit])
        ? floatval($kondisi_lahan[$id_krit])
        : 0;
}

// ========================================================================
// TOPSIS — LANGKAH 2: Normalisasi Vektor
//
// Denominator dihitung HANYA dari alternatif (petani TIDAK ikut).
// Tujuan: normalisasi stabil — tidak berubah setiap kali petani
// mengganti input kondisi lahan.
// ========================================================================
$R            = [];
$denominators = [];

for ($j = 0; $j < $n; $j++) {
    $sum_sq = 0;
    for ($i = 0; $i < $m; $i++) {
        $sum_sq += $matriks[$i][$j] * $matriks[$i][$j];
    }
    $denom            = sqrt($sum_sq);
    $denominators[$j] = $denom;

    for ($i = 0; $i < $m; $i++) {
        $R[$i][$j] = ($denom == 0) ? 0 : $matriks[$i][$j] / $denom;
    }
}

// ========================================================================
// TOPSIS — LANGKAH 3: Matriks Terbobot Alternatif
// Rumus: y_ij = w_j * r_ij
// ========================================================================
$Y = [];
for ($i = 0; $i < $m; $i++) {
    for ($j = 0; $j < $n; $j++) {
        $Y[$i][$j] = $R[$i][$j] * $bobot[$j];
    }
}

// ========================================================================
// TOPSIS — LANGKAH 4: Normalisasi & Pembobotan Input Petani
// ========================================================================
$r_petani = [];
$y_petani = [];

for ($j = 0; $j < $n; $j++) {
    $r_petani[$j] = ($denominators[$j] == 0) ? 0 : $nilai_petani_raw[$j] / $denominators[$j];
    $y_petani[$j] = $r_petani[$j] * $bobot[$j];
}

// ========================================================================
// TOPSIS — LANGKAH 5: Solusi Ideal Positif (A+) dan Negatif (A-)
//
// PENDEKATAN: Modified TOPSIS — Kesesuaian terhadap Kondisi Petani
//
// A+ = kondisi petani terbobot
//      → Varietas terbaik = yang paling mirip dengan kondisi lahan petani
//      → Setiap input petani berbeda, ranking bisa berbeda (dinamis)
//
// A- = nilai terburuk dari alternatif SAJA (petani tidak ikut)
//      - Benefit: nilai terkecil dari kolom alternatif
//      - Cost   : nilai terbesar dari kolom alternatif
//      → A- stabil, tidak dipengaruhi input petani
// ========================================================================
$y_plus  = $y_petani; // A+ = kondisi petani sebagai acuan ideal

$y_minus = [];
for ($j = 0; $j < $n; $j++) {
    $id_krit = $kriteria_data[$j]['id_kriteria'];
    $jenis   = $kriteria_types[$id_krit];

    // Hanya dari kolom alternatif
    $col = [];
    for ($i = 0; $i < $m; $i++) {
        $col[] = $Y[$i][$j];
    }

    if ($jenis == 'Benefit') {
        $y_minus[$j] = min($col); // Benefit: terkecil = terburuk
    } else {
        $y_minus[$j] = max($col); // Cost: terbesar = terburuk
    }
}

// ========================================================================
// TOPSIS — LANGKAH 6: Jarak Separasi ke A+ dan A-
// ========================================================================
$D_plus  = [];
$D_minus = [];

for ($i = 0; $i < $m; $i++) {
    $sum_plus  = 0;
    $sum_minus = 0;
    for ($j = 0; $j < $n; $j++) {
        $sum_plus  += pow($y_plus[$j]  - $Y[$i][$j], 2);
        $sum_minus += pow($y_minus[$j] - $Y[$i][$j], 2);
    }
    $D_plus[$i]  = sqrt($sum_plus);
    $D_minus[$i] = sqrt($sum_minus);
}

// ========================================================================
// TOPSIS — LANGKAH 7: Nilai Preferensi (Closeness Coefficient)
// Rumus: C_i = D-_i / (D+_i + D-_i)
// Range: 0 – 1. Semakin tinggi = semakin dekat ke kondisi petani
// ========================================================================
$scores = [];
for ($i = 0; $i < $m; $i++) {
    $denom      = $D_plus[$i] + $D_minus[$i];
    $scores[$i] = ($denom == 0) ? 0 : $D_minus[$i] / $denom;
}

// ========================================================================
// LANGKAH 8: Ranking descending (skor tertinggi = ranking 1)
// ========================================================================
$ranking = array_keys($scores);
usort($ranking, fn($a, $b) => $scores[$b] <=> $scores[$a]);

$D_ke_petani = [];
for ($i = 0; $i < $m; $i++) {
    $sum = 0;
    for ($j = 0; $j < $n; $j++) {
        $sum += pow($Y[$i][$j] - $y_petani[$j], 2);
    }
    $D_ke_petani[$i] = sqrt($sum);
}

// ========================================================================
// INFORMASI TAMBAHAN: Posisi Petani (C_petani)
// Menghitung seberapa dekat kondisi petani dengan solusi ideal alternatif
// ========================================================================
$D_plus_petani  = 0;
$D_minus_petani = 0;

for ($j = 0; $j < $n; $j++) {
    $D_plus_petani  += pow($y_plus[$j]  - $y_petani[$j], 2);
    $D_minus_petani += pow($y_minus[$j] - $y_petani[$j], 2);
}

$D_plus_petani  = sqrt($D_plus_petani);   // = 0, karena A+ = petani
$D_minus_petani = sqrt($D_minus_petani);

$denom_petani = $D_plus_petani + $D_minus_petani;
$C_petani     = ($denom_petani == 0) ? 1.0 : $D_minus_petani / $denom_petani;
// Catatan: C_petani selalu 1.0 karena A+ = petani itu sendiri (D+ = 0)

// ========================================================================
// AMBIL NAMA ALTERNATIF DAN KRITERIA
// ========================================================================
$alternatif_names = [];
foreach ($alternatif_ids as $id_alt) {
    $stmt = mysqli_prepare($koneksi, "SELECT nama_alternatif FROM alternatif WHERE id_alternatif = ?");
    mysqli_stmt_bind_param($stmt, 's', $id_alt);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $row    = mysqli_fetch_array($result);
    mysqli_stmt_close($stmt);

    if ($row) {
        $alternatif_names[$id_alt] = $row['nama_alternatif'];
    }
}

$kriteria_names = [];
foreach ($kriteria_data as $krit) {
    $kriteria_names[$krit['id_kriteria']] = $krit['nama_kriteria'];
}

// ========================================================================
// SIMPAN DATA HASIL KE SESSION
// ========================================================================
$_SESSION['data_hasil'] = [
    // Data referensi
    'kondisi_lahan'        => $kondisi_lahan,
    'alternatif_list'      => $alternatif_list,
    'alternatif_ids'       => $alternatif_ids,
    'alternatif_names'     => $alternatif_names,
    'kriteria_data'        => $kriteria_data,
    'kriteria'             => $kriteria_data,   // alias untuk cetak-hasil.php
    'kriteria_names'       => $kriteria_names,
    'kriteria_types'       => $kriteria_types,
    'n'                    => $n,
    'm'                    => $m,

    // AHP
    'matriks_perbandingan' => $matriks_perbandingan,
    'jumlah_kolom'         => $jumlah_kolom,
    'matriks_norm'         => $matriks_norm,
    'bobot'                => $bobot,
    'lambda_maks'          => round($lambda_maks, 6),
    'CI'                   => round($CI, 6),
    'RI'                   => $RI,
    'CR'                   => round($CR, 6),
    'CR_persen'            => round($CR * 100, 4),
    'konsisten'            => ($CR < 0.1),
    'status_konsistensi'   => $status_konsistensi,

    // TOPSIS — matriks
    'matriks'              => $matriks,
    'R'                    => $R,
    'Y'                    => $Y,
    'denominators'         => $denominators,

    // TOPSIS — petani
    'nilai_petani_raw'     => $nilai_petani_raw,
    'r_petani'             => $r_petani,
    'y_petani'             => $y_petani,
    'D_plus_petani'        => round($D_plus_petani, 6),
    'D_minus_petani'       => round($D_minus_petani, 6),
    'C_petani'             => round($C_petani, 6),

    // TOPSIS — hasil
    'y_plus'               => $y_plus,
    'y_minus'              => $y_minus,
    'D_plus'               => $D_plus,
    'D_minus'              => $D_minus,
    'D_ke_petani'          => $D_ke_petani,  // info tambahan
    'scores'               => $scores,
    'ranking'              => $ranking,
];

$success   = true;
$error_msg = "";

for ($i = 0; $i < $m; $i++) {
    $id_alt = $alternatif_ids[$i];
    $nilai  = round($scores[$i], 6);

    $stmt = mysqli_prepare($koneksi, "
        INSERT INTO peringkat (id_alternatif, nilai_peringkat) 
        VALUES (?, ?)
    ");
    mysqli_stmt_bind_param($stmt, 'sd', $id_alt, $nilai);
    $exec = mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);

    if (!$exec) {
        $success   = false;
        $error_msg = mysqli_error($koneksi);
        break;
    }
}

if ($success) {
    header("Location: data-hasil.php?validasi=sukses");
} else {
    $_SESSION['error_detail'] = "Error database: " . $error_msg;
    header("Location: data-hasil.php?validasi=error");
}
exit;
?>