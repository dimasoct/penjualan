<?php
function generateRandomID() {
    return "KT-" . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
}

function addCategory($nama_kategori) {
    include '../../../database/db.php';

    $conn = new mysqli($host, $username, $password, $dbname);

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Generate a random 4-digit ID for category
    $id_kategori = generateRandomID();

    // Query to insert the new category into the `kategori_barang` table
    $sql = "INSERT INTO kategori_barang (id_kategori, nama_kategori) VALUES (?, ?)";

    // Prepare the statement and bind parameters
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $id_kategori, $nama_kategori);

    if ($stmt->execute()) {
        header("Location: kategori.php");  // Redirect to category management page after successful insertion
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_kategori = $_POST['nama_kategori'];

    // Call the function to add the category to the database
    addCategory($nama_kategori);
}

$id_kategori = generateRandomID();  // Generate an ID for the new category

?>
<?php include '../header/header.php'; ?>
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Add</strong> Category</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <form method="post" enctype="multipart/form-data" action="">
                            <div class="mb-3">
                                <label class="form-label" for="id_kategori">ID Kategori</label>
                                <input class="form-control form-control-lg" type="text" id="id_kategori" name="id_kategori" value="<?php echo $id_kategori; ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="nama_kategori">Nama Kategori</label>
                                <input class="form-control form-control-lg" type="text" id="nama_kategori" name="nama_kategori" required>
                            </div><br>

                            <div class="d-grid gap-2 mt-3">
                                <div class="row">
                                    <div class="col">
                                        <a href="kategori.php" class="btn btn-lg btn-danger w-100">Kembali</a>
                                    </div>
                                    <div class="col">
                                        <input class="btn btn-lg btn-primary w-100" type="submit" value="Add Category">
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
