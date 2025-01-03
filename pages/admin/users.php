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

// Query untuk mengambil data semua pengguna
$sql_users = "SELECT id_user, fullname, username, email, phone, birthdate, gender, photo, last_joined, role FROM users";
$result_users = $conn->query($sql_users);

// Logika penghapusan data jika parameter id_user ada di URL dan hanya melalui konfirmasi modal
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['confirm_delete']) && isset($_POST['id_user'])) {
    $id_user = $_POST['id_user'];
    $delete_sql = "DELETE FROM users WHERE id_user = ?";
    $stmt = $conn->prepare($delete_sql);
    $stmt->bind_param("i", $id_user);
    if ($stmt->execute()) {
        header("Location: users.php?success=deleted"); // Redirect jika berhasil
        exit;
    } else {
        header("Location: users.php?error=delete_failed"); // Redirect jika gagal
        exit;
    }
    $stmt->close();
}
?>
<?php include 'header.php';?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Management</strong> User</h1>

        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <a class="btn btn-primary col-auto mt-0" href="add_user.php">Tambah User</a>
                        <!-- Tambahkan wrapper dengan kelas table-responsive -->
                        <div class="table-responsive">
                            <table class="table table-hover my-0">
                                <thead>
                                    <tr>
                                        <th>Full Name</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Phone</th>
                                        <th>Birthdate</th>
                                        <th>Gender</th>
                                        <th>Photo</th>
                                        <th>Role</th>
                                        <th>Last Joined</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_users->num_rows > 0): ?>
                                        <?php while ($row = $result_users->fetch_assoc()): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($row['fullname']); ?></td>
                                                <td><?php echo htmlspecialchars($row['username']); ?></td>
                                                <td><?php echo htmlspecialchars($row['email']); ?></td>
                                                <td><?php echo htmlspecialchars($row['phone']); ?></td>
                                                <td><?php echo htmlspecialchars($row['birthdate']); ?></td>
                                                <td><?php echo htmlspecialchars($row['gender']); ?></td>
                                                <td>
                                                    <img src="../<?php echo htmlspecialchars($row['photo']); ?>" alt="Photo" 
                                                        class="rounded mb-3 center-crop" 
                                                        width="80" 
                                                        height="80">
                                                </td>
                                                <td><?php echo htmlspecialchars($row['role']); ?></td>
                                                <td><?php echo htmlspecialchars($row['last_joined']); ?></td>
                                                <td>
                                                    <a href="../admin/edit_user.php?id_user=<?php echo htmlspecialchars($row['id_user']); ?>" class="btn btn-primary btn-sm mb-3"><i class="align-middle" data-feather="edit"></i></a>
                                                    <button class="btn btn-danger btn-sm mb-3" onclick="deleteUser(<?php echo htmlspecialchars($row['id_user']); ?>)">
                                                        <i class="align-middle" data-feather="trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10">No data found</td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <!-- End of table-responsive -->
                </div>
            </div>
        </div>
    </div>
</main>
<?php include 'footer.php';?>
