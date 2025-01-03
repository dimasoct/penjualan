<?php

// Koneksi ke database
$host = "localhost";
$username = "root";
$password = "";
$dbname = "koperasi";

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Periksa apakah `id_user` ada di URL
if (isset($_GET['id_user'])) {
    $id_user = $_GET['id_user'];

    // Query untuk mendapatkan data pengguna berdasarkan `id_user`
    $stmt = $conn->prepare("SELECT * FROM users WHERE id_user = ?");
    $stmt->bind_param("i", $id_user);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
    } else {
        echo "User not found.";
        exit;
    }
} else {
    echo "Invalid request.";
    exit;
}
// Jika form dikirim, proses pembaruan data
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $fullname = $_POST['fullname'];
    $username = $_POST['username'];
    $phone = $_POST['phone'];
    $birthdate = $_POST['birthdate'];
    $gender = $_POST['gender'];
    $role = $_POST['role'];

 // Proses upload file
if (isset($_FILES['photo']) && $_FILES['photo']['error'] == 0) {
    $photo = $_FILES['photo'];
    $upload_dir = '../../uploads/';
    $db_dir = '../uploads/';

    // Cek dan buat folder uploads jika belum ada
    if (!file_exists($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    $photo_ext = pathinfo($photo['name'], PATHINFO_EXTENSION);
    $new_photo_name = uniqid() . '.' . $photo_ext;
    $photo_path = $upload_dir . $new_photo_name;
    $db_photo_path = $db_dir . $new_photo_name;

    if (move_uploaded_file($photo['tmp_name'], $photo_path)) {
        // Hapus file foto lama jika ada
        if (!empty($user['photo']) && file_exists($user['photo'])) {
            unlink($user['photo']);
        }

        // Update path foto di database
        $update_stmt = $conn->prepare("UPDATE users SET photo = ? WHERE id_user = ?");
        $update_stmt->bind_param("si", $db_photo_path, $id_user);
        $update_stmt->execute();
    } else {
        echo "Failed to upload photo.";
        exit;
    }
}


    // Query untuk memperbarui data pengguna
    $update_stmt = $conn->prepare("UPDATE users SET fullname = ?, username = ?, phone = ?, birthdate = ?, gender = ?, role = ? WHERE id_user = ?");
    $update_stmt->bind_param("ssssssi", $fullname, $username, $phone, $birthdate, $gender, $role, $id_user);

    if ($update_stmt->execute()) {
        echo "User updated successfully.";
        header("Location: users.php"); // Redirect kembali ke halaman users
        exit;
    } else {
        echo "Error: " . $conn->error;
    }
}
?>
<?php include 'header.php';?>
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Edit</strong> User</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                    <form action="" method="post" enctype="multipart/form-data">
                        <div class="mb-3">
                            <label class="form-label" for="fullname">Full Name</label>
                            <input class="form-control form-control-lg" type="text" name="fullname" id="fullname" value="<?php echo $user['fullname']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="username">Username</label>
                            <input class="form-control form-control-lg" type="text" name="username" id="username" value="<?php echo $user['username']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="phone">Number Phone</label>
                            <input class="form-control form-control-lg" type="text" name="phone" id="phone" value="<?php echo $user['phone']; ?>">
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="birthdate">Date of Birth</label>
                            <input class="form-control form-control-lg" type="date" name="birthdate" id="birthdate" value="<?php echo $user['birthdate']; ?>" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="gender">Select Gender</label>
                            <select name="gender" id="gender" class="form-select mb-3" required>
                                <option value="male" <?php echo ($user['gender'] == 'male') ? 'selected' : ''; ?>>Male</option>
                                <option value="female" <?php echo ($user['gender'] == 'female') ? 'selected' : ''; ?>>Female</option>
                                <option value="other" <?php echo ($user['gender'] == 'other') ? 'selected' : ''; ?>>Other</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label" for="role">Role</label>
                            <select name="role" id="fole" class="form-select mb-3" required>
                                <option value="Admin" <?php echo ($user['role'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                                <option value="User" <?php echo ($user['role'] == 'User') ? 'selected' : ''; ?>>User</option>
                            </select>
                        <div class="mb-3">
                            <label class="form-label" for="photo">Upload Photo</label>
                            <input class="form-control form-control-lg" type="file" name="photo" id="photo" accept="image/*">
                        </div>
                        <div class="d-grid gap-2 mt-3">
                            <button class="btn btn-lg btn-primary" type="submit">Update User</button>
                        </div>
                    </form>

                </div>
            </div>
        </div>
    </div>
</div>
</main>
<?php include 'footer.php';?>

