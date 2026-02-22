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
    <title>Varietas Karet</title>
    <link rel="icon" type="image/x-icon" href="assets/img/background.jpeg" />
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
            height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: url('assets/img/background.jpeg') no-repeat center center fixed;
            background-size: cover;
            position: relative;
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

        .login-container {
            position: relative;
            z-index: 2;
            background: rgba(255, 255, 255, 0.95);
            padding: 40px 50px;
            border-radius: 15px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.3);
            width: 100%;
            max-width: 450px;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h2 {
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

        .form-check {
            margin-bottom: 20px;
        }

        .form-check-label {
            color: #666;
            font-size: 14px;
        }

        .btn-login {
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

        .btn-login:hover {
            background: #0056b3;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 123, 255, 0.3);
        }

        .login-footer {
            text-align: center;
            margin-top: 25px;
            padding-top: 20px;
            border-top: 1px solid #eee;
        }

        .login-footer a {
            color: #007bff;
            text-decoration: none;
            font-size: 14px;
            transition: all 0.3s;
        }

        .login-footer a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .login-footer p {
            margin: 8px 0;
            color: #666;
            font-size: 14px;
        }

        .alert {
            border-radius: 8px;
            font-size: 14px;
        }

        @media (max-width: 576px) {
            .login-container {
                padding: 30px 25px;
                margin: 20px;
            }

            .login-header h2 {
                font-size: 24px;
            }
        }
    </style>
</head>

<body>
    <div class="login-container">
        <div class="login-header">
            <h2>Masuk Akun</h2>
        </div>

        <?php
        if ($validasi == "error") {
            echo "
            <div class='alert alert-danger alert-dismissible fade show' role='alert'>
                <i class='fas fa-exclamation-circle'></i> Username atau Password Anda salah!
                <button type='button' class='btn-close' data-bs-dismiss='alert' aria-label='Close'></button>
            </div>
            ";
        }
        ?>

        <form action="proses.php" method="post">
            <div class="form-group">
                <input 
                    name="user" 
                    class="form-control" 
                    type="text" 
                    placeholder="Username" 
                    pattern="[A-Za-z0-9]+" 
                    title="Tidak boleh menggunakan simbol" 
                    required 
                    autocomplete="off" 
                />
            </div>

            <div class="form-group">
                <input 
                    name="pass" 
                    class="form-control" 
                    id="pass" 
                    type="password" 
                    placeholder="Password" 
                    pattern="[^&#34;&#39;&#60;&#62;]+" 
                    minlength="5" 
                    required 
                    autocomplete="off" 
                />
            </div>

            <div class="form-check">
                <input 
                    name="view" 
                    class="form-check-input" 
                    id="view" 
                    type="checkbox" 
                />
                <label class="form-check-label" for="view">
                    Lihat Password
                </label>
            </div>

            <button type="submit" name="masuk" class="btn-login">
                Masuk
            </button>
        </form>

        <div class="login-footer">
            <p>Belum punya akun? <a href="regis.php">Klik untuk daftar</a></p>
            <p>Lupa password? <a href="lupa-pass.php">Konfirmasi disini</a></p>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script type="text/javascript">
        var view = document.getElementById('view');
        var pass = document.getElementById('pass');

        view.addEventListener('click', function() {
            if (view.checked == true) {
                pass.type = "text";
            } else {
                pass.type = "password";
            }
        });
    </script>
</body>

</html>
