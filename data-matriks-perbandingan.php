<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['nama']) && !isset($_SESSION['level'])) {
    header("Location: index.php");
    exit;
}

if ($_SESSION['level'] != "Admin") {
    header("Location: menu-utama.php");
    exit();
}

$validasi = isset($_GET['validasi']) ? trim($_GET['validasi']) : "";

// Ambil semua kriteria
$query_kriteria = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
$kriteria_list  = [];
while ($k = mysqli_fetch_array($query_kriteria)) {
    $kriteria_list[] = $k;
}
$n = count($kriteria_list);

// Ambil nilai matriks yang sudah tersimpan (untuk mengisi form)
$matriks_db = [];
foreach ($kriteria_list as $ki) {
    foreach ($kriteria_list as $kj) {
        $id_i = $ki['id_kriteria'];
        $id_j = $kj['id_kriteria'];
        $q    = mysqli_query($koneksi, "
            SELECT nilai FROM matriks_perbandingan
            WHERE id_kriteria_i = '$id_i' AND id_kriteria_j = '$id_j'
        ");
        $row  = mysqli_fetch_array($q);
        $matriks_db[$id_i][$id_j] = $row ? floatval($row['nilai']) : 1;
    }
}

function formatVal($val) {
    return $val >= 1 ? number_format($val, 3) : number_format($val, 3);
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Sistem Pendukung Keputusan Metode SMART</title>
    <link rel="icon" type="image/x-icon" href="assets/img/logo.png" />
    <link href="css/styles_dash.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
    <style>
        .sb-sidenav-light .nav-link.active {
            color: #0d6efd !important;
            font-weight: 600;
        }
        .sb-sidenav-light .nav-link.active .sb-nav-link-icon {
            color: #0d6efd !important;
        }
        .matriks-table th,
        .matriks-table td {
            text-align: center;
            vertical-align: middle;
            min-width: 90px;
        }
        .matriks-table input[type="number"] {
            width: 80px;
            text-align: center;
            border: 1px solid #ced4da;
            border-radius: 4px;
            padding: 4px 6px;
        }
        .matriks-table .diagonal {
            background-color: #e9ecef;
            font-weight: bold;
            color: #495057;
        }
        .matriks-table thead th {
            background-color: #e55c2e;
            color: white;
        }
        .matriks-table tbody th {
            background-color: #e55c2e;
            color: white;
        }
        .upper-input {
            background-color: #ffffff;
        }
        .lower-input {
            background-color: #f8f9fa;
            color: #555;
        }
        .info-box {
            background-color: #fff3cd;
            border-left: 4px solid #ffc107;
            padding: 12px 16px;
            border-radius: 4px;
            margin-bottom: 16px;
            font-size: 0.9rem;
        }
        .skala-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 6px;
            padding: 16px;
            margin-bottom: 20px;
        }
        .skala-box table th {
            background-color: #e55c2e;
            color: white;
        }
    </style>
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-primary hstack gap-3">
        <a class="navbar-brand ps-3" href="menu-utama.php">Varietas Karet</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
        <ul class="navbar-nav ms-auto pe-2">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i></a>
                <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                    <li><a class="dropdown-item" href="#!"><?= $_SESSION['username']; ?></a></li>
                    <li><a class="dropdown-item" href="data-profile.php">Profil</a></li>
                    <li>
                        <hr class="dropdown-divider" />
                    </li>
                    <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#keluar">Keluar</a></li>
                </ul>
            </li>
        </ul>
    </nav>

    <!-- Modal Keluar -->
    <div class="modal fade" id="keluar" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h1 class="modal-title fs-5">Keluar dari Sistem</h1>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Apakah anda yakin ingin Keluar?</p>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-secondary" data-bs-dismiss="modal">Tidak</a>
                    <a href="keluar.php" class="btn btn-danger">Ya</a>
                </div>
            </div>
        </div>
    </div>

    <div id="layoutSidenav">
        <div id="layoutSidenav_nav">
            <nav class="sb-sidenav accordion sb-sidenav-light" id="sidenavAccordion">
                <div class="sb-sidenav-menu">
                    <div class="nav">
                        <div class="sb-sidenav-menu-heading">Menu Utama</div>
                        <a class="nav-link" href="menu-utama.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-home"></i></div>
                            Dashboard
                        </a>
                        <div class="sb-sidenav-menu-heading">Proses</div>
                        <a class="nav-link" href="data-kriteria.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cube"></i></div>
                            Data Kriteria
                        </a>
                        <a class="nav-link" href="data-subkriteria.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cube"></i></div>
                            Data Subkriteria
                        </a>
                        <a class="nav-link" href="data-matriks-perbandingan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-table"></i></div>
                            Data Matriks Perbandingan
                        </a>
                        <a class="nav-link" href="jenis-varietas.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cube"></i></div>
                            Jenis Varietas
                        </a>
                        <div class="sb-sidenav-menu-heading">Pengguna</div>
                        <a class="nav-link" href="data-profile.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                            Data Profil
                        </a>
                        <a class="nav-link" href="data-pengguna.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-users"></i></div>
                            Data Pengguna
                        </a>
                        <a class="nav-link" href="#" data-bs-toggle="modal" data-bs-target="#keluar">
                            <div class="sb-nav-link-icon"><i class="fas fa-sign-out-alt"></i></div>
                            Keluar
                        </a>
                    </div>
                </div>
                <div class="sb-sidenav-footer">
                    <div class="small">Masuk sebagai:</div>
                    <?= $_SESSION['username']; ?>
                </div>
            </nav>
        </div>

        <div id="layoutSidenav_content">
            <main>
                <div class="container-fluid px-4">
                    <h1 class="mt-4"><i class="fas fa-table"></i> Matriks Perbandingan AHP</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Matriks Perbandingan AHP</li>
                    </ol>

                    <!-- Notifikasi -->
                    <?php if ($validasi == "sukses"): ?>
                        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
                            <i class="fas fa-check-circle"></i> <strong>Berhasil!</strong> Matriks perbandingan berhasil disimpan!
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php elseif ($validasi == "error"): ?>
                        <div class="alert alert-danger alert-dismissible fade show mb-3" role="alert">
                            <i class="fas fa-exclamation-circle"></i> <strong>Gagal!</strong> Proses gagal! Silakan coba lagi.
                            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <div class="col-sm-12">

                            <!-- Info -->
                            <div class="info-box">
                                <i class="fas fa-info-circle me-1"></i>
                                Isi nilai perbandingan pada <strong>segitiga atas (putih)</strong> atau <strong>segitiga bawah (abu-abu)</strong>.
                                Keduanya akan <strong>otomatis saling mengisi</strong>. Diagonal selalu bernilai <strong>1</strong>.
                            </div>

                            <!-- Skala Saaty -->
                            <div class="skala-box">
                                <h6 class="fw-bold mb-2"><i class="fas fa-info-circle me-1"></i> Skala Perbandingan Saaty</h6>
                                <div class="table-responsive">
                                    <table class="table table-bordered table-sm mb-0">
                                        <thead>
                                            <tr>
                                                <th>Nilai</th>
                                                <th>Keterangan</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr><td class="text-center">1</td><td>Sama penting</td></tr>
                                            <tr><td class="text-center">3</td><td>Sedikit lebih penting</td></tr>
                                            <tr><td class="text-center">5</td><td>Lebih penting</td></tr>
                                            <tr><td class="text-center">7</td><td>Sangat lebih penting</td></tr>
                                            <tr><td class="text-center">9</td><td>Mutlak lebih penting</td></tr>
                                            <tr><td class="text-center">2, 4, 6, 8</td><td>Nilai tengah (kompromi)</td></tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <?php if ($n < 2): ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-1"></i>
                                    Minimal 2 kriteria diperlukan. Silakan tambah kriteria terlebih dahulu di
                                    <a href="data-kriteria.php">Data Kriteria</a>.
                                </div>
                            <?php else: ?>

                                <!-- Form Matriks -->
                                <form action="proses.php" method="post">
                                    <div class="table-responsive my-3">
                                        <table class="table table-bordered matriks-table">
                                            <thead>
                                                <tr>
                                                    <th>Kriteria</th>
                                                    <?php foreach ($kriteria_list as $k): ?>
                                                        <th>
                                                            <?= htmlspecialchars($k['kode_kriteria']) ?><br>
                                                            <small class="fw-normal"><?= htmlspecialchars($k['nama_kriteria']) ?></small>
                                                        </th>
                                                    <?php endforeach; ?>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php foreach ($kriteria_list as $idx_i => $ki): ?>
                                                    <tr>
                                                        <th>
                                                            <?= htmlspecialchars($ki['kode_kriteria']) ?><br>
                                                            <small class="fw-normal"><?= htmlspecialchars($ki['nama_kriteria']) ?></small>
                                                        </th>
                                                        <?php foreach ($kriteria_list as $idx_j => $kj):
                                                            $id_i  = $ki['id_kriteria'];
                                                            $id_j  = $kj['id_kriteria'];
                                                            $nilai = $matriks_db[$id_i][$id_j] ?? 1;
                                                        ?>
                                                            <?php if ($idx_i == $idx_j): ?>
                                                                <!-- Diagonal -->
                                                                <td class="diagonal"><strong>1</strong></td>

                                                            <?php elseif ($idx_i < $idx_j): ?>
                                                                <!-- Segitiga atas — input utama, dikirim POST -->
                                                                <td>
                                                                    <input
                                                                        type="number"
                                                                        name="matriks[<?= $id_i ?>][<?= $id_j ?>]"
                                                                        value="<?= number_format($nilai, 3, '.', '') ?>"
                                                                        min="0.111"
                                                                        max="9"
                                                                        step="any"
                                                                        class="upper-input"
                                                                        data-i="<?= $id_i ?>"
                                                                        data-j="<?= $id_j ?>"
                                                                        required />
                                                                </td>

                                                            <?php else: ?>
                                                                <!-- Segitiga bawah — tampilan saja, tidak dikirim POST -->
                                                                <td>
                                                                    <input
                                                                        type="number"
                                                                        value="<?= ($matriks_db[$id_j][$id_i] != 0) ? number_format(1 / $matriks_db[$id_j][$id_i], 3, '.', '') : 1 ?>"
                                                                        min="0.111"
                                                                        max="9"
                                                                        step="any"
                                                                        class="lower-input"
                                                                        data-i="<?= $id_i ?>"
                                                                        data-j="<?= $id_j ?>" />
                                                                </td>

                                                            <?php endif; ?>

                                                        <?php endforeach; ?>
                                                    </tr>
                                                <?php endforeach; ?>
                                            </tbody>
                                        </table>
                                    </div>

                                    <div class="d-flex gap-2">
                                        <button type="submit" name="simpan_matriks" class="btn btn-primary">
                                            <i class="fas fa-save me-1"></i> Simpan Matriks
                                        </button>
                                    </div>
                                </form>

                            <?php endif; ?>

                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="js/scripts_dash.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.3.js"></script>
    <script src="js/jquery.dataTables.js"></script>

    <script>
        $(document).ready(function() {
            // Tandai menu sidebar aktif sesuai halaman yang sedang dibuka
            var currentPage = window.location.pathname.split('/').pop().split('?')[0];
            $('.sb-sidenav .nav-link').each(function() {
                var href = $(this).attr('href');
                if (href && href.split('?')[0] === currentPage) {
                    $(this).addClass('active');
                }
            });
        });

        // Segitiga atas diisi → update segitiga bawah (FIX: tanpa pembulatan Saaty)
        document.querySelectorAll('.upper-input').forEach(function(input) {
            input.addEventListener('input', function() {
                const i   = this.getAttribute('data-i');
                const j   = this.getAttribute('data-j');
                const val = parseFloat(this.value);

                const lower = document.querySelector(
                    '.lower-input[data-i="' + j + '"][data-j="' + i + '"]'
                );
                if (lower) {
                    if (!isNaN(val) && val > 0) {
                        lower.value = (1 / val).toFixed(3);
                    } else {
                        lower.value = '';
                    }
                }
            });
        });

        // Segitiga bawah diisi → update segitiga atas (FIX: tanpa pembulatan Saaty)
        document.querySelectorAll('.lower-input').forEach(function(input) {
            input.addEventListener('input', function() {
                const i   = this.getAttribute('data-i');
                const j   = this.getAttribute('data-j');
                const val = parseFloat(this.value);

                const upper = document.querySelector(
                    '.upper-input[data-i="' + j + '"][data-j="' + i + '"]'
                );
                if (upper) {
                    if (!isNaN(val) && val > 0) {
                        upper.value = (1 / val).toFixed(3);
                    } else {
                        upper.value = '';
                    }
                }
            });
        });
    </script>
</body>

</html>