<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Query untuk mengambil data dari tabel transaksi dan barang
$sql = "
    SELECT 
        t.id_transaksi, 
        t.id_barang, 
        b.nama_barang, 
        t.tanggal_penjualan, 
        t.jumlah_terjual, 
        b.harga_beli, 
        b.harga_jual, 
        (b.harga_jual * t.jumlah_terjual) AS total_pendapatan, 
        ((b.harga_jual - b.harga_beli) * t.jumlah_terjual) AS total_keuntungan
    FROM transaksi t
    INNER JOIN barang b ON t.id_barang = b.id_barang
";

$result = $conn->query($sql);
?>

<?php include '../header/header.php';?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Penjualan</strong> Barang</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header">
                        <a class="btn btn-primary col-auto mt-0 mb-2" href="add_transaksi.php">Tambah Penjualan</a>
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <thead>
                                    <tr>
                                        <th class="center">Nama Barang</th>
                                        <th class="center">Tanggal Penjualan</th>
                                        <th class="center">Jumlah Terjual</th>
                                        <th class="center">Harga Beli</th>
                                        <th class="center">Harga Jual</th>
                                        <th class="center">Total Pendapatan</th>
                                        <th class="center">Total Keuntungan</th>
                                        <th class="center">Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result->num_rows > 0): ?>
                                        <?php while ($row = $result->fetch_assoc()): ?>
                                            <tr>
                                                <td class="center"><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['tanggal_penjualan']); ?></td>
                                                <td class="center"><?php echo htmlspecialchars($row['jumlah_terjual']); ?></td>
                                                <td class="center">Rp <?php echo number_format($row['harga_beli'], 0, ',', '.'); ?></td>
                                                <td class="center">Rp <?php echo number_format($row['harga_jual'], 0, ',', '.'); ?></td>
                                                <td class="center">Rp <?php echo htmlspecialchars(number_format($row['total_pendapatan'], 0, ',', '.')); ?></td>
                                                <td class="center">Rp <?php echo htmlspecialchars(number_format($row['total_keuntungan'], 0, ',', '.')); ?></td>
                                                <td class="center">
                                                   <a href="edit_transaksi.php?id_transaksi=<?php echo htmlspecialchars($row['id_transaksi']); ?>&id_barang=<?php echo htmlspecialchars($row['id_barang']); ?>" class="btn btn-primary btn-sm mb-3">
                                                        <i class="align-middle" data-feather="edit"></i>
                                                    </a>
                                                    <button class="btn btn-danger btn-sm mb-3" onclick="deleteTransaksi(<?php echo htmlspecialchars($row['id_transaksi']); ?>)">
                                                        <i class="align-middle" data-feather="trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="10" class="text-center">No data found</td>
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

<?php include '../footer/footer.php'; ?>

<script>
function deleteTransaksi(id_transaksi) {
    if (confirm("Apakah Anda yakin ingin menghapus transaksi ini?")) {
        // Buat permintaan AJAX untuk menghapus
        var xhr = new XMLHttpRequest();
        xhr.open("GET", "delete_transaksi.php?id_transaksi=" + id_transaksi, true);
        xhr.onload = function() {
            if (xhr.status == 200) {
                // Tampilkan pesan berdasarkan respon server
                var response = xhr.responseText.trim();
                if (response === "Record deleted successfully") {
                    alert("Data berhasil dihapus!");
                    location.reload();  // Muat ulang halaman setelah penghapusan
                } else {
                    alert("Gagal menghapus data: " + response);
                }
            } else {
                alert("Gagal menghapus data. Status: " + xhr.status);
            }
        };
        xhr.send();
    }
}
</script>
