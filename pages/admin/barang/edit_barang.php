<?php
include '../../../database/db.php';

function updateItem($id_barang, $nama_barang, $kategori, $harga_beli, $harga_jual) {
    global $host, $username, $password, $dbname;

    $conn = new mysqli($host, $username, $password, $dbname);

    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    $conn->begin_transaction();

    try {
        // Query untuk memperbarui data barang
        $sql = "UPDATE barang SET nama_barang = ?, kategori = ?, harga_beli = ?, harga_jual = ? WHERE id_barang = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("sssds", $nama_barang, $kategori, $harga_beli, $harga_jual, $id_barang);
        $stmt->execute();

        // Query untuk memperbarui nama_barang di tabel stok
        $sql_stok = "UPDATE stok SET nama_barang = ? WHERE id_barang = ?";
        $stmt_stok = $conn->prepare($sql_stok);
        $stmt_stok->bind_param("ss", $nama_barang, $id_barang);
        $stmt_stok->execute();

        $conn->commit();

        $stmt->close();
        $stmt_stok->close();
        $conn->close();

        // Set session untuk highlight baris yang diedit
        session_start();
        $_SESSION['last_edited_id'] = $id_barang;
        $_SESSION['status'] = 'edited';
        $_SESSION['nama_barang'] = $nama_barang;
        
        header("Location: barang.php");
        exit;

    } catch (Exception $e) {
        $conn->rollback();
        echo "Error: " . $e->getMessage();
        $conn->close();
    }
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_barang = $_POST['id_barang'];
    $nama_barang = $_POST['nama_barang'];
    $kategori = $_POST['kategori'];
    $harga_beli = $_POST['harga_beli'];
    $harga_jual = $_POST['harga_jual'];

    updateItem($id_barang, $nama_barang, $kategori, $harga_beli, $harga_jual);
    exit;
}

// Koneksi ke database
$conn = new mysqli($host, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil id_barang dari URL
$id_barang = $_GET['id_barang'];

// Query untuk mendapatkan data barang berdasarkan id_barang
$sql = "SELECT * FROM barang WHERE id_barang = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_barang);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $barang = $result->fetch_assoc();
} else {
    echo "Item not found.";
    exit;
}

$stmt->close();
$conn->close();
?>




<?php include '../header/header.php'; ?>
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Edit</strong> Item</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <form method="post" enctype="multipart/form-data" action="">
                            <input type="hidden" name="id_barang" value="<?php echo $barang['id_barang']; ?>">
                            <div class="mb-3">
                                <label class="form-label" for="id_barang">ID Barang</label>
                                <input class="form-control form-control-lg" type="text" name="id_barang" id="id_barang" value="<?php echo $barang['id_barang']; ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="nama_barang">Nama Barang</label>
                                <input class="form-control form-control-lg" type="text" name="nama_barang" id="nama_barang" value="<?php echo $barang['nama_barang']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="kategori">Kategori</label>
                                <input class="form-control form-control-lg" type="text" name="kategori" id="kategori" value="<?php echo $barang['kategori']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="harga_beli">Harga Beli</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input class="form-control form-control-lg" type="number" name="harga_beli" id="harga_beli" value="<?php echo $barang['harga_beli']; ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="harga_jual">Harga Jual</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input class="form-control form-control-lg" type="number" name="harga_jual" id="harga_jual" value="<?php echo $barang['harga_jual']; ?>" required>
                                </div>
                            </div><br>
                            <div class="d-grid gap-2 mt-3">
                                <div class="row">
                                    <div class="col">
                                        <a href="barang.php" class="btn btn-lg btn-danger w-100">Kembali</a>
                                    </div>
                                    <div class="col">
                                        <button class="btn btn-lg btn-primary w-100" type="submit">Update Item</button>
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
