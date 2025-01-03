<?php
session_start();
include 'database/db.php';

$error = "";

// Jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $login = trim($_POST['login']);
    $password = $_POST['password'];

    if (empty($login) || empty($password)) {
        $error = "Both username/email and password are required.";
    } else {
        try {
            // Cek apakah input merupakan email atau username
            if (filter_var($login, FILTER_VALIDATE_EMAIL)) {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE email = :login");
            } else {
                $stmt = $pdo->prepare("SELECT * FROM users WHERE username = :login");
            }

            $stmt->bindParam(':login', $login);
            $stmt->execute();
            $user = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($user && password_verify($password, $user['password'])) {
                // Regenerasi ID sesi untuk keamanan
                session_regenerate_id(true);

                // Simpan informasi pengguna ke dalam sesi
                $_SESSION['id_user'] = $user['id_user'];
                $_SESSION['username'] = $user['username'];
                $_SESSION['fullname'] = $user['fullname'];
                $_SESSION['photo'] = $user['photo'];
                $_SESSION['email'] = $user['email'];
                $_SESSION['phone'] = $user['phone'];
                $_SESSION['birthdate'] = $user['birthdate'];
                $_SESSION['gender'] = $user['gender'];
                $_SESSION['role'] = $user['role'];
                $_SESSION['role'] = $user['last_joined'];

                // Redirect berdasarkan peran (role)
                if ($user['role'] == 'Admin') {
                    header("Location: pages/admin/dashboard/dashboard.php");
                } else {
                    header("Location: pages/user/dashboard/dashboard.php");
                }
                exit;
            } else {
                $error = "Invalid username/email or password.";
            }
        } catch (PDOException $e) {
            error_log($e->getMessage()); // Log error untuk debugging
            $error = "An internal error occurred. Please try again later.";
        }
    }
}
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="Responsive Admin &amp; Dashboard Template based on Bootstrap 5">
    <meta name="author" content="AdminKit">
    <meta name="keywords" content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">

    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="img/icons/icon-48x48.png" />

    <link rel="canonical" href="https://demo-basic.adminkit.io/pages-sign-in.html" />

    <title>Sign In | AdminKit Demo</title>

    <link href="css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
    <style>
        /* Tambahan styling jika diperlukan */
        .alert-danger {
            color: red;
        }

    </style>
</head>

<body>
    <main class="d-flex w-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">

                        <div class="text-center mt-4">
                            <h1 class="h2">Welcome back!</h1>
                            <p class="lead">Sign in to your account to continue</p>
                        </div>

                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form method="POST" action="">
                                        <div class="mb-3">
                                            <label class="form-label">Username or Email</label>
                                            <input class="form-control form-control-lg" type="text" name="login" placeholder="Enter your username or email" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input class="form-control form-control-lg" type="password" name="password" placeholder="Enter your password" required />
                                        </div>
                                        <div>
                                            <div class="form-check align-items-center">
                                                <input id="customControlInline" type="checkbox" class="form-check-input" value="remember-me" name="remember-me" checked>
                                                <label class="form-check-label text-small" for="customControlInline">Remember me</label>
                                            </div>
                                        </div>
                                        <!-- Tampilkan pesan error jika ada -->
                                        <?php if (!empty($error)): ?>
                                            <div class="alert alert-danger mt-3" id="errorAlert">
                                                <?php echo htmlspecialchars($error); ?>
                                            </div>
                                        <?php endif; ?>
                                        <div class="d-grid gap-2 mt-3">
                                            <button type="submit" class="btn btn-lg btn-primary">Sign in</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        <div class="text-center mb-3">
                            Don't have an account? <a href="sign-up/sign-up.php">Sign up</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script> 
<script> 
 $(document).ready(function() { setTimeout(function() { $('#errorAlert').fadeOut('slow'); }, 3000); });  
 window.onbeforeunload = function() { document.getElementById('errorAlert').style.display = 'none'; }; </script>
    <script src="js/app.js"></script>
</body>

</html>
