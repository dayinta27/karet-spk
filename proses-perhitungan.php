<?php
session_start();
include 'koneksi.php';

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

// Validasi: Cek apakah semua kriteria sudah diisi
$jumlah_kriteria = mysqli_num_rows(mysqli_query($koneksi, "SELECT * FROM kriteria"));

if (empty($subkriteria_ids) || count($subkriteria_ids) != $jumlah_kriteria) {
    header("Location: menu-utama.php?validasi=error");
    exit;
}

// Validasi: Cek apakah semua alternatif sudah memiliki data lengkap
$query_va              = mysqli_query($koneksi, "SELECT * FROM alternatif");
$cek                   = 0;
$alternatif_bermasalah = [];

while ($baris = mysqli_fetch_array($query_va)) {
    $id_alternatif   = $baris['id_alternatif'];
    $nama_alternatif = $baris['nama_alternatif'];
    $query           = mysqli_query($koneksi, "SELECT * FROM matriks WHERE id_alternatif = '$id_alternatif'");
    $jumlah_data     = mysqli_num_rows($query);

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
    $query_nilai_lahan = mysqli_query($koneksi, "
        SELECT nilai_subkriteria 
        FROM subkriteria 
        WHERE id_subkriteria = '$id_subkrit'
    ");

    if ($data = mysqli_fetch_array($query_nilai_lahan)) {
        $kondisi_lahan[$id_krit] = floatval($data['nilai_subkriteria']);
    } else {
        $kondisi_lahan[$id_krit] = 0;
    }
}

// ========================================================================
// AMBIL DATA ALTERNATIF
// ========================================================================
$query_all_alternatif = mysqli_query($koneksi, "SELECT DISTINCT id_alternatif FROM matriks WHERE id_alternatif != 0 ORDER BY id_alternatif");
$alternatif_list      = [];
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

// Jumlah kriteria dan alternatif
$n              = count($kriteria_data);
$m              = count($alternatif_list);
$alternatif_ids = $alternatif_list;

// Tabel Random Index (RI) Saaty
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
// SUSUN MATRIKS PERBANDINGAN AHP DARI DATABASE
// ========================================================================
$matriks_perbandingan = [];
foreach ($kriteria_data as $idx_i => $k_i) {
    $id_i = $k_i['id_kriteria'];
    foreach ($kriteria_data as $idx_j => $k_j) {
        $id_j = $k_j['id_kriteria'];
        $q    = mysqli_query($koneksi, "
            SELECT nilai FROM matriks_perbandingan
            WHERE id_kriteria_i = '$id_i' AND id_kriteria_j = '$id_j'
        ");
        $row  = mysqli_fetch_array($q);
        $matriks_perbandingan[$idx_i][$idx_j] = $row ? floatval($row['nilai']) : 1.0;
    }
}

// ========================================================================
// HITUNG AHP
// ========================================================================

// Langkah 1: Jumlah setiap kolom
$jumlah_kolom = array_fill(0, $n, 0.0);
for ($j = 0; $j < $n; $j++) {
    for ($i = 0; $i < $n; $i++) {
        $jumlah_kolom[$j] += $matriks_perbandingan[$i][$j];
    }
}

// Langkah 2: Normalisasi matriks
$matriks_norm = [];
for ($i = 0; $i < $n; $i++) {
    for ($j = 0; $j < $n; $j++) {
        $matriks_norm[$i][$j] = ($jumlah_kolom[$j] > 0)
            ? $matriks_perbandingan[$i][$j] / $jumlah_kolom[$j]
            : 0;
    }
}

// Langkah 3: Bobot prioritas = rata-rata baris
$bobot = [];
for ($i = 0; $i < $n; $i++) {
    $bobot[$i] = array_sum($matriks_norm[$i]) / $n;
}

// Langkah 4: Lambda maksimum
$lambda_maks = 0;
for ($j = 0; $j < $n; $j++) {
    $lambda_maks += $jumlah_kolom[$j] * $bobot[$j];
}

// Langkah 5: CI
$CI = ($n > 1) ? ($lambda_maks - $n) / ($n - 1) : 0;

// Langkah 6: RI
$RI = isset($RI_TABLE[$n]) ? $RI_TABLE[$n] : 1.49;

// Langkah 7: CR
$CR = ($RI > 0) ? $CI / $RI : 0;

// Status konsistensi
if ($CR < 0.1) {
    $status_konsistensi = 'Konsisten';
} elseif ($CR < 0.2) {
    $status_konsistensi = 'Cukup Konsisten';
} else {
    $status_konsistensi = 'Tidak Konsisten — Perlu Direvisi';
}

// ========================================================================
// SUSUN MATRIKS KEPUTUSAN TOPSIS DARI TABEL matriks
// ========================================================================
$matriks = [];
foreach ($alternatif_list as $idx_i => $id_alt) {
    foreach ($kriteria_data as $idx_j => $k) {
        $id_krit = $k['id_kriteria'];
        $q       = mysqli_query($koneksi, "
            SELECT s.nilai_subkriteria
            FROM matriks m
            JOIN subkriteria s ON m.id_subkriteria = s.id_subkriteria
            WHERE m.id_alternatif = '$id_alt' AND m.id_kriteria = '$id_krit'
        ");
        $row                    = mysqli_fetch_array($q);
        $matriks[$idx_i][$idx_j] = $row ? floatval($row['nilai_subkriteria']) : 0;
    }
}

// ========================================================================
// METODE TOPSIS
// ========================================================================

// Langkah 1: Normalisasi Vector
$R            = [];
$denominators = [];

for ($j = 0; $j < $n; $j++) {
    $col_values = array_column($matriks, $j);
    $sum_sq     = 0;
    foreach ($col_values as $val) {
        $sum_sq += $val * $val;
    }
    $denom            = sqrt($sum_sq);
    $denominators[$j] = $denom;

    for ($i = 0; $i < $m; $i++) {
        $R[$i][$j] = ($denom == 0) ? 0 : $matriks[$i][$j] / $denom;
    }
}

// Langkah 2: Matriks Terbobot
$Y = [];
for ($i = 0; $i < $m; $i++) {
    for ($j = 0; $j < $n; $j++) {
        $Y[$i][$j] = $R[$i][$j] * $bobot[$j];
    }
}

// ========================================================================
// NORMALISASI INPUT PETANI
// ========================================================================
$r_petani = [];
$y_petani = [];

for ($j = 0; $j < $n; $j++) {
    $id_krit      = $kriteria_data[$j]['id_kriteria'];
    $nilai_petani = isset($kondisi_lahan[$id_krit]) ? $kondisi_lahan[$id_krit] : 0;
    $denom        = $denominators[$j];

    $r_petani[$j] = ($denom == 0) ? 0 : $nilai_petani / $denom;
    $y_petani[$j] = $r_petani[$j] * $bobot[$j];
}

// ========================================================================
// Langkah 3: Solusi Ideal
// ========================================================================

// A+ = Input petani terbobot
$y_plus = $y_petani;

// A- = Nilai ekstrem berdasarkan benefit/cost
$y_minus = [];
for ($j = 0; $j < $n; $j++) {
    $col     = array_column($Y, $j);
    $id_krit = $kriteria_data[$j]['id_kriteria'];
    $jenis   = $kriteria_types[$id_krit];

    $y_minus[$j] = ($jenis == 'Benefit') ? min($col) : max($col);
}

// Langkah 4: Hitung Separasi
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

// Langkah 5: Hitung Nilai Preferensi
$scores = [];
for ($i = 0; $i < $m; $i++) {
    $denom      = $D_plus[$i] + $D_minus[$i];
    $scores[$i] = ($denom == 0) ? 0 : $D_minus[$i] / $denom;
}

// ========================================================================
// AMBIL NAMA ALTERNATIF DAN KRITERIA
// ========================================================================
$alternatif_names = [];
foreach ($alternatif_ids as $id_alt) {
    $q = mysqli_query($koneksi, "SELECT nama_alternatif FROM alternatif WHERE id_alternatif = '$id_alt'");
    if ($row = mysqli_fetch_array($q)) {
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
    'kondisi_lahan'        => $kondisi_lahan,
    'alternatif_list'      => $alternatif_list,
    'alternatif_ids'       => $alternatif_ids,
    'alternatif_names'     => $alternatif_names,
    'kriteria_data'        => $kriteria_data,
    'kriteria_names'       => $kriteria_names,
    'n'                    => $n,
    'm'                    => $m,
    'matriks_perbandingan' => $matriks_perbandingan,
    'matriks'              => $matriks,
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
    'R'                    => $R,
    'Y'                    => $Y,
    'denominators'         => $denominators,
    'r_petani'             => $r_petani,
    'y_petani'             => $y_petani,
    'y_plus'               => $y_plus,
    'y_minus'              => $y_minus,
    'D_plus'               => $D_plus,
    'D_minus'              => $D_minus,
    'scores'               => $scores,
];

// ========================================================================
// SIMPAN HASIL KE DATABASE
// ========================================================================
$success   = true;
$error_msg = "";

for ($i = 0; $i < $m; $i++) {
    $id_alt = $alternatif_ids[$i];
    $nilai  = round($scores[$i], 6);

    $stmt = mysqli_prepare($koneksi, "
        INSERT INTO peringkat (id_alternatif, nilai_peringkat) 
        VALUES (?, ?)
    ");
    mysqli_stmt_bind_param($stmt, 'id', $id_alt, $nilai);
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