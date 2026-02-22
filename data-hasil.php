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

$username = $_SESSION['username'];
$level    = $_SESSION['level'];
$validasi = isset($_GET['validasi']) ? trim($_GET['validasi']) : "";
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Varietas Karet - Data Hasil</title>
    <link rel="icon" type="image/x-icon" href="assets/img/logo.png" />
    <link href="css/styles_dash.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
</head>

<body class="sb-nav-fixed">
    <nav class="sb-topnav navbar navbar-expand navbar-dark bg-primary hstack gap-3">
        <a class="navbar-brand ps-3" href="menu-utama.php">Varietas Karet</a>
        <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!">
            <i class="fas fa-bars"></i>
        </button>
        <ul class="navbar-nav ms-auto pe-2">
            <li class="nav-item dropdown">
                <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <i class="fas fa-user fa-fw"></i>
                </a>
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
                <form>
                    <div class="modal-body">
                        <p>Apakah anda yakin ingin Keluar?</p>
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-secondary" data-bs-dismiss="modal">Tidak</a>
                        <a href="keluar.php" class="btn btn-danger">Ya</a>
                    </div>
                </form>
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
                        <?php
                        if ($_SESSION['level'] == "Admin") {
                            echo "
                            <a class='nav-link' href='data-kriteria.php'>
                                <div class='sb-nav-link-icon'><i class='fas fa-cube'></i></div>
                                Data Kriteria
                            </a>
                            <a class='nav-link' href='data-subkriteria.php'>
                                <div class='sb-nav-link-icon'><i class='fas fa-cube'></i></div>
                                Data Subkriteria
                            </a>
                            ";
                        }
                        ?>
                        <a class="nav-link" href="jenis-varietas.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cube"></i></div>
                            Jenis Varietas
                        </a>
                        <div class="sb-sidenav-menu-heading">Pengguna</div>
                        <a class="nav-link" href="data-profile.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-user"></i></div>
                            Data Profil
                        </a>
                        <?php
                        if ($_SESSION['level'] == "Admin") {
                            echo "
                            <a class='nav-link' href='data-pengguna.php'>
                                <div class='sb-nav-link-icon'><i class='fas fa-users'></i></div>
                                Data Pengguna
                            </a>
                            ";
                        }
                        ?>
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
                    <h1 class="mt-4"><i class="fas fa-chart-line"></i> Data Hasil</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item"><a href="menu-utama.php">Dashboard</a></li>
                        <li class="breadcrumb-item active">Data Hasil</li>
                    </ol>
                    <div class="row">
                        <div class="col-sm-12">

                            <!-- Alert Validasi -->
                            <?php
                            if ($validasi == "sukses") {
                                echo "
                                <div class='alert alert-success alert-dismissible fade show mb-3' role='alert'>
                                    <i class='fas fa-check-circle'></i> <strong>Berhasil!</strong> Proses Perhitungan selesai.
                                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                </div>
                                ";
                            } else if ($validasi == "error") {
                                echo "
                                <div class='alert alert-danger alert-dismissible fade show mb-3' role='alert'>
                                    <i class='fas fa-exclamation-triangle'></i> <strong>Gagal!</strong> Proses perhitungan gagal. Silakan coba lagi.
                                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                </div>
                                ";
                            }
                            ?>

                            <?php
                            $cek_data = mysqli_query($koneksi, "SELECT COUNT(*) as total FROM peringkat");
                            $row_cek  = mysqli_fetch_array($cek_data);

                            if ($row_cek['total'] == 0) {
                                echo "
                                <div class='alert alert-warning alert-dismissible fade show mb-3' role='alert'>
                                    <i class='fas fa-info-circle'></i> Tidak ada data hasil perhitungan. Silakan lakukan perhitungan terlebih dahulu.
                                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                </div>
                                <div class='text-center'>
                                    <a href='menu-utama.php' class='btn btn-primary'>
                                        <i class='fas fa-arrow-left'></i> Kembali ke Dashboard
                                    </a>
                                </div>
                                ";
                            } else {

                                $query_rekom = mysqli_query($koneksi, "
                                    SELECT 
                                        alternatif.nama_alternatif AS nama_alternatif, 
                                        alternatif.kode_alternatif AS kode_alternatif,
                                        peringkat.nilai_peringkat  AS nilai
                                    FROM peringkat 
                                    JOIN alternatif ON peringkat.id_alternatif = alternatif.id_alternatif 
                                    ORDER BY peringkat.nilai_peringkat DESC 
                                    LIMIT 1
                                ");

                                if (mysqli_num_rows($query_rekom) > 0) {
                                    $data_rekom = mysqli_fetch_array($query_rekom);
                            ?>

                                    <!-- Card Rekomendasi Terbaik -->
                                    <div class="card mb-4 border-success shadow-sm">
                                        <div class="card-header bg-success text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-trophy"></i> Rekomendasi Varietas Terbaik
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="row align-items-center">
                                                <div class="col-md-8">
                                                    <h4 class="mb-2">
                                                        <strong><?= $data_rekom['nama_alternatif']; ?></strong>
                                                    </h4>
                                                    <p class="mb-0 text-muted">
                                                        Kode: <strong><?= $data_rekom['kode_alternatif']; ?></strong> | 
                                                        Nilai Preferensi: <strong><?= number_format($data_rekom['nilai'], 6); ?></strong>
                                                    </p>
                                                </div>
                                                <div class="col-md-4 text-end">
                                                    <i class="fas fa-award text-warning" style="font-size: 4rem;"></i>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Tabel Peringkat Lengkap -->
                                    <div class="card shadow-sm">
                                        <div class="card-header bg-primary text-white">
                                            <h5 class="mb-0">
                                                <i class="fas fa-list-ol"></i> Daftar Peringkat Lengkap
                                            </h5>
                                        </div>
                                        <div class="card-body">
                                            <div class="table-responsive">
                                                <table class="table table-striped table-bordered table-hover" id="dataTable" width="100%">
                                                    <thead class="table-dark">
                                                        <tr>
                                                            <th class="text-center" width="5%">No.</th>
                                                            <th class="text-center" width="10%">Peringkat</th>
                                                            <th class="text-center" width="15%">Kode</th>
                                                            <th class="text-center" width="50%">Nama Varietas</th>
                                                            <th class="text-center" width="20%">Nilai Preferensi</th>
                                                        </tr>
                                                    </thead>
                                                    <tbody>
                                                        <?php
                                                        $query_tabel = mysqli_query($koneksi, "
                                                            SELECT 
                                                                alternatif.kode_alternatif AS kode, 
                                                                alternatif.nama_alternatif AS nama, 
                                                                peringkat.nilai_peringkat  AS nilai 
                                                            FROM alternatif 
                                                            JOIN peringkat ON alternatif.id_alternatif = peringkat.id_alternatif 
                                                            ORDER BY peringkat.nilai_peringkat DESC
                                                        ");

                                                        if (mysqli_num_rows($query_tabel) > 0) {
                                                            $no = 1;
                                                            while ($baris = mysqli_fetch_array($query_tabel)) {
                                                                if ($no == 1) {
                                                                    $badge_class = "bg-warning text-dark";
                                                                } else if ($no == 2) {
                                                                    $badge_class = "bg-secondary text-white";
                                                                } else if ($no == 3) {
                                                                    $badge_class = "bg-danger text-white";
                                                                } else {
                                                                    $badge_class = "bg-light text-dark";
                                                                }
                                                        ?>
                                                            <tr>
                                                                <td class="text-center"><?= $no; ?></td>
                                                                <td class="text-center">
                                                                    <span class="badge <?= $badge_class; ?> px-3 py-2">
                                                                        <?= $no; ?>
                                                                    </span>
                                                                </td>
                                                                <td class="text-center">
                                                                    <strong><?= $baris['kode']; ?></strong>
                                                                </td>
                                                                <td><?= $baris['nama']; ?></td>
                                                                <td class="text-center">
                                                                    <strong><?= number_format($baris['nilai'], 6); ?></strong>
                                                                </td>
                                                            </tr>
                                                        <?php
                                                                $no++;
                                                            }
                                                        } else {
                                                            echo "<tr><td colspan='5' class='text-center'>Tidak ada data</td></tr>";
                                                        }
                                                        ?>
                                                    </tbody>
                                                </table>
                                            </div>

                                            <!-- =============================================================  -->
                                            <!-- PERBAIKAN: Hapus target="_blank" agar session tetap terbawa  -->
                                            <!-- Ganti nama file dari print.php → cetak-hasil.php             -->
                                            <!-- =============================================================  -->
                                            <div class="text-center mt-4">
                                                <a href="cetak-hasil.php?validasi=sukses" class="btn btn-primary btn-lg">
                                                    <i class="fas fa-print"></i> Cetak Hasil
                                                </a>
                                                <a href="menu-utama.php" class="btn btn-secondary btn-lg">
                                                    <i class="fas fa-redo"></i> Hitung Ulang
                                                </a>
                                            </div>

                                        </div>
                                    </div>

                            <?php
                                } else {
                                    echo "
                                    <div class='alert alert-danger alert-dismissible fade show mb-3' role='alert'>
                                        <i class='fas fa-exclamation-circle'></i> Terjadi kesalahan saat mengambil data rekomendasi.
                                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                    </div>
                                    <div class='text-center'>
                                        <a href='menu-utama.php' class='btn btn-primary'>
                                            <i class='fas fa-arrow-left'></i> Kembali ke Dashboard
                                        </a>
                                    </div>
                                    ";
                                }
                            }
                            ?>
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
    <script type="text/javascript">
        $(document).ready(function() {
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                },
                "pageLength": 10,
                "ordering": false
            });
        });
    </script>
</body>

</html>