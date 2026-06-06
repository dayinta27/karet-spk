<?php
session_start();
include 'koneksi.php';

// ========================================================================
// LOGIN
// ========================================================================
if (isset($_POST['masuk'])) {
    $user = htmlspecialchars($_POST['user']);
    $pass = htmlspecialchars($_POST['pass']);
    $hash = hash('sha256', $pass);

    $query = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE username = '$user' AND password = '$hash'");
    if (mysqli_num_rows($query) > 0) {
        $baris = mysqli_fetch_array($query);
        $_SESSION['id']       = $baris['id_pengguna'];
        $_SESSION['username'] = $baris['username'];
        $_SESSION['nama']     = $baris['nama'];
        $_SESSION['level']    = $baris['level'];
        header("Location: menu-utama.php?validasi=sukses");
    } else {
        header("Location: masuk.php?validasi=error");
    }
    exit;
}

// ========================================================================
// REGISTRASI
// ========================================================================
if (isset($_POST['regis'])) {
    $nama   = htmlspecialchars($_POST['nama']);
    $user   = htmlspecialchars($_POST['user']);
    $pass   = htmlspecialchars($_POST['pass']);
    $konfir = htmlspecialchars($_POST['konfir']);

    if ($pass == $konfir) {
        $query = mysqli_query($koneksi, "SELECT username FROM pengguna WHERE username = '$user'");
        if (mysqli_num_rows($query) > 0) {
            header("Location: regis.php?validasi=warning");
        } else {
            $hash   = hash('sha256', $pass);
            $insert = mysqli_query($koneksi, "INSERT INTO pengguna(nama, username, password, level) VALUES('$nama', '$user', '$hash', 'User')");
            if ($insert) {
                header("Location: regis.php?validasi=sukses");
            } else {
                header("Location: regis.php?validasi=error");
            }
        }
    } else {
        header("Location: regis.php?validasi=error");
    }
    exit;
}

// ========================================================================
// TAMBAH KRITERIA (TANPA BOBOT)
// ========================================================================
if (isset($_POST['tambah-kriteria'])) {
    $kode  = htmlspecialchars($_POST['kode']);
    $nama  = htmlspecialchars($_POST['nama']);
    $jenis = htmlspecialchars($_POST['jenis']);

    $query = mysqli_query($koneksi, "SELECT kode_kriteria FROM kriteria WHERE kode_kriteria = '$kode'");
    if (mysqli_num_rows($query) > 0) {
        header("Location: data-kriteria.php?validasi=warning");
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO kriteria(kode_kriteria, nama_kriteria, jenis_kriteria) VALUES('$kode', '$nama', '$jenis')");
        if ($insert) {
            header("Location: data-kriteria.php?validasi=sukses-tambah");
        } else {
            header("Location: data-kriteria.php?validasi=error");
        }
    }
    exit;
}

// ========================================================================
// EDIT KRITERIA (TANPA BOBOT)
// ========================================================================
if (isset($_POST['edit-kriteria'])) {
    $id    = htmlspecialchars($_POST['id']);
    $kode  = htmlspecialchars($_POST['kode']);
    $nama  = htmlspecialchars($_POST['nama']);
    $jenis = htmlspecialchars($_POST['jenis']);

    $update = mysqli_query($koneksi, "UPDATE kriteria SET kode_kriteria = '$kode', nama_kriteria = '$nama', jenis_kriteria = '$jenis' WHERE id_kriteria = '$id'");
    if ($update) {
        header("Location: data-kriteria.php?validasi=sukses-perbarui");
    } else {
        header("Location: data-kriteria.php?validasi=error");
    }
    exit;
}

// ========================================================================
// TAMBAH SUBKRITERIA
// ========================================================================
if (isset($_POST['tambah-subkriteria'])) {
    $id    = htmlspecialchars($_POST['id']);
    $nama  = htmlspecialchars($_POST['nama']);
    $nilai = htmlspecialchars($_POST['nilai']);

    $insert = mysqli_query($koneksi, "INSERT INTO subkriteria(id_kriteria, nama_subkriteria, nilai_subkriteria) VALUES('$id', '$nama', '$nilai')");
    if ($insert) {
        header("Location: data-subkriteria.php?validasi=sukses-tambah");
    } else {
        header("Location: data-subkriteria.php?validasi=error");
    }
    exit;
}

