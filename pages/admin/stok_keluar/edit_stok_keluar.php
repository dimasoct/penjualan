<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Ambil ID stok keluar dari URL
if (!isset($_GET['id_stok_keluar'])) {
    die("ID Stok Keluar is not specified.");
}
$id_stok_keluar = $conn->real_escape_string($_GET['id_stok_keluar']);

// Ambil data stok keluar berdasarkan ID
$sql = "
    SELECT stok_keluar.*, barang.nama_barang, barang.harga_jual, 
           COALESCE(stok.stok, 0) AS stok_sekarang
    FROM stok_keluar
    JOIN barang ON stok_keluar.id_barang = barang.id_barang
    LEFT JOIN stok ON barang.id_barang = stok.id_barang
    WHERE id_stok_keluar = '$id_stok_keluar'";
$result = $conn->query($sql);

if (!$result || $result->num_rows == 0) {
    die("Error: Data not found for ID: $id_stok_keluar");
}
$row = $result->fetch_assoc();

// Ambil daftar barang untuk dropdown
$sql_barang = "SELECT id_barang, nama_barang FROM barang";
$result_barang = $conn->query($sql_barang);

if (!$result_barang || $result_barang->num_rows == 0) {
    die("Error: No data found in barang table.");
}

// Handle form submit
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_barang = $conn->real_escape_string($_POST['id_barang']);
    $stok_keluar_baru = intval($_POST['stok_keluar']);
    $tanggal_keluar = $conn->real_escape_string($_POST['tanggal_keluar']);
    $tujuan_barang = $conn->real_escape_string($_POST['tujuan_barang']);

    // Ambil stok_keluar lama
    $stok_keluar_lama = intval($row['stok_keluar']);

    // Hitung selisih stok
    $stok_selisih = $stok_keluar_lama - $stok_keluar_baru;

    // Perbarui stok di tabel stok
    $update_stok_sql = "
        UPDATE stok
        SET stok = stok + $stok_selisih
        WHERE id_barang = '$id_barang'";
    
    if (!$conn->query($update_stok_sql)) {
        die("Error updating stok: " . $conn->error);
    }

    // Perbarui data stok_keluar
    $update_sql = "
        UPDATE stok_keluar 
        SET id_barang = '$id_barang', stok_keluar = '$stok_keluar_baru', 
            tanggal_keluar = '$tanggal_keluar', tujuan_barang = '$tujuan_barang' 
        WHERE id_stok_keluar = '$id_stok_keluar'";
    
    if ($conn->query($update_sql)) {
        header("Location: stok_keluar.php");
        exit();
    } else {
        die("Error updating stok_keluar: " . $conn->error);
    }
}
?>
<?php include '../header/header.php'; ?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Edit</strong> Stok Keluar</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="id_stok_keluar" class="form-label">ID Stok Keluar</label>
                                <input type="text" id="id_stok_keluar" name="id_stok_keluar" class="form-control" 
                                    value="<?php echo htmlspecialchars($row['id_stok_keluar']); ?>" disabled>
                            </div>
                           <div class="mb-3">
                                <label for="id_barang" class="form-label">Nama Barang</label>
                                <?php while($row_barang = $result_barang->fetch_assoc()): ?>
                                    <?php if ($row_barang['id_barang'] == $row['id_barang']): ?>
                                        <input type="text" id="id_barang" name="id_barang_display" class="form-control" 
                                            value="<?php echo htmlspecialchars($row_barang['nama_barang']); ?>" disabled>
                                        <input type="hidden" name="id_barang" value="<?php echo htmlspecialchars($row_barang['id_barang']); ?>">
                                    <?php endif; ?>
                                <?php endwhile; ?>
                            </div>
                            <div class="mb-3">
                                <label for="stok_keluar" class="form-label">Stok Keluar</label>
                                <input type="number" id="stok_keluar" name="stok_keluar" class="form-control" 
                                    value="<?php echo htmlspecialchars($row['stok_keluar']); ?>" 
                                    required oninput="updateValues()">
                                <div id="stokWarning" class="text-danger mt-1" style="display: none;">
                                    Peringatan: Stok tidak mencukupi!
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="total_stok" class="form-label">Total Stok</label>
                                <input type="number" id="total_stok" name="total_stok" class="form-control" 
                                    value="<?php echo htmlspecialchars($row['stok_sekarang']); ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="harga_jual" class="form-label">Harga Jual</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="harga_jual" name="harga_jual" class="form-control" 
                                        value="<?php echo number_format(floor($row['harga_jual']), 0, '', '.'); ?>" disabled>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="total_harga_jual" class="form-label">Total Harga Jual</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="total_harga_jual" name="total_harga_jual" class="form-control" 
                                        value="<?php echo number_format(floor($row['stok_keluar'] * $row['harga_jual']), 0, '', '.'); ?>" disabled>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_keluar" class="form-label">Tanggal Keluar</label>
                                <input type="date" id="tanggal_keluar" name="tanggal_keluar" class="form-control" 
                                    value="<?php echo htmlspecialchars($row['tanggal_keluar']); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="tujuan_barang" class="form-label">Tujuan Barang</label>
                                <input type="text" id="tujuan_barang" name="tujuan_barang" class="form-control" 
                                    value="<?php echo htmlspecialchars($row['tujuan_barang']); ?>" required>
                            </div><br>
                            <div class="d-grid gap-2 mt-3">
                                <div class="row">
                                    <div class="col">
                                        <a href="stok_keluar.php" class="btn btn-lg btn-danger w-100">Kembali</a>
                                    </div>
                                    <div class="col">
                                        <button type="submit" class="btn btn-lg btn-primary w-100">Simpan Perubahan</button>
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

<script>
function updateValues() {
    const stokKeluar = parseFloat(document.getElementById('stok_keluar').value) || 0;
    const hargaJual = parseFloat(document.getElementById('harga_jual').value.replace(/\./g, '')) || 0;
    const stokLama = <?php echo $row['stok_keluar']; ?>;
    const stokDiTabel = <?php echo $row['stok_sekarang']; ?>;

    // Hitung nilai baru
    const totalHargaJual = Math.floor(stokKeluar * hargaJual);
    const totalStok = stokDiTabel + stokLama - stokKeluar;

    // Format number with thousand separator
    const formattedTotalHargaJual = totalHargaJual.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");

    // Perbarui nilai di field yang sesuai
    document.getElementById('total_harga_jual').value = totalHargaJual >= 0 ? formattedTotalHargaJual : 0;
    document.getElementById('total_stok').value = totalStok;

    // Tampilkan peringatan jika total stok kurang dari 0
    const warningElement = document.getElementById('stokWarning');
    if (totalStok < 0) {
        warningElement.style.display = 'block';
    } else {
        warningElement.style.display = 'none';
    }
}
</script>

<?php include '../footer/footer.php'; ?>
