<?php
ob_start();

// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil data barang untuk ditampilkan di dropdown
$sql_barang = "SELECT id_barang, nama_barang, harga_beli, harga_jual, stok_barang FROM barang";
$result_barang = $conn->query($sql_barang);

$alert_message = "";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Generate ID transaksi acak 4 digit dan pastikan unik
    do {
        $id_transaksi = rand(1000, 9999);
        $query_check_id = "SELECT COUNT(*) FROM transaksi WHERE id_transaksi = ?";
        $stmt_check = $conn->prepare($query_check_id);
        $stmt_check->bind_param("i", $id_transaksi);
        $stmt_check->execute();
        $stmt_check->bind_result($count);
        $stmt_check->fetch();
        $stmt_check->close();
    } while ($count > 0);

    $id_barang = $_POST['id_barang'];
    $tanggal_penjualan = $_POST['tanggal_penjualan'];
    $jumlah_terjual = $_POST['jumlah_terjual'];

    // Ambil harga_beli, harga_jual, dan stok_barang dari tabel barang
    $query_barang = "SELECT harga_beli, harga_jual, stok_barang FROM barang WHERE id_barang = ?";
    $stmt_barang = $conn->prepare($query_barang);
    $stmt_barang->bind_param("i", $id_barang);
    $stmt_barang->execute();
    $stmt_barang->bind_result($harga_beli, $harga_jual, $stok_barang);
    $stmt_barang->fetch();
    $stmt_barang->close();

    if ($harga_beli !== null && $harga_jual !== null) {
        if ($stok_barang >= $jumlah_terjual) {
            // Masukkan data ke tabel transaksi
            $query_transaksi = "INSERT INTO transaksi (id_transaksi, id_barang, tanggal_penjualan, jumlah_terjual, harga_beli, harga_jual) 
                                VALUES (?, ?, ?, ?, ?, ?)";
            $stmt_transaksi = $conn->prepare($query_transaksi);
            $stmt_transaksi->bind_param("iisidd", $id_transaksi, $id_barang, $tanggal_penjualan, $jumlah_terjual, $harga_beli, $harga_jual);

            if ($stmt_transaksi->execute()) {
                // Kurangi stok barang
                $new_stok = $stok_barang - $jumlah_terjual;
                $query_update_stok = "UPDATE barang SET stok_barang = ? WHERE id_barang = ?";
                $stmt_update = $conn->prepare($query_update_stok);
                $stmt_update->bind_param("ii", $new_stok, $id_barang);
                $stmt_update->execute();
                $stmt_update->close();

                header("Location: transaksi.php?success=true");
                exit;
            } else {
                echo "Error: " . $stmt_transaksi->error;
            }
            $stmt_transaksi->close();
        } else {
            $alert_message = "Jumlah terjual melebihi stok barang yang tersedia.";
        }
    } else {
        echo "Error: Barang tidak ditemukan!";
    }
}

$conn->close();
ob_end_flush();
?>
<?php include '../header/header.php'; ?>
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Add</strong> Transaksi</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <form method="post" action="">
                            <div class="mb-3">
                                <label class="form-label" for="id_barang">Nama Barang</label>
                                <select class="form-control form-control-lg" id="id_barang" name="id_barang" required>
                                    <option value="">-- Pilih Barang --</option>
                                    <?php if ($result_barang->num_rows > 0): ?>
                                        <?php while ($row = $result_barang->fetch_assoc()): ?>
                                            <option value="<?php echo $row['id_barang']; ?>">
                                                <?php echo htmlspecialchars($row['nama_barang']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <option value="">Barang tidak tersedia</option>
                                    <?php endif; ?>
                                </select>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="tanggal_penjualan">Tanggal Penjualan</label>
                                <input class="form-control form-control-lg" type="date" id="tanggal_penjualan" name="tanggal_penjualan" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label" for="jumlah_terjual">Jumlah Terjual</label>
                                <input class="form-control form-control-lg" type="number" id="jumlah_terjual" name="jumlah_terjual" required>
                                <?php if ($alert_message) { ?>
                                    <div class="text-danger mt-2">
                                        
                                        <?php echo $alert_message; ?>
                                    </div>
                                <?php } ?>
                            </div>

                            <div class="d-grid gap-2 mt-3">
                                <button class="btn btn-lg btn-primary" type="submit">Add Transaksi</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../footer/footer.php'; ?>
