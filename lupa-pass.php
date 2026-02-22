<?php
session_start();
if (isset($_SESSION['nama']) && isset($_SESSION['level'])) {
    header("Location: menu-utama.php?validasi=sukses");
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
    <title>Lupa Password - Varietas Karet</title>
    <link rel="icon" type="image/x-icon" href="assets/img/logo.png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <script src="https://use.fontawesome.com/releases/v6.1.0/js/all.js" crossorigin="anonymous"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('assets/img/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
            padding: 20px 0;
        }

        body::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.3);
            z-index: 1;
        }

        .forgot-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 50px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
            margin: 20px;
        }

        .forgot-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .forgot-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 10px;
        }

        .forgot-header p {
            color: #666;
            font-size: 14px;
            margin-bottom: 0;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-control {
            height: 50px;
            border: 1px solid #ddd;
            border-radius: 8px;
            padding: 10px 15px;
            font-size: 15px;
            transition: all 0.3s;
        }

        .form-control:focus {
            border-color: #007bff;
            box-shadow: 0 0 0 0.2rem rgba(0, 123, 255, 0.25);
        }

        .btn-forgot {
            width: 100%;
            height: 50px;
            background: #007bff;
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s;
            cursor: pointer;
        }

        .btn-forgot:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .forgot-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .forgot-footer a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .forgot-footer a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .forgot-footer p {
            margin: 8px 0;
            color: #666;
            font-size: 14px;
        }

        .alert {
            border-radius: 8px;
            font-size: 14px;
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            color: #555;
            font-size: 14px;
            font-weight: 500;
        }

        .info-box {
            background: #e3f2fd;
            border-left: 4px solid #2196f3;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 25px;
        }

        .info-box i {
            color: #2196f3;
            margin-right: 8px;
        }

        .info-box p {
            margin: 0;
            color: #1976d2;
            font-size: 14px;
        }

        @media (max-width: 576px) {
            .forgot-container {
                padding: 30px 25px;
                margin: 20px 15px;
            }

            .forgot-header h2 {
                font-size: 24px;
            }

            .form-control {
                height: 45px;
                font-size: 14px;
            }

            .btn-forgot {
                height: 45px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="forgot-container">
        <div class="forgot-header">
            <h2>Lupa Password?</h2>
            <p>Verifikasi akun Anda untuk melanjutkan</p>
        </div>

        <div class="info-box">
            <i class="fas fa-info-circle"></i>
            <p>Masukkan nama lengkap dan username yang terdaftar untuk memverifikasi akun Anda.</p>
        </div>

        <?php
        if ($validasi == "error") {
            echo "
            <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle'></i> <strong>Verifikasi Gagal!</strong> Nama lengkap dan username tidak valid atau tidak cocok.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
            ";
        }
        ?>

        <form action="proses.php" method="post">
            <div class="form-group">
                <label for="nama">Nama Lengkap</label>
                <input 
                    name="nama" 
                    class="form-control" 
                    id="nama" 
                    type="text" 
                    placeholder="Masukkan nama lengkap" 
                    pattern="[A-Za-z ]+" 
                    title="Inputan hanya boleh huruf dan spasi" 
                    required 
                    autocomplete="off" 
                />
            </div>

            <div class="form-group">
                <label for="username">Username</label>
                <input 
                    name="user" 
                    class="form-control" 
                    id="username" 
                    type="text" 
                    placeholder="Masukkan username" 
                    pattern="[A-Za-z0-9]+" 
                    title="Tidak boleh menggunakan simbol" 
                    required 
                    autocomplete="off" 
                />
            </div>

            <button type="submit" name="verif" class="btn-forgot">
                <i class="fas fa-check-circle"></i> Verifikasi Akun
            </button>
        </form>

        <div class="forgot-footer">
            <p>Sudah ingat password? <a href="masuk.php">Kembali ke login</a></p>
            <p>Belum punya akun? <a href="regis.php">Daftar sekarang</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>