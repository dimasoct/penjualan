<?php
// Konfigurasi database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "koperasi";

// Membuat koneksi
$conn = new mysqli($servername, $username, $password, $dbname);

// Memeriksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Fungsi untuk menghasilkan ID user acak 4 digit
function generateUserId() {
    return mt_rand(1000, 9999);
}

// Fungsi untuk memeriksa duplikasi data
function isDuplicate($username, $email, $conn) {
    $stmt = $conn->prepare("SELECT id_user FROM users WHERE username = ? OR email = ?");
    $stmt->bind_param("ss", $username, $email);
    $stmt->execute();
    $stmt->store_result();
    return $stmt->num_rows > 0;
}

// Fungsi untuk menambahkan pengguna baru
function addUser($fullname, $phone, $username, $password, $birthdate, $gender, $email, $photo, $role, $conn) {
    if (isDuplicate($username, $email, $conn)) {
        echo "Username or email already exists.";
        return;
    }

    $id_user = generateUserId();
    $hashed_password = password_hash($password, PASSWORD_BCRYPT);
    $last_joined = date("Y-m-d H:i:s");

    // Mengupload foto
    $upload_dir = '../../uploads/';
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    $photo_ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
    $new_photo_name = uniqid() . '.' . $photo_ext;
    $photo_path = $upload_dir . $new_photo_name;
    $db_photo_path = '../uploads/' . $new_photo_name;

    if (move_uploaded_file($photo['tmp_name'], $photo_path)) {
        $stmt = $conn->prepare("INSERT INTO users (id_user, fullname, phone, username, password, birthdate, gender, email, photo, role, last_joined) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("issssssssss", $id_user, $fullname, $phone, $username, $hashed_password, $birthdate, $gender, $email, $db_photo_path, $role, $last_joined);

        if ($stmt->execute()) {
            $_SESSION['photo'] = $db_photo_path;
            header("Location: users.php"); 
            exit();
        } else {
            echo "Error: " . $stmt->error;
        }

        $stmt->close();
    } else {
        echo "Failed to upload photo.";
    }
}

// Contoh penggunaan
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $phone = $_POST['phone'];
    $username = $_POST['username'];
    $password = $_POST['password'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $email = $_POST['email'];
    $photo = $_FILES['photo'];
    $role = $_POST['role'];

    addUser($fullname, $phone, $username, $password, $birthdate, $gender, $email, $photo, $role, $conn);
}

// Menutup koneksi
$conn->close();
?>

<?php include 'header.php';?>
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Add</strong> User</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                    <form method="post" enctype="multipart/form-data" action="">
                        <div>
                            <label class="form-label" for="fullname">Full Name:</label>
                            <input class="form-control form-control-lg" type="text" id="fullname" name="fullname" required><br>
                        </div>
                        <div>
                            <label class="form-label" for="username">Username:</label>
                            <input class="form-control form-control-lg" type="text" id="username" name="username" required><br>
                        </div>
                        <div>
                            <label class="form-label" for="email">Email:</label>
                            <input class="form-control form-control-lg" type="email" id="email" name="email" required><br>
                        </div>
                        <div>
                            <label class="form-label" for="phone">Phone:</label>
                            <input class="form-control form-control-lg" type="text" id="phone" name="phone" required><br>
                        </div>
                        <div>
                            <label class="form-label" for="password">Password:</label>
                            <input class="form-control form-control-lg" type="password" id="password" name="password" required><br>
                        </div>
                        <div>
                            <label class="form-label" for="birthdate">Birthdate:</label>
                            <input class="form-control form-control-lg" type="date" id="birthdate" name="birthdate" required><br>
                        </div>
                        <div>
                            <label class="form-label" for="gender">Gender:</label>
                            <select id="gender" name="gender" class="form-select mb-3" required>
                                <option value="male">Male</option>
                                <option value="female">Female</option>
                            </select><br>
                        </div>
                        <div class="m">
                            <label class="form-label" for="role">Role:</label>
                            <select id="role" name="role" class="form-select mb-3" required>
                                <option value="Admin">Admin</option>
                                <option value="User">User</option>
                            </select><br>
                        </div>
                        <div class="m">
                            <label class="form-label" for="photo">Photo:</label>
                            <input class="form-control form-control-lg" type="file" id="photo" name="photo" required><br>
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <input class="btn btn-lg btn-primary" type="submit" value="Add User">
                        </div>
                     </form>
                </div>
            </div>
        </div>
    </div>
</div>
</main>
<?php include 'footer.php';?>