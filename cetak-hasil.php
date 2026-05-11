<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['nama']) && !isset($_SESSION['level'])) {
    header("Location: index.php");
    exit;
}

if (!isset($_GET['validasi'])) {
    header("Location: menu-utama.php");
    exit;
}

if (!isset($_SESSION['data_hasil'])) {
    header("Location: menu-utama.php");
    exit;
}

$data_hasil       = $_SESSION['data_hasil'];
$alternatif_ids   = $data_hasil['alternatif_ids'];
$alternatif_names = $data_hasil['alternatif_names'];
$kriteria_data    = $data_hasil['kriteria_data'];
$kriteria_names   = $data_hasil['kriteria_names'];
$kriteria         = $data_hasil['kriteria'];
$n                = $data_hasil['n'];
$m                = $data_hasil['m'];
$matriks          = $data_hasil['matriks'];
$jumlah_kolom     = $data_hasil['jumlah_kolom'];
$matriks_perbandingan = $data_hasil['matriks_perbandingan'];
$matriks_norm     = $data_hasil['matriks_norm'];
$bobot            = $data_hasil['bobot'];
$lambda_maks      = $data_hasil['lambda_maks'];
$CI               = $data_hasil['CI'];
$RI               = $data_hasil['RI'];
$CR               = $data_hasil['CR'];
$CR_persen        = $data_hasil['CR_persen'];
$konsisten        = $data_hasil['konsisten'];
$status_konsistensi = $data_hasil['status_konsistensi'];
$R                = $data_hasil['R'];
$Y                = $data_hasil['Y'];
$denominators     = $data_hasil['denominators'];
$r_petani         = $data_hasil['r_petani'];
$y_petani         = $data_hasil['y_petani'];
$y_plus           = $data_hasil['y_plus'];
$y_minus          = $data_hasil['y_minus'];
$D_plus           = $data_hasil['D_plus'];
$D_minus          = $data_hasil['D_minus'];
$scores           = $data_hasil['scores'];
$kondisi_lahan    = $data_hasil['kondisi_lahan'];

$m = count($alternatif_ids);
$n = count($kriteria_data);