// ========================================================================
// EDIT SUBKRITERIA
// ========================================================================
if (isset($_POST['edit-subkriteria'])) {
    $id    = htmlspecialchars($_POST['id']);
    $nama  = htmlspecialchars($_POST['nama']);
    $nilai = htmlspecialchars($_POST['nilai']);

    $update = mysqli_query($koneksi, "UPDATE subkriteria SET nama_subkriteria = '$nama', nilai_subkriteria = '$nilai' WHERE id_subkriteria = '$id'");
    if ($update) {
        header("Location: data-subkriteria.php?validasi=sukses-perbarui");
    } else {
        header("Location: data-subkriteria.php?validasi=error");
    }
    exit;
}

// ========================================================================
// TAMBAH JENIS VARIETAS
// ========================================================================
if (isset($_POST['tambah-alternatif'])) {
    $kode        = htmlspecialchars($_POST['kode']);
    $nama        = htmlspecialchars($_POST['nama']);
    $kriteria    = $_POST['kriteria'];
    $subkriteria = $_POST['subkriteria'];

    $query = mysqli_query($koneksi, "SELECT kode_alternatif FROM alternatif WHERE kode_alternatif = '$kode'");
    if (mysqli_num_rows($query) > 0) {
        header("Location: jenis-varietas.php?validasi=warning");
    } else {
        $insert = mysqli_query($koneksi, "INSERT INTO alternatif(kode_alternatif, nama_alternatif) VALUES('$kode', '$nama')");
        if ($insert) {
            $get_id        = mysqli_fetch_array(mysqli_query($koneksi, "SELECT id_alternatif FROM alternatif ORDER BY id_alternatif DESC LIMIT 1"));
            $id_alternatif = $get_id['id_alternatif'];

            $success = true;
            for ($i = 0; $i < count($kriteria); $i++) {
                $ins = mysqli_query($koneksi, "INSERT INTO matriks(id_alternatif, id_kriteria, id_subkriteria) VALUES('$id_alternatif', '$kriteria[$i]', '$subkriteria[$i]')");
                if (!$ins) {
                    $success = false;
                    break;
                }
            }

            if ($success) {
                header("Location: jenis-varietas.php?validasi=sukses-tambah");
            } else {
                header("Location: jenis-varietas.php?validasi=error");
            }
        } else {
            header("Location: jenis-varietas.php?validasi=error");
        }
    }
    exit;
}

// ========================================================================
// EDIT JENIS VARIEATS
// ========================================================================
if (isset($_POST['edit-alternatif'])) {
    $id          = htmlspecialchars($_POST['id']);
    $kode        = htmlspecialchars($_POST['kode']);
    $nama        = htmlspecialchars($_POST['nama']);
    $kriteria    = $_POST['kriteria'];
    $subkriteria = $_POST['subkriteria'];
 
    $update = mysqli_query($koneksi, "UPDATE alternatif SET kode_alternatif = '$kode', nama_alternatif = '$nama' WHERE id_alternatif = '$id'");
    if ($update) {
        $delete = mysqli_query($koneksi, "DELETE FROM matriks WHERE id_alternatif = '$id'");
        if ($delete) {
            $success = true;
            for ($i = 0; $i < count($kriteria); $i++) {
                $ins = mysqli_query($koneksi, "INSERT INTO matriks(id_alternatif, id_kriteria, id_subkriteria) VALUES('$id', '$kriteria[$i]', '$subkriteria[$i]')");
                if (!$ins) {
                    $success = false;
                    break;
                }
            }
 
            if ($success) {
                header("Location: jenis-varietas.php?validasi=sukses-perbarui");
                exit; // ← FIX: stop di sini kalau sukses
            } else {
                header("Location: jenis-varietas.php?validasi=error");
                exit;
            }
        } else {
            header("Location: jenis-varietas.php?validasi=error");
            exit;
        }
        // ← FIX: baris header error yang salah posisi sudah DIHAPUS dari sini
    } else {
        header("Location: jenis-varietas.php?validasi=error");
        exit;
    }
    exit;
}
 

