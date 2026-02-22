<?php
session_start();
include 'koneksi.php';
include 'base-url.php';
if (isset($_SESSION['nama']) && isset($_SESSION['level'])) {
    header("Location: menu-utama.php?validasi=sukses");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>Rekomendasi Varietas Karet</title>
    <!-- Favicon-->
    <link rel="icon" type="image/x-icon" href="assets/img/background.jpeg" />
    <!-- Bootstrap Icons-->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.5.0/font/bootstrap-icons.css" rel="stylesheet" />
    <!-- Google fonts-->
    <link href="https://fonts.googleapis.com/css?family=Merriweather+Sans:400,700" rel="stylesheet" />
    <link href="https://fonts.googleapis.com/css?family=Merriweather:400,300,300italic,400italic,700,700italic" rel="stylesheet" type="text/css" />
    <!-- SimpleLightbox plugin CSS-->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.css" rel="stylesheet" />
    <!-- Core theme CSS (includes Bootstrap)-->
    <link href="css/styles.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="css/jquery.dataTables.css">
</head>

<body id="page-top">
    <!-- Navigation-->
    <nav class="navbar navbar-expand-lg navbar-light fixed-top py-3" id="mainNav">
        <div class="container px-4 px-lg-5">
            <a class="navbar-brand d-flex align-items-center" href="#">
            <img src="assets/img/karet.png" width="75" class="me-2">
            <span class="fw-bold text-white">Varietas Tanaman Karet</span>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse"
            data-bs-target="#navbarResponsive">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Menu -->
        <div class="collapse navbar-collapse justify-content-end" id="navbarResponsive">
            <ul class="navbar-nav align-items-center">

                <li class="nav-item">
                    <a class="nav-link px-3 fw-semibold text-white" href="#penggunaan">
                        Jenis Varietas
                    </a>
                </li>

                <li class="nav-item">
                    <a class="btn btn-danger px-4 py-2 ms-2" href="masuk.php">
                        Masuk
                    </a>
                </li>
        </div>
    </nav>
    <!-- Masthead-->
    <header class="masthead">
        <div class="container px-4 px-lg-5 h-100">
            <div class="row gx-4 gx-lg-5 h-100">
                <div class="col-lg-8 align-self-end">
                    <h1 class="text-white font-weight-bold">Sistem Pendukung Keputusan</h1>
                    <hr class="divider" />
                </div>
                <div class="col-lg-8 align-self-baseline">
                    <p class="text-white-75 mb-5">Selamat datang di aplikasi pemilihan varietas karet.
            Website ini dirancang untuk memberikan rekomendasi varietas karet
            yang paling sesuai berdasarkan faktor lingkungan lahan yang tersedia.
                </div>
            </div>
        </div>
    </header>
    <!-- Services-->
    <section class="page-section" id="rekomendasi">
        <div class="container px-4 px-lg-5">
            <hr class="divider-title" />
            <?php
            // =====================================================================
            // PERBAIKAN:
            // 1. Hapus $get_admin_user dan $username yang tidak dipakai
            //    (menyebabkan warning karena tabel pengguna kosong / tidak ada Admin)
            // 2. Query langsung ambil nilai peringkat tertinggi tanpa filter username
            // =====================================================================
            $query      = mysqli_query($koneksi, "
                SELECT alternatif.nama_alternatif AS nama_alternatif 
                FROM peringkat 
                JOIN alternatif ON peringkat.id_alternatif = alternatif.id_alternatif 
                ORDER BY peringkat.nilai_peringkat DESC 
                LIMIT 1
            ");
            $data_rekom = mysqli_fetch_array($query);
            ?>
        </div>
    </section>
    <!-- Bootstrap core JS-->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <!-- SimpleLightbox plugin JS-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/SimpleLightbox/2.1.0/simpleLightbox.min.js"></script>
    <!-- Core theme JS-->
    <script src="js/scripts.js"></script>
    <script src="https://cdn.startbootstrap.com/sb-forms-latest.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.3.js"></script>
    <script src="js/jquery.dataTables.js"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $('#data-rekomendasi').DataTable();
        })
    </script>
</body>

</html>