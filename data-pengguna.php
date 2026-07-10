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
                        <a class="nav-link" href="data-kriteria.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cube"></i></div>
                            Data Kriteria
                        </a>
                        <a class="nav-link" href="data-subkriteria.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cube"></i></div>
                            Data Subkriteria
                        </a>
                        <a class="nav-link" href="data-matriks-perbandingan.php">
                            <div class="sb-nav-link-icon"><i class="fas fa-cube"></i></div>
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
                    <h1 class="mt-4"><i class="fas fa-users"></i> Data Pengguna</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Data Pengguna</li>
                    </ol>
                    <div class="row">
                        <div class="col-sm-12">

                            <!-- ===================== ALERT VALIDASI ===================== -->
                            <?php
                            if ($validasi == "sukses-tambah") {
                                echo "
                                    <div class='alert alert-success alert-dismissible fade show mb-3' role='alert'>
                                        <i class='fas fa-check-circle'></i> <strong>Berhasil!</strong> Data Pengguna berhasil ditambahkan!
                                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                    </div>
                                    ";
                            } else if ($validasi == "sukses-perbarui") {
                                echo "
                                    <div class='alert alert-success alert-dismissible fade show mb-3' role='alert'>
                                        <i class='fas fa-check-circle'></i> <strong>Berhasil!</strong> Data Pengguna berhasil diperbarui!
                                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                    </div>
                                    ";
                            } else if ($validasi == "sukses-hapus") {
                                echo "
                                    <div class='alert alert-success alert-dismissible fade show mb-3' role='alert'>
                                        <i class='fas fa-check-circle'></i> <strong>Berhasil!</strong> Data Pengguna berhasil dihapus!
                                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                    </div>
                                    ";
                            } else if ($validasi == "error") {
                                echo "
                                    <div class='alert alert-danger alert-dismissible fade show mb-3' role='alert'>
                                        <i class='fas fa-exclamation-circle'></i> <strong>Gagal!</strong> Proses gagal!
                                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                    </div>
                                    ";
                            } else if ($validasi == "warning") {
                                echo "
                                    <div class='alert alert-warning alert-dismissible fade show mb-3' role='alert'>
                                        <i class='fas fa-exclamation-triangle'></i> <strong>Peringatan!</strong> Username telah digunakan, silahkan gunakan username yang berbeda!
                                        <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                    </div>
                                    ";
                            }
                            ?>
                            <!-- =========================================================== -->

                            <a class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#tambah_data">
                                <i class="fas fa-plus"></i> Tambah data
                            </a>

                            <!-- Modal Tambah -->
                            <div class="modal fade" id="tambah_data" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5">Tambah Data Pengguna</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="proses.php" method="post">
                                            <div class="modal-body">
                                                <div class="form-floating mb-3">
                                                    <input name="nama" class="form-control" type="text"
                                                        placeholder="Nama Lengkap"
                                                        pattern="[A-Za-z ]+"
                                                        oninvalid="this.setCustomValidity('Input hanya huruf')"
                                                        oninput="setCustomValidity('')"
                                                        required autocomplete="off" />
                                                    <label>Nama Lengkap</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input name="user" class="form-control" type="text"
                                                        placeholder="Username"
                                                        pattern="[A-Za-z0-9]+"
                                                        oninvalid="this.setCustomValidity('Input tidak boleh simbol')"
                                                        oninput="setCustomValidity('')"
                                                        required autocomplete="off" />
                                                    <label>Username</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <select name="level" class="form-select" required>
                                                        <option value=""></option>
                                                        <option value="Admin">Admin</option>
                                                        <option value="User">User</option>
                                                    </select>
                                                    <label>Level</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input name="pass" class="form-control" type="password"
                                                        placeholder="Password"
                                                        minlength="5" required autocomplete="off" />
                                                    <label>Password</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input name="konfir" class="form-control" type="password"
                                                        placeholder="Konfirmasi Password"
                                                        minlength="5" required autocomplete="off" />
                                                    <label>Konfirmasi Password</label>
                                                </div>
                                            </div>
                                            <div class="modal-footer">
                                                <a class="btn btn-secondary" data-bs-dismiss="modal">Tutup</a>
                                                <button name="tambah-pengguna" class="btn btn-primary">Tambah</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabel Data -->
                            <div class="table-responsive mt-3">
                                <table id="data-pengguna" class="cell-bordered display" width="100%">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th class="text-center text-white">No.</th>
                                            <th class="text-center text-white">Nama Lengkap</th>
                                            <th class="text-center text-white">Username</th>
                                            <th class="text-center text-white">Level</th>
                                            <th class="text-center text-white">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $q_tabel = mysqli_query($koneksi, "SELECT * FROM pengguna");
                                        $no = 1;
                                        while ($baris_tabel = mysqli_fetch_array($q_tabel)) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $no; ?></td>
                                                <td class="text-center"><?= $baris_tabel['nama']; ?></td>
                                                <td class="text-center"><?= $baris_tabel['username']; ?></td>
                                                <td class="text-center"><?= $baris_tabel['level']; ?></td>
                                                <td class="text-center">
                                                    <?php if ($baris_tabel['level'] != "Admin") { ?>
                                                        <div class="d-flex justify-content-center">
                                                            <a class="btn btn-warning me-2" data-bs-toggle="modal"
                                                               data-bs-target="#edit_data<?= $baris_tabel['id_pengguna']; ?>">
                                                                <i class="fas fa-pencil"></i>
                                                            </a>
                                                            <a class="btn btn-danger" data-bs-toggle="modal"
                                                               data-bs-target="#hapus_data<?= $baris_tabel['id_pengguna']; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        </div>
                                                    <?php } else { ?>
                                                        <p>-</p>
                                                    <?php } ?>
                                                </td>
                                            </tr>
                                        <?php
                                            $no++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- ===================== MODAL EDIT & HAPUS ===================== -->
                            <?php
                            $q_modal = mysqli_query($koneksi, "SELECT * FROM pengguna");
                            while ($baris_modal = mysqli_fetch_array($q_modal)) {
                                $id          = $baris_modal['id_pengguna'];
                                $nama_user   = $baris_modal['nama'];
                                $username    = $baris_modal['username'];
                                $level_user  = $baris_modal['level'];
                            ?>
                                <!-- Modal Edit -->
                                <div class="modal fade" id="edit_data<?= $id; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5">Edit Data Pengguna</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="proses.php" method="post">
                                                <div class="modal-body">
                                                    <input type="hidden" name="id" value="<?= $id; ?>">
                                                    <div class="form-floating mb-3">
                                                        <input name="nama" class="form-control" type="text"
                                                            placeholder="Nama Lengkap"
                                                            value="<?= $nama_user; ?>"
                                                            pattern="[A-Za-z ]+"
                                                            oninvalid="this.setCustomValidity('Input hanya huruf')"
                                                            oninput="setCustomValidity('')"
                                                            required autocomplete="off" />
                                                        <label>Nama Lengkap</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input name="user" class="form-control" type="text"
                                                            placeholder="Username"
                                                            value="<?= $username; ?>"
                                                            readonly />
                                                        <label>Username</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <select name="level" class="form-select" required>
                                                            <option value=""></option>
                                                            <option value="Admin" <?= $level_user == "Admin" ? "selected" : ""; ?>>Admin</option>
                                                            <option value="User"  <?= $level_user == "User"  ? "selected" : ""; ?>>User</option>
                                                        </select>
                                                        <label>Level</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input name="pass" class="form-control" type="password"
                                                            placeholder="Password"
                                                            minlength="5" autocomplete="off" />
                                                        <label>Password <span class="text-muted small">(isi jika ingin ubah)</span></label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input name="konfir" class="form-control" type="password"
                                                            placeholder="Konfirmasi Password"
                                                            minlength="5" autocomplete="off" />
                                                        <label>Konfirmasi Password</label>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-secondary" data-bs-dismiss="modal">Tutup</a>
                                                    <button name="edit-pengguna" class="btn btn-primary">Simpan</button>
                                                </div>
                                            </form>
                                        </div>
                                    </div>
                                </div>

                                <!-- Modal Hapus -->
                                <div class="modal fade" id="hapus_data<?= $id; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5">Hapus Data Pengguna</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah anda yakin ingin menghapus pengguna <strong><?= $nama_user; ?></strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <a class="btn btn-secondary" data-bs-dismiss="modal">Tidak</a>
                                                <a href="hapus-data-pengguna.php?id=<?= $id; ?>" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i> Ya
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php } ?>
                            <!-- ============================================================= -->

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
            $('#data-pengguna').DataTable();

            // Tandai menu sidebar aktif sesuai halaman yang sedang dibuka
            var currentPage = window.location.pathname.split('/').pop().split('?')[0];
            $('.sb-sidenav .nav-link').each(function() {
                var href = $(this).attr('href');
                if (href && href.split('?')[0] === currentPage) {
                    $(this).addClass('active');
                }
            });
        });
    </script>
</body>

</html>