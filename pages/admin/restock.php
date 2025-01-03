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

// Query untuk mengambil data semua barang
$sql_barang = "SELECT id_barang, nama_barang, kategori, stok_barang, harga_beli, harga_jual, created_at FROM barang";
$result_barang = $conn->query($sql_barang);

?>
<?php include 'header.php';?>

        <main class="content">
          <div class="container-fluid p-0">
            <h1 class="h3 mb-3"><strong>Management</strong> Stok Barang</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-header col-auto mt-0">
                        
                    
                    <div class="btn btn-primary col-auto mt-0">Tambah Stok</div>
                    <!-- Tambahkan wrapper dengan kelas table-responsive -->
                    <div class="table-responsive">
                        <table class="table table-hover my-0">
                            <thead>
                                <tr>
                                    <th>Nama Barang</th>
                                    <th>Kategori</th>
                                    <th>Stok Barang</th>
                                    <th>Harga Beli</th>
                                    <th>Harga Jual</th>
                                    <th>Dibuat Pada</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_barang->num_rows > 0): ?>
                                    <?php while ($row = $result_barang->fetch_assoc()): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($row['nama_barang']); ?></td>
                                            <td><?php echo htmlspecialchars($row['kategori']); ?></td>
                                            <td><?php echo htmlspecialchars($row['stok_barang']); ?></td>
                                            <td><?php echo htmlspecialchars($row['harga_beli']); ?></td>
                                            <td><?php echo htmlspecialchars($row['harga_jual']); ?></td>
                                            <td><?php echo htmlspecialchars($row['created_at']); ?></td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="7">No data found</td>
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