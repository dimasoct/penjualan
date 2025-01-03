<?php 
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!function_exists('generateIdStokKeluar')) {
    function generateIdStokKeluar() {
        $characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $randomString = '';
        for ($i = 0; $i < 4; $i++) {
            $randomString .= $characters[rand(0, strlen($characters) - 1)];
        }
        return 'SK-' . $randomString;
    }
}

// Generate ID Stok Keluar
$id_stok_keluar = generateIdStokKeluar();

// Ambil daftar barang dengan stok dan harga
$sql_barang = "
    SELECT b.id_barang, b.nama_barang, COALESCE(s.stok, 0) AS stok, FLOOR(b.harga_jual) as harga_jual
    FROM barang b
    LEFT JOIN stok s ON b.id_barang = s.id_barang";
$result_barang = $conn->query($sql_barang);

$sisa_stok = 0;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $id_stok_keluar = generateIdStokKeluar();
    $id_barang = $_POST['id_barang'];
    $stok_keluar = $_POST['stok_keluar'];
    $tanggal_keluar = $_POST['tanggal_keluar'];
    $tujuan_barang = $_POST['tujuan_barang'];

    // Ambil stok dan harga saat ini dari tabel barang
    $sql_barang_detail = "SELECT s.stok, FLOOR(b.harga_jual) as harga_jual 
                          FROM barang b
                          LEFT JOIN stok s ON b.id_barang = s.id_barang 
                          WHERE b.id_barang = '$id_barang'";
    $result_barang_detail = $conn->query($sql_barang_detail);
    $barang_detail = $result_barang_detail->fetch_assoc();
    $stok_sekarang = $barang_detail['stok'] ?? 0;
    $harga_jual = $barang_detail['harga_jual'] ?? 0;

    // Hitung sisa stok
    $sisa_stok = $stok_sekarang - $stok_keluar;

    if ($sisa_stok >= 0) {
        // Update stok di tabel stok
        $update_stok = "UPDATE stok SET stok = $sisa_stok WHERE id_barang = '$id_barang'";
        $conn->query($update_stok);

        // Hitung total harga jual
        $total_harga_jual = $harga_jual * $stok_keluar;

        // Tambahkan data ke tabel stok_keluar
        $insert_sql = "INSERT INTO stok_keluar (id_stok_keluar, id_barang, stok_keluar, tanggal_keluar, tujuan_barang, harga_jual, total_harga_jual)
                       VALUES ('$id_stok_keluar', '$id_barang', '$stok_keluar', '$tanggal_keluar', '$tujuan_barang', '$harga_jual', '$total_harga_jual')";

        if ($conn->query($insert_sql) === TRUE) {
            header("Location: stok_keluar.php");
            exit();
        } else {
            echo "Error: " . $insert_sql . "<br>" . $conn->error;
        }
    } else {
        echo "Error: Stok tidak mencukupi.";
    }
}
?>

<?php include '../header/header.php'; ?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Tambah</strong> Stok Keluar</h1>
        <div class="row">
            <div class="col-12 col-lg-12 col-xxl-12 d-flex">
                <div class="card flex-fill">
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="id_stok_keluar" class="form-label">ID Stok Keluar</label>
                                <input type="text" id="id_stok_keluar" name="id_stok_keluar" class="form-control" value="<?php echo $id_stok_keluar; ?>" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="id_barang" class="form-label">Nama Barang</label>
                                <select id="id_barang" name="id_barang" class="form-select" required onchange="updateStokAndPrice(this.value)">
                                    <option value="">Pilih Barang</option>
                                    <?php while($row_barang = $result_barang->fetch_assoc()): ?>
                                        <option value="<?php echo $row_barang['id_barang']; ?>" 
                                                data-stok="<?php echo $row_barang['stok']; ?>" 
                                                data-harga="<?php echo floor($row_barang['harga_jual']); ?>">
                                            <?php echo htmlspecialchars($row_barang['nama_barang']); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div class="mb-3">
                                <label for="stok_sekarang" class="form-label">Stok Sekarang</label>
                                <input type="text" id="stok_sekarang" name="stok_sekarang" class="form-control" value="0" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="stok_keluar" class="form-label">Stok Keluar</label>
                                <input type="number" id="stok_keluar" name="stok_keluar" class="form-control" required>
                                <div id="stokWarning" class="text-danger mt-1" style="display: none;">
                                    Peringatan: Stok tidak mencukupi!
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="sisa_stok" class="form-label">Sisa Stok</label>
                                <input type="text" id="sisa_stok" name="sisa_stok" class="form-control" value="0" disabled>
                            </div>
                            <div class="mb-3">
                                <label for="harga_jual" class="form-label">Harga Jual</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="harga_jual" name="harga_jual" class="form-control" value="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" disabled>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="total_harga_jual" class="form-label">Total Harga Jual</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="text" id="total_harga_jual" name="total_harga_jual" class="form-control" value="0" oninput="this.value = this.value.replace(/[^0-9]/g, '')" disabled>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_keluar" class="form-label">Tanggal Keluar</label>
                                <input type="date" id="tanggal_keluar" name="tanggal_keluar" class="form-control" required>
                            </div>
                            <div class="mb-3">
                                <label for="tujuan_barang" class="form-label">Tujuan Barang</label>
                                <input type="text" id="tujuan_barang" name="tujuan_barang" class="form-control" required>
                            </div><br>
                            <div class="d-grid gap-2 mt-3">
                                <div class="row">
                                    <div class="col">
                                        <a href="stok_keluar.php" class="btn btn-lg btn-danger w-100">Kembali</a>
                                    </div>
                                    <div class="col">
                                        <button type="submit" class="btn btn-lg btn-primary w-100">Tambah Stok Keluar</button>
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
    function formatNumber(num) {
        return num.toString().replace(/\B(?=(\d{3})+(?!\d))/g, ".");
    }

    function updateStokAndPrice(id_barang) {
        const select = document.getElementById('id_barang');
        const selectedOption = select.options[select.selectedIndex];
        const stok = selectedOption.getAttribute('data-stok');
        const harga = Math.floor(selectedOption.getAttribute('data-harga'));

        document.getElementById('stok_sekarang').value = stok || 0;
        document.getElementById('harga_jual').value = formatNumber(harga) || 0;
        document.getElementById('sisa_stok').value = 0;
        document.getElementById('total_harga_jual').value = 0;

        // Update sisa stok dan total harga jual saat stok keluar berubah
        document.getElementById('stok_keluar').addEventListener('input', function () {
            const stokKeluar = parseInt(this.value) || 0;
            const stokSekarang = parseInt(stok) || 0;
            const totalHargaJual = parseInt(harga) || 0;
            const sisaStok = stokSekarang - stokKeluar;
            document.getElementById('sisa_stok').value = sisaStok;
            document.getElementById('total_harga_jual').value = formatNumber(Math.floor(totalHargaJual * stokKeluar));

            // Tampilkan peringatan jika sisa stok kurang dari 0
            const warningElement = document.getElementById('stokWarning');
            if (sisaStok < 0) {
                warningElement.style.display = 'block';
            } else {
                warningElement.style.display = 'none';
            }
        });
    }
</script>

<?php include '../footer/footer.php'; ?>
