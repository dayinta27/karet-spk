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
    <title>Registrasi - Varietas Karet</title>
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

        .register-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 50px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 500px;
            margin: 20px;
        }

        .register-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .register-header h2 {
            font-size: 28px;
            font-weight: 700;
            color: #333;
            margin-bottom: 5px;
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

        .btn-register {
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

        .btn-register:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .register-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .register-footer a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .register-footer a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .register-footer p {
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

        @media (max-width: 576px) {
            .register-container {
                padding: 30px 25px;
                margin: 20px 15px;
            }

            .register-header h2 {
                font-size: 24px;
            }

            .form-control {
                height: 45px;
                font-size: 14px;
            }

            .btn-register {
                height: 45px;
                font-size: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="register-container">
        <div class="register-header">
            <h2>Daftar Akun</h2>
        </div>

        <?php
        if ($validasi == "sukses") {
            echo "
            <div class='alert alert-success alert-dismissible fade show' role='alert'>
                <i class='fas fa-check-circle'></i> <strong>Registrasi Berhasil!</strong> Silahkan login menggunakan akun Anda.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
            ";
        } else if ($validasi == "error") {
            echo "
            <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle'></i> <strong>Registrasi Gagal!</strong> Silahkan melakukan registrasi kembali.
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
            ";
        } else if ($validasi == "warning") {
            echo "
            <div class='alert alert-warning alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-triangle'></i> <strong>Username telah digunakan!</strong> Silahkan gunakan username yang berbeda.
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

            <div class="form-group">
                <label for="password">Password</label>
                <input 
                    name="pass" 
                    class="form-control" 
                    id="password" 
                    type="password" 
                    placeholder="Masukkan password (minimal 5 karakter)" 
                    pattern="[^&#34;&#39;&#60;&#62;]+" 
                    minlength="5" 
                    required 
                    autocomplete="off" 
                />
            </div>

            <div class="form-group">
                <label for="konfirmasi">Konfirmasi Password</label>
                <input 
                    name="konfir" 
                    class="form-control" 
                    id="konfirmasi" 
                    type="password" 
                    placeholder="Masukkan ulang password" 
                    pattern="[^&#34;&#39;&#60;&#62;]+" 
                    minlength="5" 
                    required 
                    autocomplete="off" 
                />
            </div>

            <button type="submit" name="regis" class="btn-register">
                Daftar
            </button>
        </form>

        <div class="register-footer">
            <p>Sudah punya akun? <a href="masuk.php">Klik untuk masuk</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
</body>

</html>
