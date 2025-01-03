<?php
function generateRandomID() {
    return "BR-" . str_pad(rand(0, 9999), 4, '0', STR_PAD_LEFT);
}

function addItem($nama_barang, $kategori, $harga_beli, $harga_jual) {
    include '../../../database/db.php';

    $conn = new mysqli($host, $username, $password, $dbname);

    // Periksa koneksi
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }

    // Generate a random 4-digit ID
    $id_barang = generateRandomID();

    // Query untuk menambahkan data ke tabel `barang`
    $sql_barang = "INSERT INTO barang (id_barang, nama_barang, kategori, harga_beli, harga_jual) VALUES (?, ?, ?, ?, ?)";
    
    // Query untuk menambahkan data ke tabel `stok` (stok awalnya di-set 0)
    $sql_stok = "INSERT INTO stok (id_barang, nama_barang, stok) VALUES (?, ?, 0)";

    // Menggunakan 's' untuk id_barang karena itu adalah string, 's' untuk nama_barang dan kategori (string), dan 'i' untuk harga_beli dan harga_jual (integer)
    $stmt_barang = $conn->prepare($sql_barang);
    $stmt_barang->bind_param("ssssi", $id_barang, $nama_barang, $kategori, $harga_beli, $harga_jual);  // Ganti tipe bind_param ke 'ssssi'

    $stmt_stok = $conn->prepare($sql_stok);
    $stmt_stok->bind_param("ss", $id_barang, $nama_barang); // Menambahkan data stok ke tabel stok

    // Eksekusi query untuk memasukkan data barang
    if ($stmt_barang->execute() && $stmt_stok->execute()) {
        // Set session untuk notifikasi
        session_start();
        $_SESSION['status'] = 'added';
        $_SESSION['nama_barang'] = $nama_barang;
        $_SESSION['last_added_id'] = $id_barang; // Tambahkan session untuk ID barang yang baru ditambahkan
        
        header("Location: barang.php");
        exit();
    } else {
        echo "Error: " . $conn->error;
    }

    $stmt_barang->close();
    $stmt_stok->close();
    $conn->close();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $nama_barang = $_POST['nama_barang'];
    $kategori = $_POST['kategori'];
    $harga_beli = $_POST['harga_beli'];
    $harga_jual = $_POST['harga_jual'];

    addItem($nama_barang, $kategori, $harga_beli, $harga_jual);
}

$id_barang = generateRandomID();  // ID barang dihasilkan sebelum form

// Query untuk mengambil kategori dari tabel kategori_barang
include '../../../database/db.php';
$conn = new mysqli($host, $username, $password, $dbname);
$sql_kategori = "SELECT id_kategori, nama_kategori FROM kategori_barang";
$result_kategori = $conn->query($sql_kategori);
?>

<?php include '../header/header.php'; ?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Add</strong> Item</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <form method="post" enctype="multipart/form-data" action="">
                            <div class="mb-3">
                                <label class="form-label" for="id_barang">ID Barang</label>
                                <input class="form-control form-control-lg" type="text" id="id_barang" name="id_barang" value="<?php echo $id_barang; ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="nama_barang">Nama Barang</label>
                                <input class="form-control form-control-lg" type="text" id="nama_barang" name="nama_barang" required>
                            </div>

                            <!-- Dropdown Kategori -->
                            <div class="mb-3">
                                <label class="form-label" for="kategori">Kategori</label>
                                <select class="form-select form-select-lg" id="kategori" name="kategori" required>
                                    <option value="">Pilih Kategori</option>
                                    <?php if ($result_kategori->num_rows > 0): ?>
                                        <?php while ($row = $result_kategori->fetch_assoc()): ?>
                                            <option value="<?php echo htmlspecialchars($row['id_kategori']); ?>">
                                                <?php echo htmlspecialchars($row['nama_kategori']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option value="" disabled>No kategori found</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="harga_beli">Harga Beli</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input class="form-control form-control-lg" type="number" id="harga_beli" name="harga_beli" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="harga_jual">Harga Jual</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input class="form-control form-control-lg" type="number" id="harga_jual" name="harga_jual" required>
                                </div>
                            </div><br>
                            <div class="d-grid gap-2 mt-3">
                                <div class="row">
                                    <div class="col">
                                        <a href="barang.php" class="btn btn-lg btn-danger w-100">Kembali</a>
                                    </div>
                                    <div class="col">
                                        <input class="btn btn-lg btn-primary w-100" type="submit" value="Add Item">
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
