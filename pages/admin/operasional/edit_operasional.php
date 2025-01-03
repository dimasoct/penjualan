<?php
// Koneksi ke database
include '../../../database/db.php';

$conn = new mysqli($host, $username, $password, $dbname);

// Periksa koneksi
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Inisialisasi pesan sukses/gagal
$message = "";

// Ambil ID biaya dari URL
$id_biaya = isset($_GET['id_biaya']) ? intval($_GET['id_biaya']) : 0;

if ($id_biaya > 0) {
    // Ambil data biaya dari database
    $query_select = "SELECT keterangan, jumlah_biaya, tanggal_biaya FROM operasional WHERE id_biaya = ?";
    $stmt_select = $conn->prepare($query_select);
    $stmt_select->bind_param("i", $id_biaya);
    $stmt_select->execute();
    $result = $stmt_select->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $keterangan = $row['keterangan'];
        $jumlah_biaya = $row['jumlah_biaya'];
        $tanggal_biaya = $row['tanggal_biaya'];
    } else {
        die("Data tidak ditemukan.");
    }

    $stmt_select->close();
} else {
    die("ID biaya tidak valid.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil data dari form
    $keterangan = $_POST['keterangan'];
    $jumlah_biaya = $_POST['jumlah_biaya'];
    $tanggal_biaya = $_POST['tanggal_biaya'];

    // Validasi input
    if (!empty($keterangan) && !empty($jumlah_biaya) && !empty($tanggal_biaya)) {
        // Query untuk mengupdate data
        $query_update = "UPDATE operasional SET keterangan = ?, jumlah_biaya = ?, tanggal_biaya = ? WHERE id_biaya = ?";
        $stmt_update = $conn->prepare($query_update);
        $stmt_update->bind_param("sisi", $keterangan, $jumlah_biaya, $tanggal_biaya, $id_biaya);

        if ($stmt_update->execute()) {
            header("Location: operasional.php?success=edit");
            exit;
        } else {
            $message = "Terjadi kesalahan: " . $stmt_update->error;
        }

        $stmt_update->close();
    } else {
        $message = "Semua kolom harus diisi.";
    }
}

$conn->close();
?>

<?php include '../header/header.php'; ?>

<main class="content">
    <div class="container-fluid p-0">
        <h1 class="h3 mb-3"><strong>Edit Data Operasional</strong></h1>

        <?php if (!empty($message)): ?>
            <div class="alert alert-info"> <?php echo $message; ?> </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-body">
                        <form method="POST" action="">
                            <div class="mb-3">
                                <label for="keterangan" class="form-label">Keterangan</label>
                                <input type="text" class="form-control" id="keterangan" name="keterangan" value="<?php echo htmlspecialchars($keterangan); ?>" required>
                            </div>
                            <div class="mb-3">
                                <label for="jumlah_biaya" class="form-label">Jumlah Biaya</label>
                                <div class="input-group">
                                    <span class="input-group-text">Rp</span>
                                    <input type="number" class="form-control" id="jumlah_biaya" name="jumlah_biaya" value="<?php echo floor(htmlspecialchars($jumlah_biaya)); ?>" required>
                                </div>
                            </div>
                            <div class="mb-3">
                                <label for="tanggal_biaya" class="form-label">Tanggal Biaya</label>
                                <input type="date" class="form-control" id="tanggal_biaya" name="tanggal_biaya" value="<?php echo htmlspecialchars($tanggal_biaya); ?>" required>
                            </div>
                            <div class="d-grid gap-2 mt-3">
                                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</main>

<?php include '../footer/footer.php'; ?>
