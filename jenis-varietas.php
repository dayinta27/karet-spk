<?php
session_start();
include 'koneksi.php';
if (!isset($_SESSION['nama']) && !isset($_SESSION['level'])) {
    header("Location: index.php");
    exit;
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
    <title>Sistem Pendukung Keputusan Varietas Karet</title>
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
                            </a><a class='nav-link' href='data-matriks-perbandingan.php'>
                                <div class='sb-nav-link-icon'><i class='fas fa-cube'></i></div>
                                Data Matriks Perbandingan
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
                    <h1 class="mt-4"><i class="fas fa-cube"></i> Jenis Varietas</h1>
                    <ol class="breadcrumb mb-4">
                        <li class="breadcrumb-item active">Jenis Varietas</li>
                    </ol>
                    <div class="row">
                        <div class="col-sm-12">

                            <!-- ===================== ALERT VALIDASI ===================== -->
                            <?php
                            if ($validasi == "sukses-tambah") {
                                echo "
                                <div class='alert alert-success alert-dismissible fade show mb-3' role='alert'>
                                    <i class='fas fa-check-circle'></i> <strong>Berhasil!</strong> Data varietas berhasil ditambahkan.
                                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                </div>
                                ";
                            } else if ($validasi == "sukses-perbarui") {
                                echo "
                                <div class='alert alert-success alert-dismissible fade show mb-3' role='alert'>
                                    <i class='fas fa-check-circle'></i> <strong>Berhasil!</strong> Data varietas berhasil diperbarui.
                                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                </div>
                                ";
                            } else if ($validasi == "sukses-hapus") {
                                echo "
                                <div class='alert alert-success alert-dismissible fade show mb-3' role='alert'>
                                    <i class='fas fa-check-circle'></i> <strong>Berhasil!</strong> Data varietas berhasil dihapus.
                                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                </div>
                                ";
                            } else if ($validasi == "warning") {
                                echo "
                                <div class='alert alert-warning alert-dismissible fade show mb-3' role='alert'>
                                    <i class='fas fa-exclamation-triangle'></i> <strong>Peringatan!</strong> Kode alternatif sudah digunakan, gunakan kode yang berbeda.
                                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                </div>
                                ";
                            } else if ($validasi == "error") {
                                echo "
                                <div class='alert alert-danger alert-dismissible fade show mb-3' role='alert'>
                                    <i class='fas fa-exclamation-circle'></i> <strong>Gagal!</strong> Terjadi kesalahan, silakan coba lagi.
                                    <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
                                </div>
                                ";
                            }
                            ?>
                            <!-- =========================================================== -->

                            <?php if ($_SESSION['level'] == "Admin") { ?>
                            <a class="btn btn-primary mb-3" data-bs-toggle="modal" data-bs-target="#tambah_data">
                                <i class="fas fa-plus"></i> Tambah Data
                            </a>
                            <?php } ?>

                            <!-- Modal Tambah Data -->
                            <div class="modal fade" id="tambah_data" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h1 class="modal-title fs-5">Tambah Data Varietas</h1>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                        </div>
                                        <form action="proses.php" method="post">
                                            <div class="modal-body">
                                                <div class="form-floating mb-3">
                                                    <input
                                                        name="kode"
                                                        class="form-control"
                                                        type="text"
                                                        placeholder="Kode Alternatif"
                                                        pattern="[A-Za-z0-9]+"
                                                        oninvalid="this.setCustomValidity('Tidak Boleh Simbol')"
                                                        oninput="setCustomValidity('')"
                                                        required
                                                        autocomplete="off"
                                                    />
                                                    <label>Kode Alternatif</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input
                                                        name="nama"
                                                        class="form-control"
                                                        type="text"
                                                        placeholder="Nama Alternatif"
                                                        required
                                                        autocomplete="off"
                                                    />
                                                    <label>Nama Alternatif</label>
                                                </div>
                                                <?php
                                                $query = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
                                                while ($baris = mysqli_fetch_array($query)) {
                                                    $id_kriteria = $baris['id_kriteria'];
                                                ?>
                                                    <div class="form-floating mb-3">
                                                        <input type="hidden" name="kriteria[]" value="<?= $id_kriteria; ?>">
                                                        <select name="subkriteria[]" class="form-select" required>
                                                            <option value="">-- Pilih --</option>
                                                            <?php
                                                            $select = mysqli_query($koneksi, "SELECT * FROM subkriteria WHERE id_kriteria = '$id_kriteria' ORDER BY id_subkriteria");
                                                            while ($option = mysqli_fetch_array($select)) {
                                                                echo "<option value='" . $option['id_subkriteria'] . "'>" . $option['nama_subkriteria'] . "</option>";
                                                            }
                                                            ?>
                                                        </select>
                                                        <label><?= $baris['nama_kriteria']; ?></label>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="modal-footer">
                                                <a class="btn btn-secondary" data-bs-dismiss="modal">Tutup</a>
                                                <button name="tambah-alternatif" class="btn btn-primary">Tambah</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                            <!-- Tabel Data -->
                            <div class="table-responsive mt-3">
                                <?php if ($_SESSION['level'] == "User") { ?>
                                    <p class="text-danger fst-italic">
                                        *Anda dapat melihat jenis varietas (tanaman karet) yang telah ditambahkan oleh admin secara detail dengan cara klik logo "mata" pada tabel detail.
                                    </p>
                                <?php } ?>

                                <table id="jenis-varietas" class="table table-bordered display" width="100%">
                                    <thead class="bg-primary">
                                        <tr>
                                            <th class="text-center text-white">No.</th>
                                            <th class="text-center text-white">Kode Alternatif</th>
                                            <th class="text-center text-white">Nama Alternatif</th>
                                            <th class="text-center text-white">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $query = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif");
                                        $no = 1;
                                        while ($baris = mysqli_fetch_array($query)) {
                                        ?>
                                            <tr>
                                                <td class="text-center"><?= $no; ?></td>
                                                <td class="text-center"><?= $baris['kode_alternatif']; ?></td>
                                                <td class="text-center"><?= $baris['nama_alternatif']; ?></td>
                                                <td class="text-center">
                                                    <div class="d-flex justify-content-center gap-1">
                                                        <a class="btn btn-info btn-sm"
                                                           data-bs-toggle="modal"
                                                           data-bs-target="#view_data<?= $baris['id_alternatif']; ?>">
                                                            <i class="fas fa-eye text-white"></i>
                                                        </a>
                                                        <?php if ($_SESSION['level'] == "Admin") { ?>
                                                            <a class="btn btn-warning btn-sm"
                                                               data-bs-toggle="modal"
                                                               data-bs-target="#edit_data<?= $baris['id_alternatif']; ?>">
                                                                <i class="fas fa-pencil"></i>
                                                            </a>
                                                            <a class="btn btn-danger btn-sm"
                                                               data-bs-toggle="modal"
                                                               data-bs-target="#hapus_data<?= $baris['id_alternatif']; ?>">
                                                                <i class="fas fa-trash"></i>
                                                            </a>
                                                        <?php } ?>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php
                                            $no++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Modal View, Edit, Hapus -->
                            <?php
                            $query = mysqli_query($koneksi, "SELECT * FROM alternatif ORDER BY id_alternatif");
                            while ($baris = mysqli_fetch_array($query)) {
                                $id = $baris['id_alternatif'];
                            ?>

                                <!-- Modal View -->
                                <div class="modal fade" id="view_data<?= $id; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5">Detail Jenis Varietas</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <?php
                                                $data_alt = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM alternatif WHERE id_alternatif = '$id'"));
                                                ?>
                                                <div class="form-floating mb-3">
                                                    <input class="form-control" type="text" value="<?= $data_alt['kode_alternatif']; ?>" readonly />
                                                    <label>Kode Alternatif</label>
                                                </div>
                                                <div class="form-floating mb-3">
                                                    <input class="form-control" type="text" value="<?= $data_alt['nama_alternatif']; ?>" readonly />
                                                    <label>Nama Alternatif</label>
                                                </div>
                                                <?php
                                                $query_krit = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
                                                $matriks    = mysqli_query($koneksi, "SELECT * FROM matriks WHERE id_alternatif = '$id' ORDER BY id_kriteria");
                                                while ($krit = mysqli_fetch_array($query_krit)) {
                                                    $mat      = mysqli_fetch_array($matriks);
                                                    $id_sub   = isset($mat['id_subkriteria']) ? $mat['id_subkriteria'] : "";
                                                    $data_sub = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM subkriteria WHERE id_subkriteria = '$id_sub'"));
                                                    $nama_sub = isset($data_sub['nama_subkriteria']) ? $data_sub['nama_subkriteria'] : "-";
                                                ?>
                                                    <div class="form-floating mb-3">
                                                        <input class="form-control" type="text" value="<?= $nama_sub; ?>" readonly />
                                                        <label><?= $krit['nama_kriteria']; ?></label>
                                                    </div>
                                                <?php } ?>
                                            </div>
                                            <div class="modal-footer">
                                                <a class="btn btn-secondary" data-bs-dismiss="modal">Tutup</a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php if ($_SESSION['level'] == "Admin") { ?>

                                <!-- Modal Edit -->
                                <div class="modal fade" id="edit_data<?= $id; ?>" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h1 class="modal-title fs-5">Edit Data Varietas</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <form action="proses.php" method="post">
                                                <div class="modal-body">
                                                    <?php
                                                    $data_alt = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM alternatif WHERE id_alternatif = '$id'"));
                                                    ?>
                                                    <input type="hidden" name="id" value="<?= $id; ?>">
                                                    <div class="form-floating mb-3">
                                                        <input
                                                            name="kode"
                                                            class="form-control"
                                                            type="text"
                                                            placeholder="Kode Alternatif"
                                                            value="<?= $data_alt['kode_alternatif']; ?>"
                                                            readonly
                                                        />
                                                        <label>Kode Alternatif</label>
                                                    </div>
                                                    <div class="form-floating mb-3">
                                                        <input
                                                            name="nama"
                                                            class="form-control"
                                                            type="text"
                                                            placeholder="Nama Alternatif"
                                                            value="<?= $data_alt['nama_alternatif']; ?>"
                                                            required
                                                            autocomplete="off"
                                                        />
                                                        <label>Nama Alternatif</label>
                                                    </div>
                                                    <?php
                                                    $query_krit = mysqli_query($koneksi, "SELECT * FROM kriteria ORDER BY id_kriteria");
                                                    $matriks    = mysqli_query($koneksi, "SELECT * FROM matriks WHERE id_alternatif = '$id' ORDER BY id_kriteria");
                                                    while ($krit = mysqli_fetch_array($query_krit)) {
                                                        $mat        = mysqli_fetch_array($matriks);
                                                        $id_krit    = $krit['id_kriteria'];
                                                        $id_sub_sel = isset($mat['id_subkriteria']) ? $mat['id_subkriteria'] : "";
                                                    ?>
                                                        <div class="form-floating mb-3">
                                                            <input type="hidden" name="kriteria[]" value="<?= $id_krit; ?>">
                                                            <select name="subkriteria[]" class="form-select" required>
                                                                <option value="">-- Pilih --</option>
                                                                <?php
                                                                $select = mysqli_query($koneksi, "SELECT * FROM subkriteria WHERE id_kriteria = '$id_krit' ORDER BY id_subkriteria");
                                                                while ($option = mysqli_fetch_array($select)) {
                                                                    $selected_attr = ($option['id_subkriteria'] == $id_sub_sel) ? "selected" : "";
                                                                    echo "<option value='" . $option['id_subkriteria'] . "' $selected_attr>" . $option['nama_subkriteria'] . "</option>";
                                                                }
                                                                ?>
                                                            </select>
                                                            <label><?= $krit['nama_kriteria']; ?></label>
                                                        </div>
                                                    <?php } ?>
                                                </div>
                                                <div class="modal-footer">
                                                    <a class="btn btn-secondary" data-bs-dismiss="modal">Tutup</a>
                                                    <button name="edit-alternatif" class="btn btn-primary">Simpan</button>
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
                                                <h1 class="modal-title fs-5">Hapus Data Varietas</h1>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p>Apakah anda yakin ingin menghapus varietas <strong><?= $baris['nama_alternatif']; ?></strong>?</p>
                                            </div>
                                            <div class="modal-footer">
                                                <a class="btn btn-secondary" data-bs-dismiss="modal">Tidak</a>
                                                <a href="hapus-jenis-varietas.php?id=<?= $id; ?>" class="btn btn-danger">
                                                    <i class="fas fa-trash"></i> Ya, Hapus
                                                </a>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <?php } ?>

                            <?php } ?>

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
            $('#jenis-varietas').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.10.24/i18n/Indonesian.json"
                },
                "pageLength": 10
            });
        });
    </script>
</body>

</html>