$cek_data = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peringkat");
$row_cek  = mysqli_fetch_array($cek_data);
if ($row_cek['total'] == 0) {
    header("Location: menu-utama.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>Cetak Hasil - SPK Varietas Karet</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            padding: 20px;
        }
        .print-container {
            max-width: 960px;
            margin: 0 auto;
            background: white;
            padding: 40px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .header-print {
            text-align: center;
            border-bottom: 3px solid #0d6efd;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }
        .header-print h3 { color: #0d6efd; font-weight: 700; margin-bottom: 5px; }
        .header-print p  { color: #666; margin: 3px 0; font-size: 14px; }
        .info-box {
            background: #f8f9fa;
            border-left: 4px solid #0d6efd;
            padding: 12px 20px;
            border-radius: 5px;
            margin-bottom: 25px;
            font-size: 14px;
        }
        .info-box p { margin: 3px 0; color: #555; }
        .rekomendasi-box {
            background: linear-gradient(135deg, #198754, #20c997);
            color: white;
            padding: 20px 25px;
            border-radius: 10px;
            margin-bottom: 25px;
        }
        .rekomendasi-box h5 { margin-bottom: 8px; font-size: 13px; opacity: 0.9; }
        .rekomendasi-box h3 { font-weight: 700; margin-bottom: 5px; }
        .rekomendasi-box p  { margin: 0; font-size: 14px; opacity: 0.9; }

        /* Section */
        .section { margin-bottom: 35px; }
        .section h2 {
            font-size: 17px;
            font-weight: 700;
            color: #0d6efd;
            margin: 25px 0 8px 0;
            padding: 10px 15px;
            background: #e8f0fe;
            border-left: 5px solid #0d6efd;
            border-radius: 4px;
        }
        .section h3 {
            font-size: 14px;
            font-weight: 600;
            color: #333;
            margin: 18px 0 6px 0;
            padding-bottom: 4px;
            border-bottom: 1px dashed #ccc;
        }
        .info-text {
            font-size: 13px;
            color: #666;
            margin-bottom: 8px;
            font-style: italic;
        }

        /* Tabel */
        .table-container { overflow-x: auto; margin-bottom: 12px; }
        table { width: 100%; border-collapse: collapse; font-size: 13px; margin-bottom: 8px; }
        table thead th {
            background: #0d6efd;
            color: white;
            padding: 9px 11px;
            text-align: center;
            font-weight: 600;
        }
        table tbody td { padding: 7px 11px; border: 1px solid #dee2e6; text-align: center; }
        table tbody tr:nth-child(even) { background: #f8f9fa; }

        /* Ranking rows */
        .rank-1 { background: #fff9e6 !important; }
        .rank-2 { background: #f5f5f5 !important; }
        .rank-3 { background: #fff3f3 !important; }

        .rank-badge {
            display: inline-block;
            width: 28px; height: 28px;
            border-radius: 50%;
            text-align: center;
            line-height: 28px;
            font-weight: 700;
            font-size: 12px;
        }
        .badge-1 { background: #ffc107; color: #000; }
        .badge-2 { background: #adb5bd; color: #fff; }
        .badge-3 { background: #cd7f32; color: #fff; }
        .badge-other { background: #e9ecef; color: #333; }

        .footer-print {
            margin-top: 30px;
            padding-top: 15px;
            border-top: 1px solid #eee;
            display: flex;
            justify-content: space-between;
            font-size: 12px;
            color: #999;
        }
        .btn-print-area { text-align: center; margin-bottom: 25px; }
        .btn-cetak {
            background: #0d6efd; color: white; border: none;
            padding: 10px 30px; border-radius: 50px;
            font-size: 15px; font-weight: 600; cursor: pointer;
            margin: 5px; text-decoration: none; display: inline-block; transition: all 0.3s;
        }
        .btn-cetak:hover { background: #0a58ca; color: white; }
        .btn-kembali {
            background: transparent; color: #0d6efd;
            border: 2px solid #0d6efd; padding: 10px 30px;
            border-radius: 50px; font-size: 15px; font-weight: 600;
            cursor: pointer; margin: 5px; text-decoration: none;
            display: inline-block; transition: all 0.3s;
        }
        .btn-kembali:hover { background: #0d6efd; color: white; }

        @media print {
            .btn-print-area { display: none !important; }
            body { background: white; padding: 0; }
            .print-container { box-shadow: none; padding: 20px; }
            .section { page-break-inside: avoid; }
        }
    </style>
</head>

<body>
    <div class="btn-print-area">
        <button class="btn-cetak" onclick="window.print()">
            <i class="fas fa-print"></i> Cetak / Print
        </button>
        <a href="data-hasil.php?validasi=sukses" class="btn-kembali">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>

    <div class="print-container">

        <!-- HEADER -->
        <div class="header-print">
            <h3><i class="fas fa-leaf"></i> Sistem Pendukung Keputusan</h3>
            <h4>Rekomendasi Varietas Tanaman Karet</h4>
            <p>Balai Pengkajian Teknologi Pertanian</p>
            <p>Metode AHP (Pembobotan) + TOPSIS (Perangkingan)</p>
        </div>

        <div class="info-box">
            <p><strong>Tanggal Perhitungan :</strong> <?= date('d F Y, H:i'); ?> WIB</p>
            <p><strong>Pengguna           :</strong> <?= $_SESSION['nama']; ?></p>
            <p><strong>Metode             :</strong> AHP-TOPSIS</p>
        </div>

        <?php
        $query_rekom = mysqli_query($koneksi, "
            SELECT alternatif.nama_alternatif AS nama,
                   alternatif.kode_alternatif AS kode,
                   peringkat.nilai_peringkat  AS nilai
            FROM peringkat
            JOIN alternatif ON peringkat.id_alternatif = alternatif.id_alternatif
            ORDER BY peringkat.nilai_peringkat DESC
            LIMIT 1
        ");
        $data_rekom = mysqli_fetch_array($query_rekom);
        ?>

        <div class="rekomendasi-box">
            <h5><i class="fas fa-trophy"></i> REKOMENDASI VARIETAS TERBAIK</h5>
            <h3><?= $data_rekom['nama']; ?></h3>
            <p>
                Berdasarkan hasil perhitungan SPK dengan metode AHP-TOPSIS,
                <strong><?= $data_rekom['nama']; ?></strong> adalah varietas karet yang paling direkomendasikan
                dengan nilai preferensi <strong><?= number_format($data_rekom['nilai'], 6); ?></strong>.
            </p>
        </div>

        <!-- ====================================================== -->
        <!-- 1. KONDISI LAHAN PETANI                                 -->
        <!-- ====================================================== -->
        <div class="section">
            <h2>1. Kondisi Lahan Petani</h2>
            <p class="info-text">Data kondisi lahan yang diinputkan sebagai dasar rekomendasi</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="6%">No</th>
                            <th width="55%">Kriteria</th>
                            <th>Nilai Kondisi Lahan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $no = 1;
                        foreach ($kondisi_lahan as $id_krit => $nilai) {
                            $nama_krit = isset($kriteria_names[$id_krit]) ? $kriteria_names[$id_krit] : "Kriteria $id_krit";
                        ?>
                            <tr>
                                <td><?= $no++; ?></td>
                                <td style="text-align:left"><?= $nama_krit; ?></td>
                                <td><strong><?= $nilai; ?></strong></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- 2. MATRIKS KEPUTUSAN                                    -->
        <!-- ====================================================== -->
        <div class="section">
            <h2>2. Matriks Keputusan</h2>
            <p class="info-text">Nilai kebutuhan optimal setiap varietas untuk masing-masing kriteria</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Nama Varietas</th>
                            <?php foreach ($kriteria_data as $krit) { ?>
                                <th><?= $krit['kode_kriteria']; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < $m; $i++) { ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td style="text-align:left"><strong><?= $alternatif_names[$alternatif_ids[$i]]; ?></strong></td>
                                <?php for ($j = 0; $j < $n; $j++) { ?>
                                    <td><?= number_format($matriks[$i][$j], 0); ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- 3. METODE AHP                                          -->
        <!-- ====================================================== -->
        <div class="section">
            <h2>3. Metode AHP (Pembobotan Kriteria)</h2>

            <h3>Matriks Perbandingan Berpasangan</h3>
            <p class="info-text">Nilai perbandingan berpasangan antar kriteria yang diisi oleh pakar</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Kriteria</th>
                            <?php foreach ($kriteria_data as $krit) { ?>
                                <th><?= $krit['kode_kriteria']; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < $n; $i++) { ?>
                            <tr>
                                <td style="text-align:left"><strong><?= $kriteria_data[$i]['kode_kriteria']; ?></strong></td>
                                <?php for ($j = 0; $j < $n; $j++) { ?>
                                    <td><?= number_format($matriks_perbandingan[$i][$j], 3); ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <h3>Matriks Normalisasi</h3>
            <p class="info-text">Hasil normalisasi matriks perbandingan (dibagi jumlah kolom)</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th>Kriteria</th>
                            <?php foreach ($kriteria_data as $krit) { ?>
                                <th><?= $krit['kode_kriteria']; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < $n; $i++) { ?>
                            <tr>
                                <td style="text-align:left"><strong><?= $kriteria_data[$i]['kode_kriteria']; ?></strong></td>
                                <?php for ($j = 0; $j < $n; $j++) { ?>
                                    <td><?= number_format($matriks_norm[$i][$j], 3); ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <h3>Bobot Prioritas (Wⱼ)</h3>
            <p class="info-text">Bobot setiap kriteria hasil rata-rata baris matriks normalisasi</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="6%">No</th>
                            <th width="20%">Kode</th>
                            <th width="40%">Nama Kriteria</th>
                            <th>Bobot (Wⱼ)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($j = 0; $j < $n; $j++) { ?>
                            <tr>
                                <td><?= $j + 1; ?></td>
                                <td><?= $kriteria_data[$j]['kode_kriteria']; ?></td>
                                <td style="text-align:left"><?= $kriteria_data[$j]['nama_kriteria']; ?></td>
                                <td><strong><?= number_format($bobot[$j], 3); ?></strong></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <h3>Uji Konsistensi</h3>
            <p class="info-text">CR &lt; 0.1 = Konsisten, CR &lt; 0.2 = Cukup Konsisten, CR &ge; 0.2 = Tidak Konsisten</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="50%">Keterangan</th>
                            <th>Nilai</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="text-align:left">Lambda Maksimum (λmaks)</td>
                            <td><?= number_format($lambda_maks, 3); ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:left">Consistency Index (CI)</td>
                            <td><?= number_format($CI, 3); ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:left">Random Index (RI)</td>
                            <td><?= $RI; ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:left">Consistency Ratio (CR)</td>
                            <td><?= number_format($CR, 3); ?></td>
                        </tr>
                        <tr>
                            <td style="text-align:left"><strong>Status Konsistensi</strong></td>
                            <td>
                                <strong style="color: <?= ($CR < 0.1) ? 'green' : (($CR < 0.2) ? 'orange' : 'red'); ?>">
                                    <?= $status_konsistensi; ?>
                                </strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- ====================================================== -->
        <!-- 4. METODE TOPSIS                                        -->
        <!-- ====================================================== -->
        <div class="section">
            <h2>4. Metode TOPSIS</h2>

            <h3>Matriks Ternormalisasi (Vector Normalization)</h3>
            <p class="info-text">Normalisasi matriks keputusan menggunakan metode vector normalization</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Nama Varietas</th>
                            <?php foreach ($kriteria_data as $krit) { ?>
                                <th><?= $krit['kode_kriteria']; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < $m; $i++) { ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td style="text-align:left"><strong><?= $alternatif_names[$alternatif_ids[$i]]; ?></strong></td>
                                <?php for ($j = 0; $j < $n; $j++) { ?>
                                    <td><?= number_format($R[$i][$j], 4); ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <h3>Matriks Terbobot</h3>
            <p class="info-text">Hasil perkalian matriks ternormalisasi dengan bobot AHP</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="5%">No</th>
                            <th width="25%">Nama Varietas</th>
                            <?php foreach ($kriteria_data as $krit) { ?>
                                <th><?= $krit['kode_kriteria']; ?></th>
                            <?php } ?>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < $m; $i++) { ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td style="text-align:left"><strong><?= $alternatif_names[$alternatif_ids[$i]]; ?></strong></td>
                                <?php for ($j = 0; $j < $n; $j++) { ?>
                                    <td><?= number_format($Y[$i][$j], 4); ?></td>
                                <?php } ?>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <h3>Solusi Ideal Positif (A⁺) dan Negatif (A⁻)</h3>
            <p class="info-text">A⁺ = Input kondisi lahan petani terbobot &nbsp;|&nbsp; A⁻ = Nilai ekstrem berdasarkan jenis kriteria</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="6%">No</th>
                            <th width="44%">Nama Kriteria</th>
                            <th>A⁺ (Solusi Ideal Positif)</th>
                            <th>A⁻ (Solusi Ideal Negatif)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($j = 0; $j < $n; $j++) { ?>
                            <tr>
                                <td><?= $j + 1; ?></td>
                                <td style="text-align:left"><?= $kriteria_data[$j]['nama_kriteria']; ?></td>
                                <td><?= number_format($y_plus[$j], 4); ?></td>
                                <td><?= number_format($y_minus[$j], 4); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <h3>Jarak Separasi</h3>
            <p class="info-text">Jarak setiap alternatif terhadap solusi ideal positif (D⁺) dan negatif (D⁻)</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="6%">No</th>
                            <th width="44%">Nama Varietas</th>
                            <th>D⁺ (Jarak ke A⁺)</th>
                            <th>D⁻ (Jarak ke A⁻)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php for ($i = 0; $i < $m; $i++) { ?>
                            <tr>
                                <td><?= $i + 1; ?></td>
                                <td style="text-align:left"><?= $alternatif_names[$alternatif_ids[$i]]; ?></td>
                                <td><?= number_format($D_plus[$i], 4); ?></td>
                                <td><?= number_format($D_minus[$i], 4); ?></td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>

            <h3>Nilai Preferensi dan Peringkat Akhir</h3>
            <p class="info-text">Semakin tinggi nilai preferensi, semakin direkomendasikan varietas tersebut</p>
            <div class="table-container">
                <table>
                    <thead>
                        <tr>
                            <th width="10%">Peringkat</th>
                            <th width="15%">Kode</th>
                            <th width="45%">Nama Varietas</th>
                            <th width="30%">Nilai Preferensi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // Susun ulang berdasarkan skor tertinggi
                        $ranked_data = [];
                        for ($i = 0; $i < $m; $i++) {
                            $id_alt   = $alternatif_ids[$i];
                            $q_alt    = mysqli_query($koneksi, "SELECT kode_alternatif FROM alternatif WHERE id_alternatif = '$id_alt'");
                            $alt_info = mysqli_fetch_array($q_alt);
                            $ranked_data[] = [
                                'nama'  => $alternatif_names[$id_alt],
                                'kode'  => $alt_info ? $alt_info['kode_alternatif'] : "A-$id_alt",
                                'score' => $scores[$i]
                            ];
                        }
                        usort($ranked_data, function($a, $b) {
                            return $b['score'] <=> $a['score'];
                        });

                        $rank = 1;
                        foreach ($ranked_data as $item) {
                            if ($rank == 1)     { $row_class = 'rank-1'; $badge_class = 'badge-1'; }
                            elseif ($rank == 2) { $row_class = 'rank-2'; $badge_class = 'badge-2'; }
                            elseif ($rank == 3) { $row_class = 'rank-3'; $badge_class = 'badge-3'; }
                            else                { $row_class = '';        $badge_class = 'badge-other'; }
                        ?>
                            <tr class="<?= $row_class; ?>">
                                <td><span class="rank-badge <?= $badge_class; ?>"><?= $rank; ?></span></td>
                                <td><strong><?= $item['kode']; ?></strong></td>
                                <td style="text-align:left"><?= $item['nama']; ?></td>
                                <td><strong><?= number_format($item['score'], 6); ?></strong></td>
                            </tr>
                        <?php
                            $rank++;
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-print">
            <span>SPK Varietas Karet - Balai Pengkajian Teknologi Pertanian &copy; <?= date('Y'); ?></span>
            <span>Dicetak: <?= date('d/m/Y H:i'); ?> WIB</span>
        </div>

    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>
</html>