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

if (!isset($_GET['id_subkriteria']) || !isset($_GET['id_kriteria'])) {
    header("Location: 404.php");
    exit();
}

$id_subkriteria = $_GET['id_subkriteria'];
$id_kriteria    = $_GET['id_kriteria'];

// Cek apakah subkriteria ini masih dipakai di tabel matriks (data varietas)
$cek_matriks = mysqli_query($koneksi, "SELECT * FROM matriks WHERE id_subkriteria = '$id_subkriteria'");
if (mysqli_num_rows($cek_matriks) > 0) {
    // Tolak penghapusan — subkriteria masih digunakan oleh data varietas
    header("Location: data-subkriteria.php?validasi=warning-digunakan");
    exit;
}

// Cek apakah ini subkriteria terakhir di kriteria ini
$cek_terakhir = mysqli_query($koneksi, "SELECT * FROM subkriteria WHERE id_kriteria = '$id_kriteria'");
if (mysqli_num_rows($cek_terakhir) == 1) {
    // Subkriteria terakhir → hapus juga kriterianya
    $delete1 = mysqli_query($koneksi, "DELETE FROM subkriteria WHERE id_subkriteria = '$id_subkriteria'");
    $delete2 = mysqli_query($koneksi, "DELETE FROM matriks WHERE id_kriteria = '$id_kriteria'");
    $delete3 = mysqli_query($koneksi, "DELETE FROM kriteria WHERE id_kriteria = '$id_kriteria'");

    if ($delete1 && $delete2 && $delete3) {
        header("Location: data-subkriteria.php?validasi=sukses-hapus");
    } else {
        header("Location: data-subkriteria.php?validasi=error");
    }
} else {
    // Masih ada subkriteria lain → hapus subkriteria ini saja
    $delete = mysqli_query($koneksi, "DELETE FROM subkriteria WHERE id_subkriteria = '$id_subkriteria'");

    if ($delete) {
        header("Location: data-subkriteria.php?validasi=sukses-hapus");
    } else {
        header("Location: data-subkriteria.php?validasi=error");
    }
}
exit;
?>