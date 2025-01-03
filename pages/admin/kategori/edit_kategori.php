<?php
function updateCategory($id_kategori, $nama_kategori) {
    // Koneksi ke database
    include '../../../database/db.php';

    $conn = new mysqli($host, $username, $password, $dbname);

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Query untuk memperbarui data kategori
    $sql = "UPDATE kategori_barang SET nama_kategori = ? WHERE id_kategori = ?";

    $stmt = $conn->prepare($sql);
    // Bind parameter hanya untuk yang ada di dalam query (2 parameter)
    $stmt->bind_param("ss", $nama_kategori, $id_kategori); // Use 's' for both parameters (string)

    if ($stmt->execute()) {
        $stmt->close();
        $conn->close();
        header("Location: kategori.php?success=updated");
        exit;
    } else {
        $stmt->close();
        $conn->close();
        echo "Error: " . $conn->error;
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_kategori = $_POST['id_kategori'];
    $nama_kategori = $_POST['nama_kategori'];

    updateCategory($id_kategori, $nama_kategori);
    exit;
}
?>

<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil id_kategori dari URL
$id_kategori = $_GET['id_kategori'];

// Query untuk mendapatkan data kategori berdasarkan id_kategori
$sql = "SELECT * FROM kategori_barang WHERE id_kategori = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_kategori); // Use 's' for id_kategori (string)
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $kategori = $result->fetch_assoc();
} else {
    echo "Category not found.";
    exit;
}

$stmt->close();
$conn->close();
?>

<?php include '../header/header.php'; ?>
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Edit</strong> Category</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <form method="post" enctype="multipart/form-data" action="">
                            <input type="hidden" name="id_kategori" value="<?php echo $kategori['id_kategori']; ?>">
                            <div class="mb-3">
                                <label class="form-label" for="id_kategori">ID Kategori</label>
                                <input class="form-control form-control-lg" type="text" name="id_kategori" id="id_kategori" value="<?php echo $kategori['id_kategori']; ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="nama_kategori">Nama Kategori</label>
                                <input class="form-control form-control-lg" type="text" name="nama_kategori" id="nama_kategori" value="<?php echo $kategori['nama_kategori']; ?>" required>
                            </div><br>
                            <div class="d-grid gap-2 mt-3">
                                <div class="row">
                                    <div class="col">
                                        <a href="kategori.php" class="btn btn-lg btn-danger w-100">Kembali</a>
                                    </div>
                                    <div class="col">
                                        <button class="btn btn-lg btn-primary w-100" type="submit">Update Category</button>
                                    </div>
                                </div>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </div>
    </div>
</main>
<?php include '../footer/footer.php'; ?>
