<?php
ini_set('memory_limit', '2G'); // Mengatur batas memori menjadi 2GB
include '../database/db.php';

$error = "";
$success = "";

// Fungsi untuk menghasilkan 4 digit angka acak
function generateRandomID() {
    return str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
}

// Jika form dikirim
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $email = $_POST['email'];
    $phone = $_POST['phone'] ?? null; // Nilai default null jika tidak diisi
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $photo = $_FILES['photo'] ?? null;

    // Validasi input
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Invalid email format.";
    } elseif ($password !== $confirm_password) {
        $error = "Passwords do not match.";
    } else {
        // Hash password untuk keamanan
        $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

        // Path untuk menyimpan foto
        $photoPath = null;
        if ($photo && $photo['error'] == 0) {
            $uploadDir = '../uploads/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0777, true); // Buat direktori jika belum ada
            }
            // Buat nama file acak
            $uniqueName = bin2hex(random_bytes(16)); // Menghasilkan nama file acak
            $extension = pathinfo($photo['name'], PATHINFO_EXTENSION); // Mendapatkan ekstensi file
            $photoPath = $uploadDir . $uniqueName . '.' . $extension; // Membuat path lengkap dengan nama acak
            if (!move_uploaded_file($photo['tmp_name'], $photoPath)) {
                $error = "Error uploading file.";
            }
        }

        if (!$error) {
            // Simpan data pengguna ke database
            try {
                $role = "User"; // Tetapkan role default "user"
                $id_user = generateRandomID(); // Hasilkan id_user dengan 4 digit angka acak

                $stmt = $pdo->prepare("INSERT INTO users (id_user, fullname, username, email, phone, password, birthdate, gender, photo, role, last_joined) VALUES (:id_user, :fullname, :username, :email, :phone, :password, :birthdate, :gender, :photo, :role, NOW())");
                $stmt->bindParam(':id_user', $id_user);
                $stmt->bindParam(':fullname', $fullname);
                $stmt->bindParam(':username', $username);
                $stmt->bindParam(':email', $email);
                $stmt->bindParam(':phone', $phone);
                $stmt->bindParam(':password', $hashedPassword);
                $stmt->bindParam(':birthdate', $birthdate);
                $stmt->bindParam(':gender', $gender);
                $stmt->bindParam(':photo', $photoPath);
                $stmt->bindParam(':role', $role);
                $stmt->execute();
                $success = "Registration successful!";
            } catch (PDOException $e) {
                $error = "Error: " . $e->getMessage();
            }
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
    <meta name="description" content="Responsive Admin & Dashboard Template based on Bootstrap 5">
    <meta name="author" content="AdminKit">
    <meta name="keywords" content="adminkit, bootstrap, bootstrap 5, admin, dashboard, template, responsive, css, sass, html, theme, front-end, ui kit, web">
    <link rel="preconnect" href="https://fonts.gstatic.com">
    <link rel="shortcut icon" href="img/icons/icon-48x48.png" />
    <link rel="canonical" href="https://demo-basic.adminkit.io/pages-sign-up.html" />
    <title>Sign Up | AdminKit Demo</title>
    <link href="../css/app.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap" rel="stylesheet">
</head>
<body>
    <main class="d-flex w-100">
        <div class="container d-flex flex-column">
            <div class="row vh-100">
                <div class="col-sm-10 col-md-8 col-lg-6 col-xl-5 mx-auto d-table h-100">
                    <div class="d-table-cell align-middle">
                        <div class="text-center mt-4">
                            <h1 class="h2">Get started</h1>
                            <p class="lead">
                                Start creating the best possible user experience for you customers.
                            </p>
                        </div>
                        <div class="card">
                            <div class="card-body">
                                <div class="m-sm-3">
                                    <form action="" method="POST" enctype="multipart/form-data">
                                        <div class="mb-3">
                                            <label class="form-label">Full name</label>
                                            <input class="form-control form-control-lg" type="text" name="fullname" placeholder="Enter your name" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Username</label>
                                            <input class="form-control form-control-lg" type="text" name="username" placeholder="Enter your username" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Email</label>
                                            <input class="form-control form-control-lg" type="email" name="email" placeholder="Enter your email" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Number Phone</label>
                                            <input class="form-control form-control-lg" type="text" name="phone" placeholder="Enter your phone" />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Password</label>
                                            <input class="form-control form-control-lg" type="password" name="password" placeholder="Enter password" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Confirm Password</label>
                                            <input class="form-control form-control-lg" type="password" name="confirm_password" placeholder="Confirm password" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Date of Birth</label>
                                            <input class="form-control form-control-lg" type="date" name="birthdate" placeholder="Confirm Date of Birth" required />
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Select Gender</label>
                                            <select name="gender" class="form-select mb-3" required>
                                                <option value="male">Male</option>
                                                <option value="female">Female</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Upload Photo</label>
                                            <input class="form-control form-control-lg" type="file" name="photo" accept="image/*">
                                        </div>
                                        <div class="d-grid gap-2 mt-3">
                                            <button class="btn btn-lg btn-primary" type="submit">Sign up</button>
                                        </div>
                                        <?php if ($error): ?>
                                            <div class="alert alert-danger mt-3" id="errorAlert">
                                                <?php echo htmlspecialchars($error); ?>
                                            </div>
                                        <?php endif; ?>
                                        <?php if ($success): ?>
                                            <div class="alert alert-success mt-3" id="successAlert">
                                                <?php echo $success; ?> <a href='login.php'>Login</a>
                                            </div>
                                        <?php endif; ?>
                                        
                                    </form>
                                </div>
                                <div class="text-center mb-3">
                                    Already have account? <a href="../index.php">Log In</a>
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </main>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="../js/app.js"></script>
    <script>
        $(document).ready(function() {
            setTimeout(function() {
                $('#errorAlert').fadeOut('slow');
            }, 3000);

            setTimeout(function() {
                $('#successAlert').fadeOut('slow');
            }, 5000);
        });

        window.onbeforeunload = function() {
            document.getElementById('errorAlert').style.display = 'none';
            document.getElementById('successAlert').style.display = 'none';
        };
    </script>
</body>
</html