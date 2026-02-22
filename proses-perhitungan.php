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

// Ambil data kriteria beserta jenisnya
$query_kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
$kriteria_data  = [];
$kriteria_types = [];
while ($k = mysqli_fetch_array($query_kriteria)) {
    $kriteria_data[]                   = $k;
    $kriteria_types[$k['id_kriteria']] = $k['jenis_kriteria'];
}

// ========================================================================
// BUAT MATRIKS KEPUTUSAN
// ========================================================================
$matriks        = [];
$alternatif_ids = [];

foreach ($alternatif_list as $id_alt) {
    $row_data = [];

    foreach ($kriteria_data as $krit) {
        $id_krit         = $krit['id_kriteria'];
        $query_kebutuhan = mysqli_query($koneksi, "
            SELECT s.nilai_subkriteria 
            FROM matriks m 
            JOIN subkriteria s ON m.id_subkriteria = s.id_subkriteria 
            WHERE m.id_alternatif = '$id_alt' AND m.id_kriteria = '$id_krit'
        ");

        if ($data_kebutuhan = mysqli_fetch_array($query_kebutuhan)) {
            $row_data[] = floatval($data_kebutuhan['nilai_subkriteria']);
        } else {
            $row_data[] = 1;
        }
    }

    $matriks[]        = $row_data;
    $alternatif_ids[] = $id_alt;
}

// ========================================================================
// METODE CRITIC (Pembobotan Kriteria)
// ========================================================================
$matriks_critic = $matriks;
$m              = count($matriks_critic);
$n              = count($matriks_critic[0]);

// Langkah 1: Normalisasi CRITIC (Min-Max)
$norm_critic = [];
for ($j = 0; $j < $n; $j++) {
    $col_values = array_column($matriks_critic, $j);
    $min_val    = min($col_values);
    $max_val    = max($col_values);
    $range      = $max_val - $min_val;
    $id_krit    = $kriteria_data[$j]['id_kriteria'];
    $jenis      = $kriteria_types[$id_krit];

    for ($i = 0; $i < $m; $i++) {
        if ($range == 0) {
            $norm_critic[$i][$j] = 1.0;
        } else {
            if ($jenis == 'Benefit') {
                $norm_critic[$i][$j] = ($matriks_critic[$i][$j] - $min_val) / $range;
            } else {
                $norm_critic[$i][$j] = ($max_val - $matriks_critic[$i][$j]) / $range;
            }
        }
    }
}

// Langkah 2: Hitung Standar Deviasi
$std_dev = [];
for ($j = 0; $j < $n; $j++) {
    $col      = array_column($norm_critic, $j);
    $mean     = array_sum($col) / count($col);
    $variance = 0;
    foreach ($col as $val) {
        $variance += pow($val - $mean, 2);
    }
    $std_dev[$j] = sqrt($variance / max(1, (count($col) - 1)));
}

// Langkah 3: Hitung Korelasi
$corr_matrix = [];
for ($j1 = 0; $j1 < $n; $j1++) {
    for ($j2 = 0; $j2 < $n; $j2++) {
        if ($j1 == $j2) {
            $corr_matrix[$j1][$j2] = 1.0;
        } else {
            $col1  = array_column($norm_critic, $j1);
            $col2  = array_column($norm_critic, $j2);
            $mean1 = array_sum($col1) / count($col1);
            $mean2 = array_sum($col2) / count($col2);

            $numerator = 0;
            $sum_sq1   = 0;
            $sum_sq2   = 0;

            for ($i = 0; $i < count($col1); $i++) {
                $diff1      = $col1[$i] - $mean1;
                $diff2      = $col2[$i] - $mean2;
                $numerator += $diff1 * $diff2;
                $sum_sq1   += $diff1 * $diff1;
                $sum_sq2   += $diff2 * $diff2;
            }

            $denominator           = sqrt($sum_sq1 * $sum_sq2);
            $corr_matrix[$j1][$j2] = ($denominator == 0) ? 0 : $numerator / $denominator;
        }
    }
}

// Langkah 4: Hitung Informasi Kriteria (Cj)
$Cj = [];
for ($j = 0; $j < $n; $j++) {
    $sum_one_minus_r = 0;
    for ($k = 0; $k < $n; $k++) {
        if ($j != $k) {
            $sum_one_minus_r += (1 - $corr_matrix[$j][$k]);
        }
    }
    $Cj[$j] = $std_dev[$j] * $sum_one_minus_r;
}

// Langkah 5: Hitung Bobot
$sum_Cj  = array_sum($Cj);
$weights = [];
for ($j = 0; $j < $n; $j++) {
    $weights[$j] = ($sum_Cj == 0) ? (1.0 / $n) : ($Cj[$j] / $sum_Cj);
}

// ========================================================================
// METODE TOPSIS (Perangkingan Alternatif)
// ========================================================================

// Langkah 1: Normalisasi TOPSIS (Vector Normalization)
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
        $Y[$i][$j] = $R[$i][$j] * $weights[$j];
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
    $y_petani[$j] = $r_petani[$j] * $weights[$j];
}

// ========================================================================
// Langkah 3: Solusi Ideal
// ========================================================================

// A⁺ = Input petani terbobot
$y_plus = $y_petani;

// A⁻ = Nilai ekstrem berdasarkan benefit/cost
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
    'kondisi_lahan'     => $kondisi_lahan,
    'alternatif_list'   => $alternatif_list,
    'alternatif_ids'    => $alternatif_ids,
    'alternatif_names'  => $alternatif_names,
    'kriteria_data'     => $kriteria_data,
    'kriteria_names'    => $kriteria_names,
    'matriks_keputusan' => $matriks,
    'matriks_critic'    => $matriks_critic,
    'norm_critic'       => $norm_critic,
    'std_dev'           => $std_dev,
    'corr_matrix'       => $corr_matrix,
    'Cj'                => $Cj,
    'weights'           => $weights,
    'R'                 => $R,
    'Y'                 => $Y,
    'denominators'      => $denominators,
    'r_petani'          => $r_petani,
    'y_petani'          => $y_petani,
    'y_plus'            => $y_plus,
    'y_minus'           => $y_minus,
    'D_plus'            => $D_plus,
    'D_minus'           => $D_minus,
    'scores'            => $scores
];

// ========================================================================
// SIMPAN HASIL KE DATABASE (TANPA USERNAME)
// ========================================================================
$success   = true;
$error_msg = "";

for ($i = 0; $i < $m; $i++) {
    $id_alt = $alternatif_ids[$i];
    $nilai  = round($scores[$i], 6);

    $insert = mysqli_query($koneksi, "
        INSERT INTO peringkat (id_alternatif, nilai_peringkat) 
        VALUES ('$id_alt', '$nilai')
    ");

    if (!$insert) {
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