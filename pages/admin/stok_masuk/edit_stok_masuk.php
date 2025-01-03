<?php
// Cek apakah id_stok_masuk ada dalam URL
if (isset($_GET['id_stok_masuk'])) {
    $id_stok_masuk = $_GET['id_stok_masuk'];
} else {
    echo "ID Stok Masuk tidak ditemukan!";
    exit();
}

// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query untuk mendapatkan data stok masuk berdasarkan id_stok_masuk
$sql = "SELECT sm.*, b.nama_barang, b.id_barang, b.harga_beli, s.stok as current_stok 
        FROM stok_masuk sm
        JOIN barang b ON sm.id_barang = b.id_barang 
        JOIN stok s ON b.id_barang = s.id_barang
        WHERE sm.id_stok_masuk = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $id_stok_masuk); 
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
} else {
    echo "Data tidak ditemukan!";
    exit();
}

$stmt->close();

// Proses Update saat form disubmit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Ambil data dari form
    $stok_masuk = $_POST['stok_masuk'];
    $tanggal_masuk = $_POST['tanggal_masuk'];
    $asal_barang = $_POST['asal_barang'];
    
    // Ambil stok lama yang ada pada tabel stok_masuk
    $stok_lama = $row['stok_masuk'];

    // Hitung perubahan stok
    $perubahan_stok = $stok_masuk - $stok_lama;

    // Query untuk update data stok masuk
    $update_sql = "UPDATE stok_masuk 
                   SET stok_masuk = ?, tanggal_masuk = ?, asal_barang = ? 
                   WHERE id_stok_masuk = ?";
    $update_stmt = $conn->prepare($update_sql);
    $update_stmt->bind_param("isss", $stok_masuk, $tanggal_masuk, $asal_barang, $id_stok_masuk); // Corrected binding parameters

    if ($update_stmt->execute()) {
        // Update stok pada tabel stok
        $update_stok_sql = "UPDATE stok 
                            SET stok = stok + ? 
                            WHERE id_barang = ?";
        $update_stok_stmt = $conn->prepare($update_stok_sql);
        $update_stok_stmt->bind_param("is", $perubahan_stok, $row['id_barang']);
        
        if ($update_stok_stmt->execute()) {
            // Redirect setelah berhasil
            header("Location: stok_masuk.php"); 
            exit();
        } else {
            echo "Gagal memperbarui stok: " . $conn->error;
        }

        $update_stok_stmt->close();
    } else {
        echo "Gagal memperbarui data stok masuk: " . $conn->error;
    }

    $update_stmt->close();
}

$conn->close();
?>

<?php include '../header/header.php'; ?>
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Edit</strong> Stok Masuk</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <form method="post" enctype="multipart/form-data" action="">
                            <!-- ID Barang (Disabled) -->
                            <div class="mb-3">
                                <label class="form-label" for="id_barang">ID Barang</label>
                                <input class="form-control form-control-lg" type="text" id="id_barang" name="id_barang" value="<?php echo htmlspecialchars($row['id_barang']); ?>" disabled>
                            </div>

                            <!-- Nama Barang (Disabled) -->
                            <div class="mb-3">
                                <label class="form-label" for="nama_barang">Nama Barang</label>
                                <input class="form-control form-control-lg" type="text" id="nama_barang" name="nama_barang" value="<?php echo htmlspecialchars($row['nama_barang']); ?>" disabled>
                            </div>

                            <!-- Jumlah Stok Masuk -->
                            <div class="mb-3">
                                <label class="form-label" for="stok_masuk">Stok Masuk</label>
                                <input class="form-control form-control-lg" type="number" id="stok_masuk" name="stok_masuk" value="<?php echo htmlspecialchars($row['stok_masuk']); ?>" required>
                            </div>

                            <!-- Total Stok -->
                            <div class="mb-3">
                                <label class="form-label" for="total_stok">Total Stok</label>
                                <input class="form-control form-control-lg" type="number" id="total_stok" name="total_stok" value="<?php echo ($row['current_stok'] - $row['stok_masuk']) + $row['stok_masuk']; ?>" disabled>
                            </div>

                            <!-- Harga Beli (Readonly) -->
                            <div class="mb-3">
                                <label class="form-label" for="harga_beli">Harga Beli</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input class="form-control form-control-lg" type="number" step="1" min="0" id="harga_beli" name="harga_beli" value="<?php echo (int)$row['harga_beli']; ?>" readonly>
                                </div>
                            </div>

                            <!-- Total Harga Beli -->
                            <div class="mb-3">
                                <label class="form-label" for="total_harga_beli">Total Harga Beli</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input class="form-control form-control-lg" type="number" step="1" min="0" id="total_harga_beli" name="total_harga_beli" value="<?php echo (int)($row['harga_beli'] * $row['stok_masuk']); ?>" readonly>
                                </div>
                            </div>

                            <!-- Tanggal Masuk -->
                            <div class="mb-3">
                                <label class="form-label" for="tanggal_masuk">Tanggal Masuk</label>
                                <input class="form-control form-control-lg" type="date" id="tanggal_masuk" name="tanggal_masuk" value="<?php echo htmlspecialchars($row['tanggal_masuk']); ?>" required>
                            </div>

                            <!-- Asal Barang -->
                            <div class="mb-3">
                                <label class="form-label" for="asal_barang">Asal Barang</label>
                                <input class="form-control form-control-lg" type="text" id="asal_barang" name="asal_barang" value="<?php echo htmlspecialchars($row['asal_barang']); ?>" required>
                            </div><br>

                            <div class="d-grid gap-2 mt-3">
                                <div class="row">
                                    <div class="col">
                                        <a href="stok_masuk.php" class="btn btn-lg btn-danger w-100">Kembali</a>
                                    </div>
                                    <div class="col">
                                        <button class="btn btn-lg btn-primary w-100" type="submit">Update Stok Masuk</button>
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

<script>
// Calculate total stock when stok_masuk changes
document.getElementById('stok_masuk').addEventListener('input', function() {
    var currentStok = <?php echo $row['current_stok']; ?>;
    var stokMasuk = parseInt(this.value) || 0;
    var oldStokMasuk = <?php echo $row['stok_masuk']; ?>;
    document.getElementById('total_stok').value = (currentStok - oldStokMasuk) + stokMasuk;
    
    // Calculate total harga beli
    var hargaBeli = parseInt(document.getElementById('harga_beli').value) || 0;
    document.getElementById('total_harga_beli').value = hargaBeli * stokMasuk;
});
</script>