// ========================================================================
// SIMPAN MATRIKS PERBANDINGAN AHP
// ========================================================================
if (isset($_POST['simpan_matriks'])) {

    $nilai_matriks = $_POST['matriks'];

    // Ambil semua id_kriteria dari database
    $q_krit = mysqli_query($koneksi, "SELECT id_kriteria FROM kriteria ORDER BY id_kriteria");
    $ids = [];
    while ($r = mysqli_fetch_array($q_krit)) {
        $ids[] = $r['id_kriteria'];
    }
    $n = count($ids);

    // Hapus data lama
    mysqli_query($koneksi, "DELETE FROM matriks_perbandingan");

    $success = true;
    foreach ($ids as $i => $id_i) {
        foreach ($ids as $j => $id_j) {

            if ($i == $j) {
                $nilai = 1;
            } elseif ($i < $j) {
                $nilai = floatval($nilai_matriks[$id_i][$id_j] ?? 1);
            } else {
                $nilai = 1 / floatval($nilai_matriks[$id_j][$id_i] ?? 1);
            }

            $stmt = mysqli_prepare($koneksi, "
                INSERT INTO matriks_perbandingan (id_kriteria_i, id_kriteria_j, nilai)
                VALUES (?, ?, ?)
            ");
            mysqli_stmt_bind_param($stmt, 'iid', $id_i, $id_j, $nilai);
            $exec = mysqli_stmt_execute($stmt);
            mysqli_stmt_close($stmt);

            if (!$exec) {
                $success = false;
                break 2;
            }
        }
    }

    if ($success) {
        header("Location: data-matriks-perbandingan.php?validasi=sukses");
    } else {
        header("Location: data-matriks-perbandingan.php?validasi=error");
    }
    exit;
}

// ========================================================================
// PROSES PERHITUNGAN
// ========================================================================
if (isset($_POST['hitung'])) {
    $user  = $_POST['user'];
    $pilih = isset($_POST['pilih']) ? $_POST['pilih'] : 0;

    if ($pilih == 0 || count($pilih) < 2) {
        header("Location: proses-perhitungan.php?validasi=error");
    } else {
        $cek = mysqli_query($koneksi, "SELECT * FROM checked WHERE username = '$user'");
        if (mysqli_num_rows($cek) > 0) {
            $delete = mysqli_query($koneksi, "DELETE FROM checked WHERE username = '$user'");
            if ($delete) {
                $success = true;
                for ($i = 0; $i < count($pilih); $i++) {
                    $ins = mysqli_query($koneksi, "INSERT INTO checked(id_alternatif, username) VALUES('$pilih[$i]', '$user')");
                    if (!$ins) {
                        $success = false;
                        break;
                    }
                }
                if ($success) {
                    header("Location: proses-perhitungan.php?validasi=sukses");
                } else {
                    header("Location: proses-perhitungan.php?validasi=error");
                }
            } else {
                header("Location: proses-perhitungan.php?validasi=error");
            }
        } else {
            $success = true;
            for ($i = 0; $i < count($pilih); $i++) {
                $ins = mysqli_query($koneksi, "INSERT INTO checked(id_alternatif, username) VALUES('$pilih[$i]', '$user')");
                if (!$ins) {
                    $success = false;
                    break;
                }
            }
            if ($success) {
                header("Location: proses-perhitungan.php?validasi=sukses");
            } else {
                header("Location: proses-perhitungan.php?validasi=error");
            }
        }
    }
    exit;
}

// ========================================================================
// EDIT PROFILE
// ========================================================================
if (isset($_POST['edit-profile'])) {
    $id       = htmlspecialchars($_POST['id']);
    $nama     = htmlspecialchars($_POST['nama']);
    $user     = htmlspecialchars($_POST['user']);
    $pass_old = htmlspecialchars($_POST['pass_old']);
    $pass_new = htmlspecialchars($_POST['pass_new']);
    $konfir   = htmlspecialchars($_POST['konfir']);

    if ($pass_old == "" || $pass_new == "" || $konfir == "") {
        $update = mysqli_query($koneksi, "UPDATE pengguna SET nama = '$nama', username = '$user' WHERE id_pengguna = '$id'");
        if ($update) {
            header("Location: data-profile.php?validasi=sukses");
        } else {
            header("Location: data-profile.php?validasi=error");
        }
    } else {
        $baris = mysqli_fetch_array(mysqli_query($koneksi, "SELECT * FROM pengguna WHERE id_pengguna = '$id'"));
        if (hash('sha256', $pass_old) === $baris['password']) {
            if ($pass_new === $konfir) {
                $pass_new = hash('sha256', $pass_new);
                $update   = mysqli_query($koneksi, "UPDATE pengguna SET nama = '$nama', username = '$user', password = '$pass_new' WHERE id_pengguna = '$id'");
                if ($update) {
                    header("Location: data-profile.php?validasi=sukses");
                } else {
                    header("Location: data-profile.php?validasi=error");
                }
            } else {
                header("Location: data-profile.php?validasi=error");
            }
        } else {
            header("Location: data-profile.php?validasi=error");
        }
    }
    exit;
}

// ========================================================================
// TAMBAH PENGGUNA
// ========================================================================
if (isset($_POST['tambah-pengguna'])) {
    $nama   = htmlspecialchars($_POST['nama']);
    $user   = htmlspecialchars($_POST['user']);
    $level  = htmlspecialchars($_POST['level']);
    $pass   = htmlspecialchars($_POST['pass']);
    $konfir = htmlspecialchars($_POST['konfir']);

    if ($pass == $konfir) {
        $query = mysqli_query($koneksi, "SELECT username FROM pengguna WHERE username = '$user'");
        if (mysqli_num_rows($query) > 0) {
            header("Location: data-pengguna.php?validasi=warning");
        } else {
            $hash   = hash('sha256', $pass);
            $insert = mysqli_query($koneksi, "INSERT INTO pengguna(nama, username, password, level) VALUES('$nama', '$user', '$hash', '$level')");
            if ($insert) {
                header("Location: data-pengguna.php?validasi=sukses-tambah");
            } else {
                header("Location: data-pengguna.php?validasi=error");
            }
        }
    } else {
        header("Location: data-pengguna.php?validasi=error");
    }
    exit;
}

// ========================================================================
// EDIT PENGGUNA
// ========================================================================
if (isset($_POST['edit-pengguna'])) {
    $id     = htmlspecialchars($_POST['id']);
    $nama   = htmlspecialchars($_POST['nama']);
    $user   = htmlspecialchars($_POST['user']);
    $level  = htmlspecialchars($_POST['level']);
    $pass   = htmlspecialchars($_POST['pass']);
    $konfir = htmlspecialchars($_POST['konfir']);

    if ($pass == "" && $konfir == "") {
        $update = mysqli_query($koneksi, "UPDATE pengguna SET nama = '$nama', username = '$user', level = '$level' WHERE id_pengguna = '$id'");
        if ($update) {
            header("Location: data-pengguna.php?validasi=sukses-perbarui");
        } else {
            header("Location: data-pengguna.php?validasi=error");
        }
    } else {
        if ($pass == $konfir) {
            $hash   = hash('sha256', $pass);
            $update = mysqli_query($koneksi, "UPDATE pengguna SET nama = '$nama', username = '$user', password = '$hash', level = '$level' WHERE id_pengguna = '$id'");
            if ($update) {
                header("Location: data-pengguna.php?validasi=sukses-perbarui");
            } else {
                header("Location: data-pengguna.php?validasi=error");
            }
        } else {
            header("Location: data-pengguna.php?validasi=error");
        }
    }
    exit;
}

// ========================================================================
// VERIFIKASI LUPA PASSWORD
// ========================================================================
if (isset($_POST['verif'])) {
    $nama = htmlspecialchars($_POST['nama']);
    $user = htmlspecialchars($_POST['user']);

    $verifikasi = mysqli_query($koneksi, "SELECT * FROM pengguna WHERE nama = '$nama' AND username = '$user'");
    if (mysqli_num_rows($verifikasi) > 0) {
        header("Location: ubah-pass.php?validasi=sukses&user=" . $user);
    } else {
        header("Location: lupa-pass.php?validasi=error");
    }
    exit;
}

// ========================================================================
// UBAH PASSWORD BARU
// ========================================================================
if (isset($_POST['pass-new'])) {
    $user   = htmlspecialchars($_POST['user']);
    $pass   = htmlspecialchars($_POST['pass']);
    $konfir = htmlspecialchars($_POST['konfir']);

    if ($pass === $konfir) {
        $hash   = hash('sha256', $pass);
        $update = mysqli_query($koneksi, "UPDATE pengguna SET password = '$hash' WHERE username = '$user'");
        if ($update) {
            echo "
            <script>
                alert('Ubah password berhasil!');
                document.location.href = 'masuk.php';
            </script>
            ";
            exit;
        } else {
            header("Location: ubah-pass.php?validasi=error");
        }
    } else {
        header("Location: ubah-pass.php?validasi=error");
    }
    exit;
}
?>