<?php 
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Cek apakah parameter id_transaksi dan id_barang ada di URL
if (isset($_GET['id_transaksi']) && isset($_GET['id_barang'])) {
    $id_transaksi = $_GET['id_transaksi'];
    $id_barang = $_GET['id_barang'];

    // Ambil data barang dan transaksi berdasarkan id
    $query_barang = $conn->prepare("SELECT * FROM barang WHERE id_barang = ?");
    $query_barang->bind_param("i", $id_barang);
    $query_barang->execute();
    $result_barang = $query_barang->get_result();
    $barang = $result_barang->fetch_assoc();

    $query_transaksi = $conn->prepare("SELECT * FROM transaksi WHERE id_transaksi = ? AND id_barang = ?");
    $query_transaksi->bind_param("ii", $id_transaksi, $id_barang);
    $query_transaksi->execute();
    $result_transaksi = $query_transaksi->get_result();
    $transaksi = $result_transaksi->fetch_assoc();

    // Cek jika form disubmit
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        // Ambil data dari form
        $id_transaksi = $_POST['id_transaksi'];
        $id_barang = $_POST['id_barang'];
        $nama_barang = $_POST['nama_barang'];
        $tanggal_penjualan = $_POST['tanggal_penjualan'];
        $jumlah_terjual = $_POST['jumlah_terjual'];

        // Update data transaksi dengan prepared statement
        $update_query = $conn->prepare("UPDATE transaksi SET nama_barang = ?, tanggal_penjualan = ?, jumlah_terjual = ? WHERE id_transaksi = ? AND id_barang = ?");
        $update_query->bind_param("ssiii", $nama_barang, $tanggal_penjualan, $jumlah_terjual, $id_transaksi, $id_barang);
        
        if ($update_query->execute()) {
            echo "<script>alert('Item updated successfully'); window.location.href = 'transaksi_list.php';</script>";
        } else {
            echo "Error updating record: " . $conn->error;
        }
    }
} else {
    // Jika id_transaksi atau id_barang tidak ada, tampilkan error atau redirect
    echo "Error: Missing id_transaksi or id_barang.";
    exit;
}
?>
<?php include '../header/header.php';?>
<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Edit</strong> Item</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <form method="post" enctype="multipart/form-data" action="">
                            <input type="hidden" name="id_transaksi" value="<?php echo $transaksi['id_transaksi']; ?>">
                            <input type="hidden" name="id_barang" value="<?php echo $transaksi['id_barang']; ?>">
                            <div class="mb-3">
                                <label class="form-label" for="nama_barang">Nama Barang</label>
                                <input class="form-control form-control-lg" type="text" name="nama_barang" id="nama_barang" value="<?php echo $barang['nama_barang']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="tanggal_penjualan">Tanggal Penjualan</label>
                                <input class="form-control form-control-lg" type="date" id="tanggal_penjualan" name="tanggal_penjualan" value="<?php echo $transaksi['tanggal_penjualan']; ?>" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label" for="jumlah_terjual">Jumlah Terjual</label>
                                <input class="form-control form-control-lg" type="number" name="jumlah_terjual" id="jumlah_terjual" value="<?php echo $transaksi['jumlah_terjual']; ?>" required>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <button class="btn btn-lg btn-primary" type="submit">Update Item</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../footer/footer.php'; ?>
